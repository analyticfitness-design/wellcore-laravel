<div class="mx-auto max-w-4xl space-y-8"
     x-data="{
         copied: false,
         copyLink() {
             navigator.clipboard.writeText('{{ $referralLink }}').then(() => {
                 this.copied = true;
                 setTimeout(() => { this.copied = false; }, 2500);
             });
         }
     }">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text">REFERIDOS</h1>
            <p class="mt-1 text-sm text-wc-text-tertiary">Invita a tus amigos y gana recompensas exclusivas</p>
        </div>
    </div>

    {{-- Success Notice --}}
    @if($showSuccess)
        <div class="flex items-center justify-between rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-sm font-medium text-green-400">{{ $successMessage }}</span>
            </div>
            <button wire:click="dismissSuccess" class="text-green-400/60 hover:text-green-400 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Referral Link Card --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                </svg>
            </div>
            <div>
                <h2 class="font-semibold text-wc-text">Tu link de referido</h2>
                <p class="text-xs text-wc-text-tertiary">Comparte este enlace con tus amigos</p>
            </div>
        </div>

        {{-- Link display --}}
        <div class="mb-4 flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5">
            <span class="flex-1 truncate font-mono text-xs text-wc-text-secondary">{{ $referralLink }}</span>
        </div>

        {{-- Action buttons --}}
        <div class="flex flex-wrap gap-3">
            {{-- Copy button --}}
            <button
                @click="copyLink()"
                class="flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm font-medium text-wc-text transition-all hover:border-wc-accent hover:text-wc-accent">
                <template x-if="!copied">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                    </svg>
                </template>
                <template x-if="copied">
                    <svg class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </template>
                <span x-text="copied ? 'Copiado!' : 'Copiar link'"></span>
            </button>

            {{-- WhatsApp button --}}
            <a href="https://wa.me/?text={{ urlencode('Hola! Te invito a unirte a WellCore Fitness, la plataforma de entrenamiento personalizado. Usa mi link: ' . $referralLink) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="flex items-center gap-2 rounded-lg bg-[#25D366] px-4 py-2 text-sm font-medium text-white transition-opacity hover:opacity-90">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                </svg>
                Compartir por WhatsApp
            </a>
        </div>
    </div>

    {{-- Stats Grid (4 cards) --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        {{-- Total referidos --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
            <p class="font-data text-3xl font-bold text-wc-text">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">Total referidos</p>
        </div>

        {{-- Convertidos --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
            <p class="font-data text-3xl font-bold text-green-400">{{ $stats['converted'] }}</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">Convertidos</p>
        </div>

        {{-- Pendientes --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
            <p class="font-data text-3xl font-bold text-yellow-400">{{ $stats['pending'] }}</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">Pendientes</p>
        </div>

        {{-- Tasa de conversion --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
            <p class="font-data text-3xl font-bold text-wc-accent">{{ $stats['tasa'] }}%</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">Tasa conversion</p>
        </div>
    </div>

    {{-- Invite Form --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
            </div>
            <div>
                <h2 class="font-semibold text-wc-text">Invitar directamente</h2>
                <p class="text-xs text-wc-text-tertiary">Envía una invitación por correo</p>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <input
                    type="email"
                    wire:model="inviteEmail"
                    placeholder="correo@ejemplo.com"
                    class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none transition-colors"
                />
                @error('inviteEmail')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <button
                wire:click="sendInvite"
                wire:loading.attr="disabled"
                class="flex items-center justify-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white transition-opacity hover:opacity-90 disabled:opacity-50">
                <span wire:loading.remove wire:target="sendInvite">Enviar invitacion</span>
                <span wire:loading wire:target="sendInvite">Enviando...</span>
            </button>
        </div>
    </div>

    {{-- Referral History --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="mb-4 font-semibold text-wc-text">Historial de referidos</h2>

        @if($referrals->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg">
                    <svg class="h-7 w-7 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                <p class="font-medium text-wc-text-secondary">Aun no tienes referidos</p>
                <p class="mt-1 text-sm text-wc-text-tertiary">Comparte tu link o invita directamente a tus amigos</p>
            </div>
        @else
            <div class="overflow-hidden rounded-lg border border-wc-border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-wc-border bg-wc-bg">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Correo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-wc-border">
                        @foreach($referrals as $referral)
                            @php
                                $email = $referral->referred_email;
                                $atPos = strpos($email, '@');
                                $masked = ($atPos > 1)
                                    ? substr($email, 0, 1) . str_repeat('*', min($atPos - 1, 3)) . substr($email, $atPos)
                                    : $email;
                            @endphp
                            <tr class="transition-colors hover:bg-wc-bg">
                                <td class="px-4 py-3 font-mono text-xs text-wc-text-secondary">
                                    {{ $masked }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($referral->status === 'converted')
                                        <span class="inline-flex rounded-full bg-green-500/15 px-2 py-0.5 text-[10px] font-medium text-green-400">
                                            Convertido
                                        </span>
                                    @elseif($referral->status === 'registered')
                                        <span class="inline-flex rounded-full bg-blue-500/15 px-2 py-0.5 text-[10px] font-medium text-blue-400">
                                            Registrado
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-yellow-500/15 px-2 py-0.5 text-[10px] font-medium text-yellow-400">
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-xs text-wc-text-tertiary">
                                    {{ $referral->created_at ? $referral->created_at->format('d M Y') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
