<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Admin;

final class AccountController extends AdminController
{
    public function edit(Request $request, array $params): Response
    {
        return $this->form([], 200);
    }

    public function update(Request $request, array $params): Response
    {
        $user = Auth::user() ?? $this->abort(403);
        $post = $request->post;
        $current = is_string($post['current_password'] ?? null) ? $post['current_password'] : '';
        $new = is_string($post['new_password'] ?? null) ? $post['new_password'] : '';
        $confirm = is_string($post['confirm_password'] ?? null) ? $post['confirm_password'] : '';

        $errors = [];
        if (!password_verify($current, $user['password_hash'])) {
            $errors[] = 'Le mot de passe actuel est incorrect.';
        }
        if (mb_strlen($new) < 12) {
            $errors[] = 'Le nouveau mot de passe doit contenir au moins 12 caractères.';
        }
        if (mb_strlen($new) > 200) {
            $errors[] = 'Le nouveau mot de passe est trop long.';
        }
        if ($new !== $confirm) {
            $errors[] = 'La confirmation ne correspond pas.';
        }
        if ($errors) {
            return $this->form($errors, 422);
        }

        Admin::updatePassword((int) $user['id'], $new);
        Session::regenerate();
        $this->success('Mot de passe modifié.');
        return $this->redirect('/admin/account');
    }

    private function form(array $errors, int $status): Response
    {
        return $this->adminView('admin/account', ['title' => 'Mon compte', 'active' => 'account', 'errors' => $errors], $status);
    }
}
