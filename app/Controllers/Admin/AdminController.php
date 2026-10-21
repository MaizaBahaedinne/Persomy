<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Response;
use App\Core\Session;

abstract class AdminController extends Controller
{
    protected function adminView(string $template, array $data = [], int $status = 200): Response
    {
        $data += ['title' => 'Administration', 'active' => ''];
        return $this->view($template, $data, 'layouts/admin', $status)->noCache();
    }

    protected function success(string $message): void
    {
        Session::flash('success', $message);
    }

    protected function failure(string $message): void
    {
        Session::flash('error', $message);
    }

    protected function checkbox(\App\Core\Request $request, string $key): bool
    {
        return $request->input($key) === '1';
    }
}
