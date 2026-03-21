<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="font-display text-2xl uppercase tracking-wide text-wc-text">Chat Analytics</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Monitorea las conversaciones del chatbot</p>
    </div>

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Conversations --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Conversaciones</p>
                    <p class="mt-1 text-2xl font-bold text-wc-text">{{ number_format($totalConversations) }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Messages --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Mensajes Totales</p>
                    <p class="mt-1 text-2xl font-bold text-wc-text">{{ number_format($totalMessages) }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10">
                    <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Messages Today --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Mensajes Hoy</p>
                    <p class="mt-1 text-2xl font-bold text-wc-text">{{ number_format($messagesToday) }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10">
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Top Question --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Top Pregunta</p>
                    <p class="mt-1 truncate text-sm font-semibold text-wc-text">
                        {{ $topQuestions->first()?->message ?? 'Sin datos' }}
                    </p>
                    @if($topQuestions->first())
                        <p class="text-xs text-wc-text-tertiary">{{ $topQuestions->first()->count }} veces</p>
                    @endif
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-500/10">
                    <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Questions List --}}
    @if($topQuestions->count() > 1)
        <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="mb-3 text-sm font-semibold text-wc-text">Preguntas Frecuentes</h3>
            <div class="space-y-2">
                @foreach($topQuestions as $q)
                    <div class="flex items-center justify-between rounded-lg bg-wc-bg-secondary px-3 py-2">
                        <span class="truncate text-sm text-wc-text-secondary">{{ $q->message }}</span>
                        <span class="ml-3 shrink-0 rounded-full bg-wc-accent/10 px-2 py-0.5 text-xs font-semibold text-wc-accent">{{ $q->count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Search + Conversations Table --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
        {{-- Search bar --}}
        <div class="border-b border-wc-border px-5 py-4">
            <div class="flex items-center gap-3">
                <h3 class="text-sm font-semibold text-wc-text">Conversaciones</h3>
                <div class="ml-auto w-full max-w-xs">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar en mensajes..."
                        class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                    >
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-wc-bg-secondary">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Session</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Primera Pregunta</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Mensajes</th>
                        <th class="hidden px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary sm:table-cell">Pagina</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Ver</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-wc-border">
                    @forelse($conversations as $conv)
                        <tr class="hover:bg-wc-bg-secondary/50 transition-colors">
                            <td class="px-5 py-3">
                                <span class="rounded-md bg-wc-bg-secondary px-2 py-1 font-mono text-xs text-wc-text-tertiary">
                                    {{ Str::limit($conv->session_id, 12) }}
                                </span>
                            </td>
                            <td class="max-w-[200px] truncate px-5 py-3 text-wc-text-secondary">
                                {{ $conv->first_message ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center rounded-full bg-wc-accent/10 px-2 py-0.5 text-xs font-semibold text-wc-accent">
                                    {{ $conv->message_count }}
                                </span>
                            </td>
                            <td class="hidden max-w-[150px] truncate px-5 py-3 text-xs text-wc-text-tertiary sm:table-cell">
                                {{ $conv->page_url ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-xs text-wc-text-tertiary">
                                {{ \Carbon\Carbon::parse($conv->started_at)->format('d M H:i') }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <button
                                    wire:click="toggleSession('{{ $conv->session_id }}')"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors"
                                >
                                    <svg class="h-4 w-4 transition-transform {{ $expandedSession === $conv->session_id ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        {{-- Expanded conversation --}}
                        @if($expandedSession === $conv->session_id)
                            <tr>
                                <td colspan="6" class="bg-wc-bg px-5 py-4">
                                    <div class="max-h-80 space-y-3 overflow-y-auto rounded-lg border border-wc-border bg-wc-bg-secondary p-4">
                                        @foreach($expandedMessages as $msg)
                                            <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                                                @if($msg->role === 'assistant')
                                                    <div class="flex gap-2 max-w-[85%]">
                                                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                                                            <span class="text-[9px] font-bold text-wc-accent">W</span>
                                                        </div>
                                                        <div>
                                                            <div class="rounded-lg rounded-tl-sm bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text-secondary">
                                                                {{ $msg->message }}
                                                            </div>
                                                            <p class="mt-0.5 text-[10px] text-wc-text-tertiary">{{ $msg->created_at->format('H:i') }}</p>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="max-w-[85%]">
                                                        <div class="rounded-lg rounded-tr-sm bg-wc-accent px-3 py-2 text-sm text-white">
                                                            {{ $msg->message }}
                                                        </div>
                                                        <p class="mt-0.5 text-right text-[10px] text-wc-text-tertiary">{{ $msg->created_at->format('H:i') }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-wc-text-tertiary">
                                <svg class="mx-auto mb-3 h-10 w-10 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                                </svg>
                                @if($search)
                                    No se encontraron conversaciones con "{{ $search }}"
                                @else
                                    Aun no hay conversaciones del chatbot
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($conversations->hasPages())
            <div class="border-t border-wc-border px-5 py-3">
                {{ $conversations->links() }}
            </div>
        @endif
    </div>
</div>
