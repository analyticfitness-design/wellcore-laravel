<?php

namespace App\Livewire\Admin;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Chat Analytics'])]
class ChatAnalytics extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $expandedSession = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleSession(string $sessionId): void
    {
        $this->expandedSession = $this->expandedSession === $sessionId ? null : $sessionId;
    }

    public function render()
    {
        try {
            // Stats
            $totalConversations = ChatMessage::distinct('session_id')->count('session_id');
            $totalMessages = ChatMessage::count();
            $messagesToday = ChatMessage::whereDate('created_at', today())->count();

            $topQuestions = ChatMessage::where('role', 'user')
                ->select('content', DB::raw('COUNT(*) as count'))
                ->groupBy('content')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            // Conversations table — use page_url only if the column exists
            $hasPageUrl = \Illuminate\Support\Facades\Schema::hasColumn('chat_messages', 'page_url');
            $conversationsQuery = ChatMessage::select(
                    'session_id',
                    DB::raw('MIN(CASE WHEN role = \'user\' THEN content END) as first_message'),
                    DB::raw('COUNT(*) as message_count'),
                    $hasPageUrl ? DB::raw('MAX(page_url) as page_url') : DB::raw('NULL as page_url'),
                    DB::raw('MIN(created_at) as started_at'),
                    DB::raw('MAX(created_at) as last_message_at')
                )
                ->groupBy('session_id')
                ->orderByDesc(DB::raw('MAX(created_at)'));

            if ($this->search) {
                $search = $this->search;
                $conversationsQuery->whereIn('session_id', function ($query) use ($search) {
                    $query->select('session_id')
                        ->from('chat_messages')
                        ->where('content', 'like', '%' . $search . '%');
                });
            }

            $conversations = $conversationsQuery->paginate(20);

            // Expanded conversation messages
            $expandedMessages = [];
            if ($this->expandedSession) {
                $expandedMessages = ChatMessage::where('session_id', $this->expandedSession)
                    ->orderBy('created_at')
                    ->get();
            }
        } catch (\Exception $e) {
            // Table or column schema mismatch (legacy DB) — degrade gracefully
            $totalConversations = 0;
            $totalMessages = 0;
            $messagesToday = 0;
            $topQuestions = collect();
            $conversations = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $expandedMessages = [];
        }

        return view('livewire.admin.chat-analytics', [
            'totalConversations' => $totalConversations,
            'totalMessages' => $totalMessages,
            'messagesToday' => $messagesToday,
            'topQuestions' => $topQuestions,
            'conversations' => $conversations,
            'expandedMessages' => $expandedMessages,
        ]);
    }
}
