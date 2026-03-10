<?php
declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\NavigationService;
use App\Services\PageService;
use App\Services\ProductService;
use App\Services\ScriptService;
use App\Services\SeoService;
use App\Services\SettingService;
use PDOException;

final class PageController
{
    private PageService $pageService;
    private ProductService $productService;
    private SettingService $settingService;
    private SeoService $seoService;
    private ScriptService $scriptService;
    private NavigationService $navigationService;

    public function __construct()
    {
        $this->pageService = new PageService();
        $this->productService = new ProductService();
        $this->settingService = new SettingService();
        $this->seoService = new SeoService();
        $this->scriptService = new ScriptService();
        $this->navigationService = new NavigationService();
    }

    public function home(Request $request): void
    {
        $page = $this->pageService->getBySlug('home');
        $products = array_slice($this->productService->getPublished(), 0, 5);

        $this->render(
            'pages/home',
            $request->path(),
            [
                'page' => $page,
                'products' => $products,
                'meta' => $this->seoService->resolve('page', (int) ($page['id'] ?? 0), [
                    'title' => (string) ($page['title'] ?? 'Nutech Paper Products'),
                ]),
            ]
        );
    }

    public function about(Request $request): void
    {
        $page = $this->pageService->getBySlug('about-us');
        $this->render(
            'pages/about',
            $request->path(),
            [
                'page' => $page,
                'meta' => $this->seoService->resolve('page', (int) ($page['id'] ?? 0), [
                    'title' => (string) ($page['title'] ?? 'About Us | Nutech Paper Products'),
                ]),
            ]
        );
    }

    public function contact(Request $request): void
    {
        $page = $this->pageService->getBySlug('contact-us');
        $this->render(
            'pages/contact',
            $request->path(),
            [
                'page' => $page,
                'meta' => $this->seoService->resolve('page', (int) ($page['id'] ?? 0), [
                    'title' => (string) ($page['title'] ?? 'Contact Us | Nutech Paper Products'),
                ]),
                'success' => Session::pullFlash('success'),
                'error' => Session::pullFlash('error'),
            ]
        );
    }

    public function submitContact(Request $request): void
    {
        if (!Csrf::validate((string) $request->input('_csrf'))) {
            Session::flash('error', 'Security token validation failed. Please try again.');
            Response::redirect('/contact-us');
        }

        $name = trim((string) $request->input('full_name', ''));
        $email = trim((string) $request->input('email', ''));
        $message = trim((string) $request->input('message', ''));

        if ($name === '' || $email === '' || $message === '') {
            Session::flash('error', 'Please fill all required fields.');
            Response::redirect('/contact-us');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please provide a valid email address.');
            Response::redirect('/contact-us');
        }

        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'INSERT INTO contact_inquiries (full_name, email, phone, company_name, inquiry_type, message, source_page, status, created_at)
                 VALUES (:full_name, :email, :phone, :company_name, :inquiry_type, :message, :source_page, :status, NOW())'
            );
            $stmt->execute([
                'full_name' => $name,
                'email' => $email,
                'phone' => trim((string) $request->input('phone', '')),
                'company_name' => trim((string) $request->input('company_name', '')),
                'inquiry_type' => trim((string) $request->input('inquiry_type', 'General Inquiry')),
                'message' => $message,
                'source_page' => '/contact-us',
                'status' => 'new',
            ]);
        } catch (PDOException) {
        }

        Session::flash('success', 'Thank you. Our team will get back to you shortly.');
        Response::redirect('/contact-us');
    }

    public function catalog(Request $request): void
    {
        $page = $this->pageService->getBySlug('product-catalog');
        $category = trim((string) $request->query('category', ''));
        $products = $this->productService->getPublished([
            'category' => $category !== '' ? $category : null,
        ]);

        $this->render(
            'pages/product-catalog',
            $request->path(),
            [
                'page' => $page,
                'products' => $products,
                'meta' => $this->seoService->resolve('page', (int) ($page['id'] ?? 0), [
                    'title' => (string) ($page['title'] ?? 'Product Catalog | Nutech Paper Products'),
                ]),
            ]
        );
    }

    public function productDetail(Request $request, array $params): void
    {
        $slug = (string) ($params['slug'] ?? '');
        $product = $this->productService->findBySlug($slug);

        if ($product === null) {
            $this->notFound($request);
            return;
        }

        $this->render(
            'pages/product-detail',
            $request->path(),
            [
                'product' => $product,
                'relatedProducts' => $this->productService->getRelated($slug),
                'meta' => $this->seoService->resolve('product', (int) ($product['id'] ?? 0), [
                    'title' => (string) ($product['title'] ?? 'Product Detail') . ' | Nutech Paper Products',
                    'description' => (string) ($product['short_description'] ?? ''),
                ]),
            ]
        );
    }

    public function notFound(Request $request): void
    {
        $this->render(
            'pages/404',
            $request->path(),
            [
                'meta' => $this->seoService->resolve('global', 0, [
                    'title' => 'Page Not Found | Nutech Paper Products',
                ]),
            ],
            404
        );
    }

    private function render(string $template, string $currentPath, array $data, int $status = 200): void
    {
        $settings = $this->settingService->getGrouped();

        View::render($template, array_merge($data, [
            'site' => $settings['site'] ?? [],
            'theme' => $settings['theme'] ?? [],
            'primaryNav' => $this->navigationService->getMenu('primary'),
            'footerNav' => $this->navigationService->getMenu('footer'),
            'currentPath' => $currentPath,
            'headScripts' => $this->scriptService->getByLocation('head_end'),
            'footerScripts' => $this->scriptService->getByLocation('body_end'),
            'csrfToken' => Csrf::token(),
        ]), 'layouts/public', $status);
    }
}

