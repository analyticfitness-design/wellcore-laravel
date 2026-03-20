<div wire:poll.5s="pollMessages">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">CHAT CON TU COACH</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Comunicate directamente con tu coach</p>
    </div>

    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
        {{-- Coach Info Bar --}}
        @if($coachName)
            <div class="flex items-center gap-3 border-b border-wc-border px-5 py-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/20">
                    <span class="text-sm font-bold text-wc-accent">{{ substr($coachName, 0, 1) }}</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-wc-text">{{ $coachName }}</p>
                    <div class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-green-500"></span>
                        <span class="text-xs text-wc-text-tertiary">Coach</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Messages Area --}}
        <div
            x-data="{
                scrollToBottom() {
                    this.$nextTick(() => {
                        const el = this.$refs.messagesContainer;
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                }
            }"
            x-init="scrollToBottom()"
            x-on:message-sent.window="scrollToBottom()"
        >
            <div
                x-ref="messagesContainer"
                class="h-[28rem] overflow-y-auto px-5 py-4 space-y-4 sm:h-[32rem]"
            >
                @if($messages->isEmpty())
                    {{-- Empty State --}}
                    <div class="flex h-full flex-col items-center justify-center text-center">
                        <svg class="h-16 w-16 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                        </svg>
                        <h3 class="mt-4 font-display text-xl text-wc-text">SIN MENSAJES</h3>
                        <p class="mt-2 max-w-xs text-sm text-wc-text-secondary">Envia un mensaje a tu coach para comenzar la conversacion.</p>
                    </div>
                @else
                    @foreach($messages as $msg)
                        @if($msg->direction === 'coach_to_client')
                            {{-- Coach Message (left) --}}
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-wc-bg-secondary">
                                    <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                </div>
                                <div class="max-w-[75%]">
                                    <div class="rounded-2xl rounded-tl-sm bg-wc-bg-secondary px-4 py-2.5">
                                        <p class="text-sm text-wc-text">{{ $msg->message }}</p>
                                    </div>
                                    <p class="mt-1 px-1 text-[10px] text-wc-text-tertiary">
                                        {{ $msg->created_at?->format('d/m H:i') }}
                                    </p>
                                </div>
                            </div>
                        @else
                            {{-- Client Message (right) --}}
                            <div class="flex items-start justify-end gap-3">
                                <div class="max-w-[75%]">
                                    <div class="rounded-2xl rounded-tr-sm bg-wc-accent px-4 py-2.5">
                                        <p class="text-sm text-white">{{ $msg->message }}</p>
                                    </div>
                                    <p class="mt-1 px-1 text-right text-[10px] text-wc-text-tertiary">
                                        {{ $msg->created_at?->format('d/m H:i') }}
                                        @if($msg->read_at)
                                            <svg class="ml-1 inline h-3 w-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Input Area --}}
        <div class="border-t border-wc-border px-4 py-3">
            <form wire:submit="sendMessage" class="flex items-end gap-3">
                <div class="flex-1">
                    <textarea
                        wire:model="message"
                        rows="1"
                        placeholder="Escribe un mensaje..."
                        class="block w-full resize-none rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                        x-on:keydown.enter.prevent="if (!$event.shiftKey) { $wire.sendMessage(); }"
                    ></textarea>
                    @error('message') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-wc-accent text-white transition-all hover:bg-wc-accent-hover active:scale-95 disabled:opacity-60"
                >
                    <svg wire:loading.remove wire:target="sendMessage" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                    <svg wire:loading wire:target="sendMessage" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
