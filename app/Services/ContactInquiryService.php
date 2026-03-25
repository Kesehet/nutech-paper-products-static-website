<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Request;
use PDO;
use PDOException;

final class ContactInquiryService
{
    private const MAX_FULL_NAME_LENGTH = 190;
    private const MAX_EMAIL_LENGTH = 190;
    private const MAX_PHONE_LENGTH = 80;
    private const MAX_COMPANY_LENGTH = 190;
    private const MAX_INQUIRY_TYPE_LENGTH = 120;
    private const MAX_MESSAGE_LENGTH = 5000;
    private const MAX_SOURCE_PAGE_LENGTH = 255;
    private const RATE_LIMIT_WINDOW_SECONDS = 900;
    private const RATE_LIMIT_MAX_ATTEMPTS = 8;
    private const MIN_SUBMIT_SECONDS = 2;

    /**
     * @return array{ok:bool,message:string,old:array<string,mixed>}
     */
    public function submitPublicInquiry(Request $request): array
    {
        if ($this->isHoneypotTriggered($request)) {
            return [
                'ok' => true,
                'message' => 'Thank you. Our team will get back to you shortly.',
                'old' => [],
            ];
        }

        if (!$this->passesSubmissionTiming($request)) {
            return [
                'ok' => false,
                'message' => 'Please wait a moment and submit the form again.',
                'old' => $this->oldInput($request),
            ];
        }

        $rateLimitError = $this->consumeRateLimitToken($request);
        if ($rateLimitError !== null) {
            return [
                'ok' => false,
                'message' => $rateLimitError,
                'old' => $this->oldInput($request),
            ];
        }

        [$payload, $error] = $this->buildPayload($request);
        if ($error !== null) {
            return [
                'ok' => false,
                'message' => $error,
                'old' => $this->oldInput($request),
            ];
        }

        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'INSERT INTO contact_inquiries
                    (full_name, email, phone, company_name, inquiry_type, message, product_id, source_page, status, created_at)
                 VALUES
                    (:full_name, :email, :phone, :company_name, :inquiry_type, :message, :product_id, :source_page, :status, NOW())'
            );
            $stmt->execute($payload);

            $inquiryId = (int) $pdo->lastInsertId();
            ActivityLogger::log(
                null,
                'contact_inquiry.create',
                'contact_inquiry',
                $inquiryId > 0 ? $inquiryId : null,
                null,
                [
                    'source_page' => (string) ($payload['source_page'] ?? ''),
                    'inquiry_type' => (string) ($payload['inquiry_type'] ?? ''),
                    'product_id' => $payload['product_id'],
                ],
                $this->clientIp($request),
                $this->userAgent($request)
            );
        } catch (PDOException $exception) {
            error_log('Contact inquiry insert failed: ' . $exception->getMessage());

            return [
                'ok' => false,
                'message' => 'We could not submit your inquiry right now. Please try again shortly.',
                'old' => $this->oldInput($request),
            ];
        }

        return [
            'ok' => true,
            'message' => 'Thank you. Our team will get back to you shortly.',
            'old' => [],
        ];
    }

    /**
     * @return array{rows:array<int,array<string,mixed>>,total:int}
     */
    public function getPaginatedForAdmin(string $search, int $page, int $perPage): array
    {
        $safePage = max(1, $page);
        $safePerPage = max(1, $perPage);
        $offset = ($safePage - 1) * $safePerPage;

        try {
            $pdo = Database::connection();
            [$whereSql, $params] = $this->buildAdminSearchClause($search);

            $countStmt = $pdo->prepare(
                'SELECT COUNT(*)
                 FROM contact_inquiries ci
                 LEFT JOIN products p ON p.id = ci.product_id' . $whereSql
            );
            $countStmt->execute($params);
            $total = (int) ($countStmt->fetchColumn() ?: 0);

            $sql = 'SELECT ci.id, ci.full_name, ci.email, ci.phone, ci.company_name, ci.inquiry_type,
                           ci.message, ci.source_page, ci.status, ci.created_at, p.title AS product_title
                    FROM contact_inquiries ci
                    LEFT JOIN products p ON p.id = ci.product_id'
                    . $whereSql .
                    ' ORDER BY ci.id DESC
                      LIMIT :limit OFFSET :offset';

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit', $safePerPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'rows' => $stmt->fetchAll() ?: [],
                'total' => $total,
            ];
        } catch (PDOException $exception) {
            error_log('Admin inquiries query failed: ' . $exception->getMessage());

            return [
                'rows' => [],
                'total' => 0,
            ];
        }
    }

    public function countAll(): int
    {
        try {
            $pdo = Database::connection();
            return (int) ($pdo->query('SELECT COUNT(*) FROM contact_inquiries')->fetchColumn() ?: 0);
        } catch (PDOException) {
            return 0;
        }
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getExportRows(string $search): array
    {
        try {
            $pdo = Database::connection();
            [$whereSql, $params] = $this->buildAdminSearchClause($search);

            $stmt = $pdo->prepare(
                'SELECT ci.id, ci.full_name, ci.email, ci.phone, ci.company_name, ci.inquiry_type,
                        ci.message, ci.source_page, ci.status, ci.created_at, p.title AS product_title
                 FROM contact_inquiries ci
                 LEFT JOIN products p ON p.id = ci.product_id'
                 . $whereSql .
                 ' ORDER BY ci.id DESC'
            );
            $stmt->execute($params);
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $exception) {
            error_log('Inquiry export query failed: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * @return list<string>
     */
    public function csvHeaders(): array
    {
        return [
            'ID',
            'Full Name',
            'Email',
            'Phone',
            'Company',
            'Inquiry Type',
            'Product',
            'Source Page',
            'Status',
            'Message',
            'Submitted At',
        ];
    }

    /**
     * @return list<string>
     */
    public function toCsvRow(array $row): array
    {
        return [
            $this->escapeCsvValue((string) ($row['id'] ?? '')),
            $this->escapeCsvValue((string) ($row['full_name'] ?? '')),
            $this->escapeCsvValue((string) ($row['email'] ?? '')),
            $this->escapeCsvValue((string) ($row['phone'] ?? '')),
            $this->escapeCsvValue((string) ($row['company_name'] ?? '')),
            $this->escapeCsvValue((string) ($row['inquiry_type'] ?? '')),
            $this->escapeCsvValue((string) ($row['product_title'] ?? '')),
            $this->escapeCsvValue((string) ($row['source_page'] ?? '')),
            $this->escapeCsvValue((string) ($row['status'] ?? '')),
            $this->escapeCsvValue((string) ($row['message'] ?? '')),
            $this->escapeCsvValue((string) ($row['created_at'] ?? '')),
        ];
    }

    /**
     * @return array{0:array<string,mixed>,1:?string}
     */
    private function buildPayload(Request $request): array
    {
        $fullName = $this->sanitizeSingleLine((string) $request->input('full_name', ''));
        $email = strtolower($this->sanitizeSingleLine((string) $request->input('email', '')));
        $phone = $this->sanitizeSingleLine((string) $request->input('phone', ''));
        $companyName = $this->sanitizeSingleLine((string) $request->input('company_name', ''));
        $inquiryType = $this->sanitizeSingleLine((string) $request->input('inquiry_type', 'General Inquiry'));
        $message = $this->sanitizeMessage((string) $request->input('message', ''));
        $productId = (int) $request->input('product_id', 0);
        $sourcePage = $this->sanitizeSourcePage((string) $request->input('source_page', '/contact-us'));

        if ($fullName === '') {
            return [[], 'Full name is required.'];
        }

        if ($this->stringLength($fullName) > self::MAX_FULL_NAME_LENGTH) {
            return [[], 'Full name is too long.'];
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [[], 'Please provide a valid email address.'];
        }

        if ($this->stringLength($email) > self::MAX_EMAIL_LENGTH) {
            return [[], 'Email address is too long.'];
        }

        if ($phone !== '') {
            if ($this->stringLength($phone) > self::MAX_PHONE_LENGTH) {
                return [[], 'Phone number is too long.'];
            }

            if (preg_match('/^[0-9+().\-\s]{7,80}$/', $phone) !== 1) {
                return [[], 'Please provide a valid phone number.'];
            }
        }

        if ($companyName !== '' && $this->stringLength($companyName) > self::MAX_COMPANY_LENGTH) {
            return [[], 'Company name is too long.'];
        }

        if ($inquiryType === '') {
            $inquiryType = 'General Inquiry';
        }

        if ($this->stringLength($inquiryType) > self::MAX_INQUIRY_TYPE_LENGTH) {
            return [[], 'Inquiry type is too long.'];
        }

        if ($message === '') {
            return [[], 'Message is required.'];
        }

        if ($this->stringLength($message) < 5) {
            return [[], 'Message is too short.'];
        }

        if ($this->stringLength($message) > self::MAX_MESSAGE_LENGTH) {
            return [[], 'Message is too long.'];
        }

        if ($this->stringLength($sourcePage) > self::MAX_SOURCE_PAGE_LENGTH) {
            return [[], 'Invalid source page.'];
        }

        $resolvedProductId = null;
        if ($productId > 0) {
            $product = $this->findProductById($productId);
            if ($product === null) {
                return [[], 'Selected product could not be verified. Please try again.'];
            }

            $resolvedProductId = (int) ($product['id'] ?? 0);
            if ($inquiryType === 'General Inquiry') {
                $inquiryType = 'Product Inquiry';
            }
        }

        return [[
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'company_name' => $companyName !== '' ? $companyName : null,
            'inquiry_type' => $inquiryType,
            'message' => $message,
            'product_id' => $resolvedProductId,
            'source_page' => $sourcePage,
            'status' => 'new',
        ], null];
    }

    /**
     * @return array{0:string,1:array<string,string>}
     */
    private function buildAdminSearchClause(string $search): array
    {
        $needle = trim($search);
        if ($needle === '') {
            return ['', []];
        }

        $term = '%' . $needle . '%';

        return [
            ' WHERE (
                ci.full_name LIKE :search
                OR ci.email LIKE :search
                OR ci.phone LIKE :search
                OR ci.company_name LIKE :search
                OR ci.inquiry_type LIKE :search
                OR ci.message LIKE :search
                OR ci.source_page LIKE :search
                OR ci.status LIKE :search
                OR p.title LIKE :search
            )',
            ['search' => $term],
        ];
    }

    private function isHoneypotTriggered(Request $request): bool
    {
        return trim((string) $request->input('website', '')) !== '';
    }

    private function passesSubmissionTiming(Request $request): bool
    {
        $startedAt = (int) $request->input('contact_started_at', 0);
        if ($startedAt <= 0) {
            return false;
        }

        $now = time();
        if ($startedAt > $now + 300) {
            return false;
        }

        if (($now - $startedAt) > 86400) {
            return false;
        }

        return ($now - $startedAt) >= self::MIN_SUBMIT_SECONDS;
    }

    private function consumeRateLimitToken(Request $request): ?string
    {
        $identifier = $this->rateLimitIdentifier($request);
        if ($identifier === '') {
            return null;
        }

        $directory = BASE_PATH . '/storage/rate-limits';
        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            return null;
        }

        $filePath = $directory . '/' . $identifier . '.json';
        $handle = @fopen($filePath, 'c+');
        if ($handle === false) {
            return null;
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                return null;
            }

            $contents = stream_get_contents($handle);
            $timestamps = json_decode(is_string($contents) ? $contents : '[]', true);
            if (!is_array($timestamps)) {
                $timestamps = [];
            }

            $now = time();
            $timestamps = array_values(array_filter(
                array_map(static fn (mixed $value): int => (int) $value, $timestamps),
                static fn (int $timestamp): bool => $timestamp > 0 && ($now - $timestamp) < self::RATE_LIMIT_WINDOW_SECONDS
            ));

            if (count($timestamps) >= self::RATE_LIMIT_MAX_ATTEMPTS) {
                return 'Too many submissions were received from your connection. Please wait 15 minutes and try again.';
            }

            $timestamps[] = $now;

            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, json_encode($timestamps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]');
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        return null;
    }

    /**
     * @return array<string,mixed>
     */
    private function oldInput(Request $request): array
    {
        return [
            'full_name' => $this->sanitizeSingleLine((string) $request->input('full_name', '')),
            'email' => $this->sanitizeSingleLine((string) $request->input('email', '')),
            'phone' => $this->sanitizeSingleLine((string) $request->input('phone', '')),
            'company_name' => $this->sanitizeSingleLine((string) $request->input('company_name', '')),
            'inquiry_type' => $this->sanitizeSingleLine((string) $request->input('inquiry_type', '')),
            'message' => $this->sanitizeMessage((string) $request->input('message', '')),
            'product_id' => max(0, (int) $request->input('product_id', 0)),
            'source_page' => $this->sanitizeSourcePage((string) $request->input('source_page', '/contact-us')),
        ];
    }

    /**
     * @return array{id:int,title:string}|null
     */
    private function findProductById(int $productId): ?array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'SELECT id, title
                 FROM products
                 WHERE id = :id AND status = "published"
                 LIMIT 1'
            );
            $stmt->execute(['id' => $productId]);
            $row = $stmt->fetch();

            return is_array($row) ? [
                'id' => (int) ($row['id'] ?? 0),
                'title' => (string) ($row['title'] ?? ''),
            ] : null;
        } catch (PDOException) {
            return null;
        }
    }

    private function sanitizeSingleLine(string $value): string
    {
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return trim($value);
    }

    private function sanitizeMessage(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/[^\P{C}\n\t]+/u', '', $value) ?? $value;
        $value = preg_replace("/\n{3,}/", "\n\n", $value) ?? $value;
        return trim($value);
    }

    private function sanitizeSourcePage(string $value): string
    {
        $value = trim($value);
        if ($value === '' || $value[0] !== '/' || preg_match('#^(https?:)?//#i', $value) === 1) {
            return '/contact-us';
        }

        $path = parse_url($value, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return '/contact-us';
        }

        return '/' . ltrim($path, '/');
    }

    private function stringLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }

    private function rateLimitIdentifier(Request $request): string
    {
        $ip = $this->clientIp($request);
        if ($ip === '') {
            return '';
        }

        return hash('sha256', $ip);
    }

    private function clientIp(Request $request): string
    {
        $ip = trim((string) $request->server('REMOTE_ADDR', ''));
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return '';
        }

        return $ip;
    }

    private function userAgent(Request $request): string
    {
        return substr(trim((string) $request->server('HTTP_USER_AGENT', '')), 0, 255);
    }

    private function escapeCsvValue(string $value): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $value);
        $trimmedForFormulaCheck = ltrim($normalized, " \t");
        if ($trimmedForFormulaCheck !== '' && preg_match('/^[=+\-@]/', $trimmedForFormulaCheck) === 1) {
            return "'" . $normalized;
        }

        return $normalized;
    }
}
