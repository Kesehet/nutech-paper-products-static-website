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
    private const FIELD_DEFINITIONS = [
        'site.title' => [
            'type' => 'string',
            'input' => 'text',
            'section' => 'Branding',
            'label' => 'Site Title',
            'description' => 'Shown in header, footer, and default page metadata.',
        ],
        'site.footer_tagline' => [
            'type' => 'string',
            'input' => 'textarea',
            'section' => 'Branding',
            'label' => 'Footer Tagline',
            'description' => 'Text shown under the footer logo.',
        ],
        'site.logo_path' => [
            'type' => 'string',
            'input' => 'text',
            'section' => 'Branding',
            'label' => 'Logo Path',
            'description' => 'Use an absolute URL or a local path like /assets/img/nuteck_square_logo.png.',
        ],
        'site.home_hero_image' => [
            'type' => 'string',
            'input' => 'text',
            'section' => 'Homepage Hero',
            'label' => 'Hero Image URL/Path',
            'description' => 'Main image shown on the homepage hero section.',
        ],
        'site.home_hero_image_alt' => [
            'type' => 'string',
            'input' => 'text',
            'section' => 'Homepage Hero',
            'label' => 'Hero Image Alt Text',
            'description' => 'Accessibility text for the homepage hero image.',
        ],
        'site.contact_email' => [
            'type' => 'string',
            'input' => 'email',
            'section' => 'Contact',
            'label' => 'Contact Email',
            'description' => 'Displayed on the website and used for customer contact info.',
        ],
        'site.contact_phone' => [
            'type' => 'string',
            'input' => 'text',
            'section' => 'Contact',
            'label' => 'Contact Phone',
            'description' => 'Displayed on the website in contact and footer sections.',
        ],
        'site.address' => [
            'type' => 'string',
            'input' => 'textarea',
            'section' => 'Contact',
            'label' => 'Address',
            'description' => 'Primary business address shown on the website.',
        ],
        'site.social_linkedin' => [
            'type' => 'string',
            'input' => 'url',
            'section' => 'Social',
            'label' => 'LinkedIn URL',
            'description' => 'Public LinkedIn page URL.',
        ],
        'site.social_facebook' => [
            'type' => 'string',
            'input' => 'url',
            'section' => 'Social',
            'label' => 'Facebook URL',
            'description' => 'Public Facebook page URL.',
        ],
        'site.social_instagram' => [
            'type' => 'string',
            'input' => 'url',
            'section' => 'Social',
            'label' => 'Instagram URL',
            'description' => 'Public Instagram profile URL.',
        ],
        'theme.primary_color' => [
            'type' => 'string',
            'input' => 'color',
            'section' => 'Theme',
            'label' => 'Primary Color',
            'description' => 'Main brand accent color.',
        ],
        'theme.primary_hover_color' => [
            'type' => 'string',
            'input' => 'color',
            'section' => 'Theme',
            'label' => 'Primary Hover Color',
            'description' => 'Hover state color for primary actions.',
        ],
        'theme.dark_navy_color' => [
            'type' => 'string',
            'input' => 'color',
            'section' => 'Theme',
            'label' => 'Dark Navy Color',
            'description' => 'Dark background color used in header/footer and highlights.',
        ],
        'theme.background_light_color' => [
            'type' => 'string',
            'input' => 'color',
            'section' => 'Theme',
            'label' => 'Background Light Color',
            'description' => 'Base light background tone for page sections.',
        ],
    ];

    public function index(Request $request): void
    {
        $this->requireAdmin();

        $settingService = new SettingService();
        $grouped = $settingService->getGrouped();
        $defaults = $settingService->getDefaults();

        $sections = [];
        foreach (self::FIELD_DEFINITIONS as $flatKey => $definition) {
            [$group, $key] = explode('.', $flatKey, 2);
            $section = (string) ($definition['section'] ?? 'General');

            $sections[$section][] = [
                'flat_key' => $flatKey,
                'input_name' => str_replace('.', '__', $flatKey),
                'label' => (string) ($definition['label'] ?? $flatKey),
                'description' => (string) ($definition['description'] ?? ''),
                'input' => (string) ($definition['input'] ?? 'text'),
                'value' => (string) ($grouped[$group][$key] ?? ''),
                'default' => (string) ($defaults[$group][$key] ?? ''),
            ];
        }

        $this->render('admin/settings/index', $request, [
            'meta' => [
                'title' => 'Site Settings | Nuteck Admin',
                'description' => 'Manage site identity, contact info, and theme values.',
            ],
            'sections' => $sections,
            'fieldCount' => count(self::FIELD_DEFINITIONS),
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

            foreach (self::FIELD_DEFINITIONS as $flatKey => $definition) {
                [$group, $key] = explode('.', $flatKey, 2);
                $value = trim((string) $request->input(str_replace('.', '__', $flatKey), ''));
                $type = (string) ($definition['type'] ?? 'string');

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
                'field_count' => count(self::FIELD_DEFINITIONS),
            ]);
            Session::flash('success', 'Settings saved successfully.');
        } catch (PDOException $exception) {
            Session::flash('error', 'Unable to save settings: ' . $exception->getMessage());
        }

        Response::redirect('/admin/settings');
    }
}
