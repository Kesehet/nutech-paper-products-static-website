<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\SettingService;
use PDOException;

final class SettingsAdminController extends BaseAdminController
{
    private const FIELDS = [
        'site.title' => 'string',
        'site.logo_path' => 'string',
        'site.home_hero_image' => 'string',
        'site.home_hero_image_alt' => 'string',
        'site.contact_email' => 'string',
        'site.contact_phone' => 'string',
        'site.address' => 'string',
        'site.social_linkedin' => 'string',
        'site.social_facebook' => 'string',
        'site.social_instagram' => 'string',
        'theme.primary_color' => 'string',
        'theme.primary_hover_color' => 'string',
        'theme.dark_navy_color' => 'string',
        'theme.background_light_color' => 'string',
    ];

    public function index(Request $request): void
    {
        $this->requireAdmin();

        $values = [];
        $grouped = (new SettingService())->getGrouped();
        foreach (array_keys(self::FIELDS) as $flatKey) {
            [$group, $key] = explode('.', $flatKey, 2);
            $values[$flatKey] = (string) ($grouped[$group][$key] ?? '');
        }

        $this->render('admin/settings/index', $request, [
            'meta' => [
                'title' => 'Site Settings | Nutech Admin',
                'description' => 'Manage site identity, contact info, and theme values.',
            ],
            'values' => $values,
            'fields' => self::FIELDS,
        ]);
    }

    public function save(Request $request): void
    {
        $this->requireAdmin();
        $this->validateCsrfOrRedirect($request, '/admin/settings');

        $user = Auth::user();
        $userId = (int) ($user['id'] ?? 0);

        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'INSERT INTO settings
                    (setting_group, setting_key, setting_value, value_type, is_public, updated_by, created_at, updated_at)
                 VALUES
                    (:setting_group, :setting_key, :setting_value, :value_type, :is_public, :updated_by, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    setting_value = VALUES(setting_value),
                    value_type = VALUES(value_type),
                    is_public = VALUES(is_public),
                    updated_by = VALUES(updated_by),
                    updated_at = NOW()'
            );

            foreach (self::FIELDS as $flatKey => $type) {
                [$group, $key] = explode('.', $flatKey, 2);
                $value = trim((string) $request->input(str_replace('.', '__', $flatKey), ''));

                $stmt->execute([
                    'setting_group' => $group,
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'value_type' => $type,
                    'is_public' => 1,
                    'updated_by' => $userId > 0 ? $userId : null,
                ]);
            }

            $this->logActivity($request, 'settings.save', 'settings', null, null, [
                'field_count' => count(self::FIELDS),
            ]);
            Session::flash('success', 'Settings saved successfully.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to save settings: ' . $exception->getMessage());
        }

        Response::redirect('/admin/settings');
    }
}
