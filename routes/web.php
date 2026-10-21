<?php

declare(strict_types=1);

use App\Controllers\Admin;
use App\Controllers\ContactController;
use App\Controllers\PublicController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

/** @var App\Core\Router $router */

$lang = '/{lang:fr|en|ar}';
$auth = [AuthMiddleware::class];
$authPost = [AuthMiddleware::class, CsrfMiddleware::class];

// --- Site public ---
$router->get('/', [PublicController::class, 'root']);
$router->get('/sitemap.xml', [PublicController::class, 'sitemap']);
$router->get('/robots.txt', [PublicController::class, 'robots']);

$router->get($lang, [PublicController::class, 'home']);
$router->get($lang . '/about', [PublicController::class, 'about']);
$router->get($lang . '/services', [PublicController::class, 'services']);
$router->get($lang . '/portfolio', [PublicController::class, 'portfolio']);
$router->get($lang . '/portfolio/{slug:[a-z0-9]+(?:-[a-z0-9]+)*}', [PublicController::class, 'project']);
$router->get($lang . '/legal', [PublicController::class, 'legal']);
$router->get($lang . '/privacy', [PublicController::class, 'privacy']);
$router->get($lang . '/contact', [ContactController::class, 'show']);
$router->post($lang . '/contact', [ContactController::class, 'submit'], [CsrfMiddleware::class]);

// --- Administration ---
$router->get('/admin/login', [Admin\AuthController::class, 'showLogin']);
$router->post('/admin/login', [Admin\AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->post('/admin/logout', [Admin\AuthController::class, 'logout'], $authPost);

$router->get('/admin', [Admin\DashboardController::class, 'index'], $auth);

$router->get('/admin/projects', [Admin\ProjectController::class, 'index'], $auth);
$router->get('/admin/projects/create', [Admin\ProjectController::class, 'create'], $auth);
$router->post('/admin/projects', [Admin\ProjectController::class, 'store'], $authPost);
$router->get('/admin/projects/{id:\d+}/edit', [Admin\ProjectController::class, 'edit'], $auth);
$router->post('/admin/projects/{id:\d+}', [Admin\ProjectController::class, 'update'], $authPost);
$router->post('/admin/projects/{id:\d+}/toggle', [Admin\ProjectController::class, 'toggle'], $authPost);
$router->post('/admin/projects/{id:\d+}/delete', [Admin\ProjectController::class, 'destroy'], $authPost);

$router->get('/admin/categories', [Admin\CategoryController::class, 'index'], $auth);
$router->get('/admin/categories/create', [Admin\CategoryController::class, 'create'], $auth);
$router->post('/admin/categories', [Admin\CategoryController::class, 'store'], $authPost);
$router->get('/admin/categories/{id:\d+}/edit', [Admin\CategoryController::class, 'edit'], $auth);
$router->post('/admin/categories/{id:\d+}', [Admin\CategoryController::class, 'update'], $authPost);
$router->post('/admin/categories/{id:\d+}/delete', [Admin\CategoryController::class, 'destroy'], $authPost);

$router->get('/admin/services', [Admin\ServiceController::class, 'index'], $auth);
$router->get('/admin/services/create', [Admin\ServiceController::class, 'create'], $auth);
$router->post('/admin/services', [Admin\ServiceController::class, 'store'], $authPost);
$router->get('/admin/services/{id:\d+}/edit', [Admin\ServiceController::class, 'edit'], $auth);
$router->post('/admin/services/{id:\d+}', [Admin\ServiceController::class, 'update'], $authPost);
$router->post('/admin/services/{id:\d+}/delete', [Admin\ServiceController::class, 'destroy'], $authPost);

$router->get('/admin/pages', [Admin\PageController::class, 'index'], $auth);
$router->get('/admin/pages/{id:\d+}/edit', [Admin\PageController::class, 'edit'], $auth);
$router->post('/admin/pages/{id:\d+}', [Admin\PageController::class, 'update'], $authPost);

$router->get('/admin/settings', [Admin\SettingController::class, 'edit'], $auth);
$router->post('/admin/settings', [Admin\SettingController::class, 'update'], $authPost);

$router->get('/admin/messages', [Admin\MessageController::class, 'index'], $auth);
$router->post('/admin/messages/purge', [Admin\MessageController::class, 'purge'], $authPost);
$router->get('/admin/messages/{id:\d+}', [Admin\MessageController::class, 'show'], $auth);
$router->post('/admin/messages/{id:\d+}/status', [Admin\MessageController::class, 'status'], $authPost);
$router->post('/admin/messages/{id:\d+}/delete', [Admin\MessageController::class, 'destroy'], $authPost);

$router->get('/admin/account', [Admin\AccountController::class, 'edit'], $auth);
$router->post('/admin/account', [Admin\AccountController::class, 'update'], $authPost);
