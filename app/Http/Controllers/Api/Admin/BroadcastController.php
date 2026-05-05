<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BroadcastMessage;
use App\Services\BroadcastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function __construct(private BroadcastService $service) {}

    public function preview(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $data = $request->validate([
            'audience' => 'required|in:clients,coaches,all_communities,segmented',
            'segment' => 'array',
        ]);

        $count = $this->service->previewRecipients($data['audience'], $data['segment'] ?? []);

        return response()->json(['count' => $count]);
    }

    public function send(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $data = $request->validate([
            'audience' => 'required|in:clients,coaches,all_communities,segmented',
            'segment' => 'array',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string|max:10000',
            'push_enabled' => 'boolean',
        ]);

        $bc = $this->service->dispatch(
            sender: $admin,
            senderType: 'admin',
            audience: $data['audience'],
            segment: $data['segment'] ?? [],
            subject: $data['subject'] ?? null,
            body: $data['body'],
            pushEnabled: (bool) ($data['push_enabled'] ?? false),
        );

        return response()->json([
            'broadcast_id' => $bc->id,
            'recipients_count' => $bc->recipients_count,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $admin = $request->user();
        abort_unless($this->isAdmin($admin), 403);

        $page = BroadcastMessage::query()
            ->orderByDesc('sent_at')
            ->paginate(20);

        return response()->json($page);
    }

    private function isAdmin(mixed $user): bool
    {
        if (! $user instanceof Admin) {
            return false;
        }

        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        return in_array($role, ['admin', 'superadmin', 'jefe'], true);
    }
}
