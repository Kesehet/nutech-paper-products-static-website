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

        $media = [];
        try {
            $pdo = Database::connection();
            $stmt = $pdo->query(
                'SELECT m.id, m.file_name, m.original_name, m.storage_path, m.mime_type, m.size_bytes, m.width, m.height, m.created_at,
                        u.full_name AS uploaded_by_name
                 FROM media m
                 LEFT JOIN users u ON u.id = m.uploaded_by
                 ORDER BY m.id DESC'
            );
            $media = $stmt->fetchAll() ?: [];
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to load media library: ' . $exception->getMessage());
        }

        $this->render('admin/media/index', $request, [
            'meta' => [
                'title' => 'Media Library | Nutech Admin',
                'description' => 'Upload and manage reusable media assets.',
            ],
            'media' => $media,
        ]);
    }

    public function upload(Request $request): void
    {
        $this->requireAuth();
        $this->validateCsrfOrRedirect($request, '/admin/media');

        $file = $request->files()['media_file'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please choose a valid file to upload.');
            Response::redirect('/admin/media');
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        $originalName = (string) ($file['name'] ?? 'upload');
        $size = (int) ($file['size'] ?? 0);
        $maxSize = (int) env('UPLOAD_MAX_SIZE', 5242880);
        if ($size <= 0 || $size > $maxSize) {
            Session::flash('error', 'File size exceeds upload limit.');
            Response::redirect('/admin/media');
        }

        $allowedExt = array_filter(array_map('trim', explode(',', (string) env('UPLOAD_ALLOWED_EXT', 'jpg,jpeg,png,webp,gif,svg,pdf'))));
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExt, true)) {
            Session::flash('error', 'File extension not allowed.');
            Response::redirect('/admin/media');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? (string) finfo_file($finfo, $tmpPath) : 'application/octet-stream';
        if ($finfo) {
            finfo_close($finfo);
        }

        $baseUploadDir = trim((string) env('UPLOAD_DIR', 'public/uploads'));
        $absoluteUploadDir = BASE_PATH . '/' . trim($baseUploadDir, '/');
        if (!is_dir($absoluteUploadDir) && !@mkdir($absoluteUploadDir, 0775, true) && !is_dir($absoluteUploadDir)) {
            Session::flash('error', 'Unable to create upload directory.');
            Response::redirect('/admin/media');
        }

        $storedName = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $targetPath = $absoluteUploadDir . '/' . $storedName;
        if (!move_uploaded_file($tmpPath, $targetPath)) {
            Session::flash('error', 'File upload failed.');
            Response::redirect('/admin/media');
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
            Session::flash('success', 'Media uploaded successfully.');
        } catch (PDOException $exception) {
            @unlink($targetPath);
            Session::flash('error', 'Unable to save media metadata: ' . $exception->getMessage());
        }

        Response::redirect('/admin/media');
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
                    (SELECT COUNT(*) FROM product_images WHERE media_id = :media_id) +
                    (SELECT COUNT(*) FROM page_sections WHERE featured_media_id = :media_id) +
                    (SELECT COUNT(*) FROM seo_meta WHERE og_image_id = :media_id) AS usage_count'
            );
            $usageStmt->execute(['media_id' => $id]);
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
}

