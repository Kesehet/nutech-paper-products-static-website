<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Services\ContactInquiryService;

final class ContactInquiryAdminController extends BaseAdminController
{
    private ContactInquiryService $contactInquiryService;

    public function __construct()
    {
        $this->contactInquiryService = new ContactInquiryService();
    }

    public function index(Request $request): void
    {
        $this->requireAdmin();

        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;

        $result = $this->contactInquiryService->getPaginatedForAdmin($search, $page, $perPage);
        $pagination = $this->buildPagination((int) ($result['total'] ?? 0), $page, $perPage);

        $this->render('admin/inquiries/index', $request, [
            'meta' => [
                'title' => 'Inquiries | Nuteck Admin',
                'description' => 'Review customer contact submissions.',
            ],
            'inquiries' => $result['rows'] ?? [],
            'search' => $search,
            'pagination' => $pagination,
        ]);
    }

    public function export(Request $request): void
    {
        $this->requireAdmin();

        $search = trim((string) $request->input('q', ''));
        $redirectPath = '/admin/inquiries';
        if ($search !== '') {
            $redirectPath = query_url('/admin/inquiries', ['q' => $search]);
        }

        $this->validateCsrfOrRedirect($request, $redirectPath);

        $rows = $this->contactInquiryService->getExportRows($search);
        $filename = 'contact-inquiries-' . date('Ymd-His') . '.csv';

        $this->logActivity($request, 'contact_inquiry.export', 'contact_inquiry', null, [
            'search' => $search,
            'row_count' => count($rows),
        ], null);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');

        $output = fopen('php://output', 'wb');
        if ($output === false) {
            Response::redirect($redirectPath);
        }

        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $this->contactInquiryService->csvHeaders());
        foreach ($rows as $row) {
            fputcsv($output, $this->contactInquiryService->toCsvRow($row));
        }
        fclose($output);
        exit;
    }
}
