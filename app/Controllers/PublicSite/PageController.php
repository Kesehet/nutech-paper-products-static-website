<?php
declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\BlogService;
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
    private BlogService $blogService;
    private SettingService $settingService;
    private SeoService $seoService;
    private ScriptService $scriptService;
    private NavigationService $navigationService;

    public function __construct()
    {
        $this->pageService = new PageService();
        $this->productService = new ProductService();
        $this->blogService = new BlogService();
        $this->settingService = new SettingService();
        $this->seoService = new SeoService();
        $this->scriptService = new ScriptService();
        $this->navigationService = new NavigationService();
    }

    public function home(Request $request): void
    {
        $page = $this->pageService->getBySlug('home');
        $products = array_slice($this->productService->getPublished(), 0, 5);
        $siteTitle = $this->siteTitle();

        $this->render(
            'pages/home',
            $request->path(),
            [
                'page' => $page,
                'products' => $products,
                'meta' => $this->seoService->resolve('page', (int) ($page['id'] ?? 0), [
                    'title' => (string) ($page['title'] ?? $siteTitle),
                ]),
            ]
        );
    }

    public function about(Request $request): void
    {
        $page = $this->pageService->getBySlug('about-us');
        $siteTitle = $this->siteTitle();
        $this->render(
            'pages/about',
            $request->path(),
            [
                'page' => $page,
                'meta' => $this->seoService->resolve('page', (int) ($page['id'] ?? 0), [
                    'title' => (string) ($page['title'] ?? ('About Us | ' . $siteTitle)),
                ]),
            ]
        );
    }

    public function contact(Request $request): void
    {
        $page = $this->pageService->getBySlug('contact-us');
        $siteTitle = $this->siteTitle();
        $this->render(
            'pages/contact',
            $request->path(),
            [
                'page' => $page,
                'meta' => $this->seoService->resolve('page', (int) ($page['id'] ?? 0), [
                    'title' => (string) ($page['title'] ?? ('Contact Us | ' . $siteTitle)),
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
        $siteTitle = $this->siteTitle();
        $category = trim((string) $request->query('category', ''));
        $search = trim((string) $request->query('q', ''));
        $products = $this->productService->getPublished([
            'category' => $category !== '' ? $category : null,
            'search' => $search !== '' ? $search : null,
        ]);
        $categories = $this->productService->getCatalogCategories();

        $this->render(
            'pages/product-catalog',
            $request->path(),
            [
                'page' => $page,
                'products' => $products,
                'categories' => $categories,
                'currentCategory' => $category,
                'search' => $search,
                'meta' => $this->seoService->resolve('page', (int) ($page['id'] ?? 0), [
                    'title' => (string) ($page['title'] ?? ('Product Catalog | ' . $siteTitle)),
                ]),
            ]
        );
    }

    public function blogs(Request $request): void
    {
        $page = $this->pageService->getBySlug('blogs');
        $siteTitle = $this->siteTitle();
        $blogs = $this->blogService->getPublished();

        $this->render(
            'pages/blogs',
            $request->path(),
            [
                'page' => $page,
                'blogs' => $blogs,
                'meta' => $this->seoService->resolve('page', (int) ($page['id'] ?? 0), [
                    'title' => (string) ($page['title'] ?? ('Blogs | ' . $siteTitle)),
                    'description' => 'Latest updates, product stories, and industry insights from ' . $siteTitle . '.',
                ]),
            ]
        );
    }

    public function blogDetail(Request $request, array $params): void
    {
        $slug = (string) ($params['slug'] ?? '');
        $siteTitle = $this->siteTitle();
        $blog = $this->blogService->findPublishedBySlug($slug);

        if ($blog === null) {
            $this->notFound($request);
            return;
        }

        $meta = [
            'title' => (string) ($blog['seo_title'] ?? '') !== '' ? (string) $blog['seo_title'] : ((string) ($blog['title'] ?? 'Blog') . ' | ' . $siteTitle),
            'description' => (string) ($blog['seo_description'] ?? '') !== '' ? (string) $blog['seo_description'] : (string) ($blog['excerpt'] ?? ''),
            'keywords' => (string) ($blog['seo_keywords'] ?? ''),
            'canonical' => (string) ($blog['canonical_url'] ?? ''),
            'robots' => (string) ($blog['robots'] ?? 'index,follow'),
            'og_title' => (string) ($blog['og_title'] ?? '') !== '' ? (string) $blog['og_title'] : ((string) ($blog['seo_title'] ?? '') !== '' ? (string) $blog['seo_title'] : (string) ($blog['title'] ?? 'Blog')),
            'og_description' => (string) ($blog['og_description'] ?? '') !== '' ? (string) $blog['og_description'] : ((string) ($blog['seo_description'] ?? '') !== '' ? (string) $blog['seo_description'] : (string) ($blog['excerpt'] ?? '')),
            'og_image' => !empty($blog['og_image_path']) ? path_url((string) $blog['og_image_path']) : (!empty($blog['featured_image_path']) ? path_url((string) $blog['featured_image_path']) : ''),
            'schema_json' => (string) ($blog['schema_json'] ?? ''),
        ];

        $recommendedBlogs = array_values(array_filter(
            $this->blogService->getPublished(6),
            static fn (array $item): bool => (string) ($item['slug'] ?? '') !== $slug
        ));

        $this->render(
            'pages/blog-detail',
            $request->path(),
            [
                'blog' => $blog,
                'recommendedBlogs' => array_slice($recommendedBlogs, 0, 3),
                'meta' => $meta,
            ]
        );
    }

    public function productDetail(Request $request, array $params): void
    {
        $slug = (string) ($params['slug'] ?? '');
        $siteTitle = $this->siteTitle();
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
                    'title' => (string) ($product['title'] ?? 'Product Detail') . ' | ' . $siteTitle,
                    'description' => (string) ($product['short_description'] ?? ''),
                ]),
            ]
        );
    }

    public function notFound(Request $request): void
    {
        $siteTitle = $this->siteTitle();
        $this->render(
            'pages/404',
            $request->path(),
            [
                'meta' => $this->seoService->resolve('global', 0, [
                    'title' => 'Page Not Found | ' . $siteTitle,
                ]),
            ],
            404
        );
    }

    private function siteTitle(): string
    {
        $settings = $this->settingService->getGrouped();
        return (string) ($settings['site']['title'] ?? 'Nuteck Paper Products');
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
            'headStartScripts' => $this->scriptService->getByLocation('head_start'),
            'headEndScripts' => $this->scriptService->getByLocation('head_end'),
            'footerScripts' => $this->scriptService->getByLocation('body_end'),
            'csrfToken' => Csrf::token(),
        ]), 'layouts/public', $status);
    }
}
