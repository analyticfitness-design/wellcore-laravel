<div class="flex h-[calc(100vh-10rem)] flex-col space-y-0"
     x-data="{ autoScroll() { this.$nextTick(() => { const el = document.getElementById('chat-messages'); if(el) el.scrollTop = el.scrollHeight; }); } }"
     x-init="autoScroll()"
     x-on:message-sent.window="autoScroll()"
     wire:poll.5s>

    {{-- Page header --}}
    <div class="mb-4">
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">CHAT GRUPAL RISE</h1>
        @if($podName)
            <div class="mt-1 flex items-center gap-2">
                <p class="text-sm text-wc-text-tertiary">{{ $podName }}</p>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-medium text-emerald-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ $memberCount }} {{ $memberCount === 1 ? 'miembro' : 'miembros' }}
                </span>
            </div>
        @else
            <p class="mt-1 text-sm text-wc-text-tertiary">Conecta con tu grupo RISE</p>
        @endif
    </div>

    {{-- Chat container --}}
    <div class="flex flex-1 flex-col overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">

        {{-- Messages area --}}
        <div id="chat-messages" class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
            @if($podId)
                @forelse($messages as $msg)
                    @if($msg['isOwn'])
                        {{-- Own message (right aligned) --}}
                        <div class="flex justify-end gap-2">
                            <div class="max-w-[75%] sm:max-w-[60%]">
                                <div class="rounded-2xl rounded-br-sm bg-amber-500 px-4 py-2.5 text-sm text-white">
                                    {{ $msg['message'] }}
                                </div>
                                <p class="mt-0.5 text-right text-[10px] text-wc-text-tertiary">{{ $msg['time'] }}</p>
                            </div>
                        </div>
                    @else
                        {{-- Other user's message (left aligned) --}}
                        <div class="flex gap-2">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-500/20 to-amber-600/10">
                                <span class="text-xs font-semibold text-amber-500">{{ $msg['initial'] }}</span>
                            </div>
                            <div class="max-w-[75%] sm:max-w-[60%]">
                                <p class="mb-0.5 text-[11px] font-medium text-amber-500">{{ $msg['name'] }}</p>
                                <div class="rounded-2xl rounded-bl-sm bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text">
                                    {{ $msg['message'] }}
                                </div>
                                <p class="mt-0.5 text-[10px] text-wc-text-tertiary">{{ $msg['time'] }}</p>
                            </div>
                        </div>
                    @endif
                @empty
                    {{-- Empty state --}}
                    <div class="flex h-full flex-col items-center justify-center py-12">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-wc-bg-secondary">
                            <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                            </svg>
                        </div>
                        <p class="mt-4 text-sm font-medium text-wc-text">Sin mensajes aun</p>
                        <p class="mt-1 text-xs text-wc-text-tertiary">Se el primero en escribir!</p>
                    </div>
                @endforelse
            @else
                {{-- No pod assigned --}}
                <div class="flex h-full flex-col items-center justify-center py-12">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-500/10">
                        <svg class="h-8 w-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                    <p class="mt-4 text-sm font-medium text-wc-text">Sin grupo asignado</p>
                    <p class="mt-1 text-center text-xs text-wc-text-tertiary">Aun no has sido asignado a un pod de accountability.<br>Tu coach te asignara pronto.</p>
                </div>
            @endif
        </div>

        {{-- Input bar --}}
        @if($podId)
            <div class="border-t border-wc-border bg-wc-bg-secondary/50 p-3">
                <form wire:submit="sendMessage" class="flex items-center gap-2">
                    <input type="text"
                           wire:model="newMessage"
                           placeholder="Escribe un mensaje..."
                           class="flex-1 rounded-full border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                           autocomplete="off"
                           x-on:keydown.enter.prevent="$el.closest('form').requestSubmit()">

                    <button type="submit"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover transition-all"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendMessage">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </span>
                        <span wire:loading wire:target="sendMessage">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>
                    </button>
                </form>
                @error('newMessage')
                    <p class="mt-1 px-4 text-xs text-wc-accent">{{ $message }}</p>
                @enderror
            </div>
        @endif
    </div>
</div>
