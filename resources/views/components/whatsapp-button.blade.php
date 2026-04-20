{{-- WellCore Chat Widget --}}
<div x-data="{
        chatOpen: false,
        messages: [
            { role: 'assistant', text: 'Hola! Soy el asistente de WellCore. Puedo ayudarte con informacion sobre nuestros planes, el metodo, precios o cualquier duda que tengas. Como puedo ayudarte?' }
        ],
        quickReplies: [
            'Cuanto cuestan los planes?',
            'Como funciona el metodo?',
            'Que incluye el programa RISE?',
            'Como contacto un coach?'
        ],
        showQuickReplies: true,
        newMessage: '',
        loading: false,
        sessionId: '',
        init() {
            let stored = sessionStorage.getItem('wc_chat_session');
            if (stored) {
                this.sessionId = stored;
            } else {
                this.sessionId = this.generateId();
                sessionStorage.setItem('wc_chat_session', this.sessionId);
            }
        },
        generateId() {
            return 'wc_' + Date.now().toString(36) + '_' + Math.random().toString(36).substring(2, 10);
        },
        sendQuickReply(text) {
            this.newMessage = text;
            this.showQuickReplies = false;
            this.sendMessage();
        },
        async sendMessage() {
            if (!this.newMessage.trim() || this.loading) return;

            const userMsg = this.newMessage.trim();
            this.messages.push({ role: 'user', text: userMsg });
            this.newMessage = '';
            this.loading = true;
            this.showQuickReplies = false;

            this.$nextTick(() => this.scrollToBottom());

            try {
                const res = await fetch('/api/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message: userMsg,
                        session_id: this.sessionId,
                        page_url: window.location.pathname,
                    }),
                });

                const data = await res.json();
                this.messages.push({ role: 'assistant', text: data.message });
            } catch (e) {
                this.messages.push({ role: 'assistant', text: 'Lo siento, hubo un error. Por favor intenta de nuevo o escribenos a info@wellcorefitness.com' });
            }

            this.loading = false;
            this.$nextTick(() => this.scrollToBottom());
        },
        scrollToBottom() {
            const container = this.$refs.chatMessages;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
     }"
     class="fixed bottom-6 right-6 z-50">

    {{-- Chat Dialog --}}
    <div x-show="chatOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         style="display: none;"
         class="absolute bottom-16 right-0 mb-2 w-80 overflow-hidden rounded-2xl border border-wc-border bg-wc-bg shadow-2xl sm:w-96">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-wc-border bg-wc-accent px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                    <picture><source srcset="/images/logo-icon-light.webp" type="image/webp"><img src="/images/logo-icon-light.png" alt="WellCore" width="32" height="32" class="h-8 w-8 rounded-full object-contain" loading="lazy" decoding="async"></picture>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">WellCore</p>
                    <div class="flex items-center gap-1.5">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-300 opacity-75"></span>
                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        </span>
                        <p class="text-[11px] text-white/70">En linea</p>
                    </div>
                </div>
            </div>
            <button x-on:click="chatOpen = false" type="button" aria-label="Cerrar chat" class="flex h-8 w-8 items-center justify-center rounded-lg text-white/60 hover:bg-white/10 hover:text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Messages area --}}
        <div x-ref="chatMessages" class="h-72 overflow-y-auto px-4 py-4 space-y-3">
            <template x-for="(msg, index) in messages" :key="index">
                <div>
                    {{-- Assistant message --}}
                    <div x-show="msg.role === 'assistant'" class="flex gap-2">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full">
                            <picture class="dark:hidden"><source srcset="/images/logo-icon-dark.webp" type="image/webp"><img src="/images/logo-icon-dark.png" alt="W" width="36" height="36" class="h-9 w-9 rounded-full object-contain" loading="lazy" decoding="async"></picture>
                            <picture class="hidden dark:block"><source srcset="/images/logo-icon-light.webp" type="image/webp"><img src="/images/logo-icon-light.png" alt="W" width="36" height="36" class="h-9 w-9 rounded-full object-contain" loading="lazy" decoding="async"></picture>
                        </div>
                        <div class="max-w-[85%] rounded-xl rounded-tl-sm bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text-secondary" x-text="msg.text"></div>
                    </div>
                    {{-- User message --}}
                    <div x-show="msg.role === 'user'" class="flex justify-end">
                        <div class="max-w-[85%] rounded-xl rounded-tr-sm bg-wc-accent px-3 py-2 text-sm text-white" x-text="msg.text"></div>
                    </div>
                </div>
            </template>

            {{-- Quick Reply Buttons --}}
            <div x-show="showQuickReplies && messages.length <= 1 && !loading" x-transition class="space-y-2 pt-1">
                <p class="text-[11px] text-wc-text-tertiary">Preguntas frecuentes:</p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="(reply, i) in quickReplies" :key="i">
                        <button
                            x-on:click="sendQuickReply(reply)"
                            x-text="reply"
                            class="rounded-full border border-wc-accent/30 bg-wc-accent/5 px-3 py-1.5 text-xs font-medium text-wc-accent transition-all hover:border-wc-accent hover:bg-wc-accent/10 active:scale-95">
                        </button>
                    </template>
                </div>
            </div>

            {{-- Loading indicator with typing animation --}}
            <div x-show="loading" class="flex gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                    <picture class="dark:hidden"><source srcset="/images/logo-icon-dark.webp" type="image/webp"><img src="/images/logo-icon-dark.png" alt="W" width="24" height="24" class="h-6 w-6 rounded-full object-contain" loading="lazy"></picture>
                    <picture class="hidden dark:block"><source srcset="/images/logo-icon-light.webp" type="image/webp"><img src="/images/logo-icon-light.png" alt="W" width="24" height="24" class="h-6 w-6 rounded-full object-contain" loading="lazy"></picture>
                </div>
                <div class="rounded-xl rounded-tl-sm bg-wc-bg-tertiary px-4 py-3">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] text-wc-text-tertiary">Escribiendo</span>
                        <span class="inline-block h-1.5 w-1.5 animate-bounce rounded-full bg-wc-accent/60" style="animation-delay: 0ms;"></span>
                        <span class="inline-block h-1.5 w-1.5 animate-bounce rounded-full bg-wc-accent/60" style="animation-delay: 150ms;"></span>
                        <span class="inline-block h-1.5 w-1.5 animate-bounce rounded-full bg-wc-accent/60" style="animation-delay: 300ms;"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="border-t border-wc-border px-3 py-3">
            <form class="flex gap-2" x-on:submit.prevent="sendMessage()">
                <input
                    type="text"
                    x-model="newMessage"
                    placeholder="Escribe tu pregunta..."
                    maxlength="500"
                    class="flex-1 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                    x-on:keydown.enter.prevent="sendMessage()"
                    :disabled="loading"
                >
                <button
                    type="submit"
                    aria-label="Enviar mensaje"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent text-white hover:bg-wc-accent-hover disabled:opacity-50"
                    :disabled="loading || !newMessage.trim()"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <div class="border-t border-wc-border bg-wc-bg-secondary px-4 py-2 text-center text-[10px] text-wc-text-tertiary">
            Powered by WellCore · <a href="mailto:info@wellcorefitness.com" class="text-wc-accent hover:underline">info@wellcorefitness.com</a>
        </div>
    </div>

    {{-- Floating Button --}}
    <button
        x-on:click="chatOpen = !chatOpen"
        class="relative flex h-14 w-14 items-center justify-center rounded-full bg-wc-accent shadow-lg shadow-wc-accent/30 transition-transform duration-200 hover:scale-110 active:scale-95"
        aria-label="Abrir chat WellCore"
    >
        {{-- Chat icon (when closed) --}}
        <svg x-show="!chatOpen" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
        </svg>
        {{-- Close icon (when open) --}}
        <svg x-show="chatOpen" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
        {{-- Notification badge --}}
        <span x-show="!chatOpen" class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[9px] font-bold text-white">1</span>
    </button>
</div>
