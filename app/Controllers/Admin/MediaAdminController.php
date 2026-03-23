<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use PDOException;

final class MediaAdminController extends BaseAdminController
{
    public function index(Request $request): void
    {
        $this->requireAuth();

        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;
        $media = [];
        $pagination = $this->buildPagination(0, $page, $perPage);
        try {
            $pdo = Database::connection();
            $params = [];
            $where = '';
            if ($search !== '') {
                $where = ' WHERE m.original_name LIKE :search_name OR m.mime_type LIKE :search_mime OR m.storage_path LIKE :search_path';
                $term = '%' . $search . '%';
                $params['search_name'] = $term;
                $params['search_mime'] = $term;
                $params['search_path'] = $term;
            }

            $countStmt = $pdo->prepare('SELECT COUNT(*) FROM media m' . $where);
            $countStmt->execute($params);
            $total = (int) ($countStmt->fetchColumn() ?: 0);
            $pagination = $this->buildPagination($total, $page, $perPage);

            $stmt = $pdo->prepare(
                'SELECT m.id, m.file_name, m.original_name, m.storage_path, m.mime_type, m.size_bytes, m.width, m.height, m.created_at,
                        u.full_name AS uploaded_by_name
                 FROM media m
                 LEFT JOIN users u ON u.id = m.uploaded_by
                 ' . $where . '
                 ORDER BY m.id DESC
                 LIMIT :limit OFFSET :offset'
            );
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit', $pagination['per_page'], \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $pagination['offset'], \PDO::PARAM_INT);
            $stmt->execute();
            $media = $stmt->fetchAll() ?: [];
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to load media library: ' . $exception->getMessage());
        }

        $this->render('admin/media/index', $request, [
            'meta' => [
                'title' => 'Media Library | Nuteck Admin',
                'description' => 'Upload and manage reusable media assets.',
            ],
            'media' => $media,
            'search' => $search,
            'pagination' => $pagination,
        ]);
    }

    public function upload(Request $request): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($request, '/admin/media');

        [$media, $error] = $this->handleUpload($request);
        if ($error !== null) {
            Session::flash('error', $error);
            Response::redirect('/admin/media');
        }

        Session::flash('success', 'Media uploaded successfully.');
        Response::redirect('/admin/media');
    }

    public function uploadAsync(Request $request): void
    {
        $this->requireAuth();
        if (!$this->isValidCsrf($request)) {
            Response::json(['ok' => false, 'message' => 'Security token validation failed.'], 422);
        }

        [$media, $error] = $this->handleUpload($request);
        if ($error !== null || !is_array($media)) {
            Response::json(['ok' => false, 'message' => $error ?? 'Upload failed.'], 422);
        }

        Response::json([
            'ok' => true,
            'media' => $media,
            'message' => 'Media uploaded successfully.',
        ]);
    }

    public function ckeditorUpload(Request $request): void
    {
        $this->requireAuth();
        if (!$this->isValidCsrf($request)) {
            $this->emitCkEditorScript(0, '', 'Security token validation failed.');
        }

        [$media, $error] = $this->handleUpload($request);
        if ($error !== null || !is_array($media)) {
            $this->emitCkEditorScript(0, '', $error ?? 'Upload failed.');
        }

        $callback = (int) $request->query('CKEditorFuncNum', 0);
        $url = (string) ($media['url'] ?? '');
        $message = 'Uploaded successfully';

        header('Content-Type: text/html; charset=UTF-8');
        echo '<script>window.parent.CKEDITOR.tools.callFunction(' . $callback . ', ' . json_encode($url) . ', ' . json_encode($message) . ');</script>';
        exit;
    }

    public function delete(Request $request, array $params): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($request, '/admin/media');

        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            Response::redirect('/admin/media');
        }

        try {
            $pdo = Database::connection();

            $usageStmt = $pdo->prepare(
                'SELECT
                    (SELECT COUNT(*) FROM product_images WHERE media_id = :product_images_media_id) +
                    (SELECT COUNT(*) FROM products WHERE featured_image_id = :products_media_id) +
                    (SELECT COUNT(*) FROM page_sections WHERE featured_media_id = :page_sections_media_id) +
                    (SELECT COUNT(*) FROM seo_meta WHERE og_image_id = :seo_meta_media_id) +
                    (SELECT COUNT(*) FROM blogs WHERE featured_image_id = :blogs_featured_media_id) +
                    (SELECT COUNT(*) FROM blogs WHERE og_image_id = :blogs_og_media_id) AS usage_count'
            );
            $usageStmt->execute([
                'product_images_media_id' => $id,
                'products_media_id' => $id,
                'page_sections_media_id' => $id,
                'seo_meta_media_id' => $id,
                'blogs_featured_media_id' => $id,
                'blogs_og_media_id' => $id,
            ]);
            $usageCount = (int) $usageStmt->fetchColumn();
            if ($usageCount > 0) {
                Session::flash('error', 'Media is currently in use and cannot be deleted.');
                Response::redirect('/admin/media');
            }

            $mediaStmt = $pdo->prepare('SELECT file_name, storage_path FROM media WHERE id = :id LIMIT 1');
            $mediaStmt->execute(['id' => $id]);
            $row = $mediaStmt->fetch();
            if (!is_array($row)) {
                Session::flash('error', 'Media file not found.');
                Response::redirect('/admin/media');
            }

            $deleteStmt = $pdo->prepare('DELETE FROM media WHERE id = :id');
            $deleteStmt->execute(['id' => $id]);
            $this->logActivity($request, 'media.delete', 'media', $id, [
                'original_name' => $row['file_name'] ?? '',
                'storage_path' => $row['storage_path'] ?? '',
            ], null);

            $filePath = BASE_PATH . '/public' . (string) ($row['storage_path'] ?? '');
            if (is_file($filePath)) {
                @unlink($filePath);
            }

            Session::flash('success', 'Media deleted successfully.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to delete media: ' . $exception->getMessage());
        }

        Response::redirect('/admin/media');
    }

    private function isValidCsrf(Request $request): bool
    {
        $token = (string) $request->input('_csrf', '');
        if ($token === '') {
            $token = (string) $request->query('_csrf', '');
        }
        if ($token === '') {
            $headers = [
                'HTTP_X_CSRF_TOKEN',
                'HTTP_X_CSRF',
            ];
            foreach ($headers as $header) {
                $candidate = (string) $request->server($header, '');
                if ($candidate !== '') {
                    $token = $candidate;
                    break;
                }
            }
        }

        return \App\Core\Csrf::validate($token);
    }

    private function emitCkEditorScript(int $callback, string $url, string $message): never
    {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<script>window.parent.CKEDITOR.tools.callFunction(' . $callback . ', ' . json_encode($url) . ', ' . json_encode($message) . ');</script>';
        exit;
    }

    private function handleUpload(Request $request): array
    {
        $file = $request->files()['upload'] ?? $request->files()['media_file'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return [null, 'Please choose a valid file to upload.'];
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        $originalName = (string) ($file['name'] ?? 'upload');
        $size = (int) ($file['size'] ?? 0);
        $maxSize = (int) env('UPLOAD_MAX_SIZE', 5242880);
        if ($size <= 0 || $size > $maxSize) {
            return [null, 'File size exceeds upload limit.'];
        }

        $allowedExt = array_filter(array_map('trim', explode(',', (string) env('UPLOAD_ALLOWED_EXT', 'jpg,jpeg,png,webp,gif,svg,pdf'))));
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExt, true)) {
            return [null, 'File extension not allowed.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? (string) finfo_file($finfo, $tmpPath) : 'application/octet-stream';
        if ($finfo) {
            finfo_close($finfo);
        }

        $baseUploadDir = trim((string) env('UPLOAD_DIR', 'public/uploads'));
        $absoluteUploadDir = BASE_PATH . '/' . trim($baseUploadDir, '/');
        if (!is_dir($absoluteUploadDir) && !@mkdir($absoluteUploadDir, 0775, true) && !is_dir($absoluteUploadDir)) {
            return [null, 'Unable to create upload directory.'];
        }

        $storedName = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $targetPath = $absoluteUploadDir . '/' . $storedName;
        if (!move_uploaded_file($tmpPath, $targetPath)) {
            return [null, 'File upload failed.'];
        }

        $width = null;
        $height = null;
        if (str_starts_with($mimeType, 'image/')) {
            $dimensions = @getimagesize($targetPath);
            if (is_array($dimensions)) {
                $width = (int) ($dimensions[0] ?? 0);
                $height = (int) ($dimensions[1] ?? 0);
            }
        }

        $storagePath = '/uploads/' . $storedName;
        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'INSERT INTO media
                    (disk, file_name, original_name, storage_path, mime_type, size_bytes, width, height, alt_text, caption, uploaded_by, created_at)
                 VALUES
                    (:disk, :file_name, :original_name, :storage_path, :mime_type, :size_bytes, :width, :height, :alt_text, :caption, :uploaded_by, NOW())'
            );
            $user = Auth::user();
            $stmt->execute([
                'disk' => 'public',
                'file_name' => $storedName,
                'original_name' => $originalName,
                'storage_path' => $storagePath,
                'mime_type' => $mimeType,
                'size_bytes' => $size,
                'width' => $width,
                'height' => $height,
                'alt_text' => '',
                'caption' => '',
                'uploaded_by' => (int) ($user['id'] ?? 0) ?: null,
            ]);
            $mediaId = (int) $pdo->lastInsertId();
            $this->logActivity($request, 'media.upload', 'media', $mediaId, null, [
                'original_name' => $originalName,
                'storage_path' => $storagePath,
                'mime_type' => $mimeType,
                'size_bytes' => $size,
            ]);

            return [[
                'id' => $mediaId,
                'original_name' => $originalName,
                'storage_path' => $storagePath,
                'mime_type' => $mimeType,
                'url' => path_url($storagePath),
            ], null];
        } catch (PDOException $exception) {
            @unlink($targetPath);
            return [null, 'Unable to save media metadata: ' . $exception->getMessage()];
        }
    }
}
