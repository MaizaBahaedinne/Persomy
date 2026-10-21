<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\Setting;
use App\Models\Submission;

final class MessageController extends AdminController
{
    private const PER_PAGE = 20;

    public function index(Request $request, array $params): Response
    {
        $status = $request->query('status');
        $status = isset(Submission::STATUSES[$status]) ? $status : null;
        $page = max(1, (int) $request->query('page', '1'));
        $result = Submission::paginate($status, $page, self::PER_PAGE);

        return $this->adminView('admin/messages/index', [
            'title' => 'Messages',
            'active' => 'messages',
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / self::PER_PAGE)),
            'status' => $status,
            'retention' => (int) (Setting::get('contact_retention_days', '365') ?: 365),
        ]);
    }

    public function show(Request $request, array $params): Response
    {
        $message = Submission::find((int) $params['id']) ?? $this->abort(404);
        return $this->adminView('admin/messages/show', [
            'title' => 'Demande de ' . $message['name'],
            'active' => 'messages',
            'message' => $message,
        ]);
    }

    public function status(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        Submission::find($id) ?? $this->abort(404);
        $status = $request->input('status');
        if (isset(Submission::STATUSES[$status])) {
            Submission::setStatus($id, $status);
            $this->success('Statut mis à jour.');
        } else {
            $this->failure('Statut invalide.');
        }
        return $this->redirect('/admin/messages/' . $id);
    }

    public function destroy(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        Submission::find($id) ?? $this->abort(404);
        Submission::delete($id);
        $this->success('Demande supprimée.');
        return $this->redirect('/admin/messages');
    }

    public function purge(Request $request, array $params): Response
    {
        $days = (int) (Setting::get('contact_retention_days', '365') ?: 365);
        $count = Submission::purgeOlderThan($days);
        $this->success($count . ' demande(s) de plus de ' . $days . ' jours supprimée(s).');
        return $this->redirect('/admin/messages');
    }
}
