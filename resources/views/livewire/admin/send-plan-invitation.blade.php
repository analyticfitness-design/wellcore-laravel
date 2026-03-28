<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-wc-text">Enviar Invitacion de Plan</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Envia un correo profesional con la informacion del plan seleccionado y el link de pago directo.</p>
    </div>

    {{-- ═══ PLAN SELECTOR ═══ --}}
    <div>
        <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Selecciona el plan</p>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach($plans as $key => $plan)
            <button wire:click="selectPlan('{{ $key }}')"
                    class="group relative rounded-xl border p-4 text-left transition-all duration-200
                           {{ $selectedPlan === $key
                               ? 'border-red-500 bg-red-500/10 ring-1 ring-red-500/30'
                               : 'border-wc-border bg-wc-bg-secondary hover:border-wc-border-hover hover:bg-wc-bg-tertiary' }}">

                {{-- Selected indicator --}}
                @if($selectedPlan === $key)
                <div class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500">
                    <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>
                @endif

                <p class="text-base font-bold text-wc-text">{{ $plan['name'] }}</p>
                <p class="mt-0.5 text-sm font-semibold text-red-500">{{ $plan['price'] }}</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">{{ $plan['type'] }}</p>
                <p class="mt-2 text-xs leading-relaxed text-wc-text-secondary">{{ $plan['desc'] }}</p>
            </button>
            @endforeach
        </div>
    </div>

    {{-- ═══ SEND FORM ═══ --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-wc-text-tertiary">Datos del destinatario</h2>

        <form wire:submit="sendInvitation" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Name --}}
                <div>
                    <label for="recipientName" class="mb-1 block text-sm font-medium text-wc-text">Nombre</label>
                    <input wire:model="recipientName"
                           type="text"
                           id="recipientName"
                           placeholder="Nombre del prospecto"
                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary transition focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500/30">
                    @error('recipientName')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="recipientEmail" class="mb-1 block text-sm font-medium text-wc-text">Email</label>
                    <input wire:model="recipientEmail"
                           type="email"
                           id="recipientEmail"
                           placeholder="correo@ejemplo.com"
                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary transition focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500/30">
                    @error('recipientEmail')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Selected plan summary --}}
            <div class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg px-4 py-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-500/15">
                    <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-wc-text">
                        Se enviara invitacion del plan <strong class="text-red-500">{{ $plans[$selectedPlan]['name'] }}</strong>
                        ({{ $plans[$selectedPlan]['price'] }})
                    </p>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-red-700 disabled:cursor-wait disabled:opacity-60">
                    <span wire:loading.remove wire:target="sendInvitation">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="sendInvitation">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                    Enviar Invitacion
                </button>

                {{-- Success --}}
                @if($successMessage)
                <div class="flex items-center gap-2 rounded-lg bg-green-500/10 px-4 py-2 text-sm text-green-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ $successMessage }}
                </div>
                @endif

                {{-- Error --}}
                @if($errorMessage)
                <div class="flex items-center gap-2 rounded-lg bg-red-500/10 px-4 py-2 text-sm text-red-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    {{ $errorMessage }}
                </div>
                @endif
            </div>
        </form>
    </div>

    {{-- ═══ SESSION HISTORY ═══ --}}
    @if(count($sentHistory) > 0)
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-wc-text-tertiary">Enviados en esta sesion</h2>
        <table class="w-full">
            <thead>
                <tr class="border-b border-wc-border text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
                    <th class="pb-2">Nombre</th>
                    <th class="pb-2">Email</th>
                    <th class="pb-2">Plan</th>
                    <th class="pb-2">Hora</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_reverse($sentHistory) as $entry)
                <tr class="border-b border-wc-border/50 last:border-0">
                    <td class="py-2.5 text-sm text-wc-text">{{ $entry['name'] }}</td>
                    <td class="py-2.5 text-sm text-wc-text-secondary">{{ $entry['email'] }}</td>
                    <td class="py-2.5">
                        <span class="inline-block rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-semibold text-red-400">{{ $entry['plan'] }}</span>
                    </td>
                    <td class="py-2.5 text-sm text-wc-text-tertiary">{{ $entry['time'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
