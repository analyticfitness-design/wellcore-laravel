<div class="min-h-screen bg-wc-bg">
    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-wc-accent">Checkout WellCore</p>
            <h1 class="mt-2 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">COMPLETA TU PAGO</h1>
        </div>

        {{-- Step Indicator --}}
        <div class="mt-8 flex items-center justify-center gap-4">
            @foreach([1 => 'Plan', 2 => 'Datos', 3 => 'Pago'] as $num => $label)
                <div class="flex items-center gap-2 {{ $step >= $num ? 'text-wc-accent' : 'text-wc-text-tertiary' }}">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full {{ $step >= $num ? 'bg-wc-accent text-white' : 'border border-wc-border' }} text-xs font-bold">{{ $num }}</div>
                    <span class="hidden text-sm font-medium sm:inline">{{ $label }}</span>
                </div>
                @if($num < 3)
                    <div class="h-px w-8 {{ $step > $num ? 'bg-wc-accent' : 'bg-wc-border' }}"></div>
                @endif
            @endforeach
        </div>

        <div class="mt-10 grid grid-cols-1 gap-8 lg:grid-cols-5">
            {{-- Main Content --}}
            <div class="lg:col-span-3">
                {{-- Step 1: Plan Selection --}}
                @if($step === 1)
                <div>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">ELIGE TU PLAN</h2>
                    <p class="mt-1 text-sm text-wc-text-secondary">Selecciona el plan que mejor se adapte a tu objetivo.</p>

                    <div class="mt-6 space-y-4">
                        @foreach($allPlans as $key => $p)
                        <button type="button" wire:click="selectPlan('{{ $key }}')"
                            class="flex w-full items-center justify-between rounded-xl border-2 p-6 text-left transition-all {{ $plan === $key ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}">
                            <div>
                                @if($key === 'metodo')
                                    <span class="mb-1 inline-block rounded-full bg-wc-accent px-2 py-0.5 text-[10px] font-semibold text-white">MAS POPULAR</span>
                                @endif
                                @if($key === 'elite')
                                    <span class="mb-1 inline-block rounded-full bg-wc-text/10 px-2 py-0.5 text-[10px] font-semibold text-wc-text-secondary">LIMITADO 5 CUPOS</span>
                                @endif
                                <h3 class="font-display text-lg tracking-wide text-wc-text">{{ strtoupper($p['name']) }}</h3>
                                <p class="mt-1 text-xs text-wc-text-tertiary">{{ $p['desc'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-data text-xl font-bold text-wc-accent">${{ number_format($p['price'], 0, ',', '.') }}</p>
                                <p class="text-xs text-wc-text-tertiary">COP/mes</p>
                            </div>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Step 2: User Data --}}
                @if($step === 2)
                <div>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">TUS DATOS</h2>
                    <p class="mt-1 text-sm text-wc-text-secondary">Informacion para crear tu cuenta y contactarte.</p>

                    <form wire:submit="proceedToPayment" class="mt-6 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Nombre completo *</label>
                            <input type="text" wire:model="nombre" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Tu nombre completo">
                            @error('nombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Email *</label>
                            <input type="email" wire:model="email" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="tu@email.com">
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">WhatsApp *</label>
                            <input type="text" wire:model="whatsapp" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="+57 312 490 4720">
                            @error('whatsapp') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary">Pais *</label>
                                <select wire:model="pais" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                    <option value="colombia">Colombia</option>
                                    <option value="mexico">Mexico</option>
                                    <option value="argentina">Argentina</option>
                                    <option value="peru">Peru</option>
                                    <option value="chile">Chile</option>
                                    <option value="venezuela">Venezuela</option>
                                    <option value="ecuador">Ecuador</option>
                                    <option value="usa">Estados Unidos</option>
                                    <option value="espana">Espana</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary">Objetivo principal *</label>
                                <select wire:model="objetivo" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                    <option value="">Seleccionar</option>
                                    <option value="perder_grasa">Perder grasa</option>
                                    <option value="ganar_masa">Ganar masa muscular</option>
                                    <option value="rendimiento">Rendimiento atletico</option>
                                    <option value="bienestar">Bienestar general</option>
                                </select>
                                @error('objetivo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <input type="checkbox" wire:model="terminos" id="checkout-terminos" class="mt-1 h-4 w-4 rounded border-wc-border bg-wc-bg-tertiary text-wc-accent focus:ring-wc-accent">
                            <label for="checkout-terminos" class="text-sm text-wc-text-secondary">
                                Acepto los <a href="{{ route('terminos') }}" target="_blank" class="text-wc-accent hover:underline">terminos de servicio</a> y la <a href="{{ route('privacidad') }}" target="_blank" class="text-wc-accent hover:underline">politica de privacidad</a> *
                            </label>
                        </div>
                        @error('terminos') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                        <div class="flex items-center justify-between pt-4">
                            <button type="button" wire:click="goToStep(1)" class="inline-flex items-center gap-2 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                                Cambiar plan
                            </button>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover">
                                Continuar al pago
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                            </button>
                        </div>
                    </form>
                </div>
                @endif

                {{-- Step 3: Payment --}}
                @if($step === 3)
                <div>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">METODO DE PAGO</h2>
                    <p class="mt-1 text-sm text-wc-text-secondary">Pago seguro procesado por Wompi (Bancolombia).</p>

                    {{-- Payment Methods --}}
                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <span class="rounded border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text-secondary">Visa</span>
                        <span class="rounded border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text-secondary">Mastercard</span>
                        <span class="rounded border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text-secondary">Amex</span>
                        <span class="rounded border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text-secondary">PSE</span>
                        <span class="rounded border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text-secondary">Nequi</span>
                        <span class="rounded border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text-secondary">Efecty</span>
                    </div>

                    @if($paymentError)
                        <div class="mt-4 rounded-lg border border-red-500/30 bg-red-500/5 p-4 text-sm text-red-400">
                            {{ $paymentError }}
                        </div>
                    @endif

                    {{-- Wompi Widget Container --}}
                    @if($wompiPublicKey && $paymentReference)
                        @php
                            $wompiConfigForJs = [
                                'currency' => $currency,
                                'amountInCents' => $amountInCents,
                                'reference' => $paymentReference,
                                'publicKey' => $wompiPublicKey,
                                'signature' => $wompiSignature,
                                'redirectUrl' => $wompiRedirectUrl,
                                'email' => $email,
                                'fullName' => $nombre,
                                'phoneNumber' => $whatsapp,
                            ];
                        @endphp
                        <script type="application/json" id="wompi-cfg-json">{!! json_encode($wompiConfigForJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                        <div class="mt-8 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6"
                             x-data="{
                                ready: false,
                                checkout: null,
                                cfg: null,
                                init() {
                                    const el = document.getElementById('wompi-cfg-json');
                                    if (el) { try { this.cfg = JSON.parse(el.textContent); } catch (e) { console.error('wompi cfg parse', e); } }
                                    this.loadWompi();
                                },
                                loadWompi() {
                                    if (window.WidgetCheckout) { this.ready = true; return; }
                                    const script = document.createElement('script');
                                    script.src = 'https://checkout.wompi.co/widget.js';
                                    script.onload = () => { this.ready = true; };
                                    script.onerror = () => { console.error('Failed to load Wompi widget script'); this.ready = true; };
                                    document.head.appendChild(script);
                                    setTimeout(() => { this.ready = true; }, 6000);
                                },
                                openWompi() {
                                    if (!window.WidgetCheckout) {
                                        alert('La pasarela de pago no cargo correctamente. Recarga la pagina e intenta de nuevo.');
                                        return;
                                    }
                                    const cfg = this.cfg || {};
                                    this.checkout = new WidgetCheckout({
                                        currency: cfg.currency,
                                        amountInCents: cfg.amountInCents,
                                        reference: cfg.reference,
                                        publicKey: cfg.publicKey,
                                        signature: { integrity: cfg.signature },
                                        redirectUrl: cfg.redirectUrl,
                                        customerData: {
                                            email: cfg.email,
                                            fullName: cfg.fullName,
                                            phoneNumber: cfg.phoneNumber,
                                        },
                                    });
                                    this.checkout.open(function(result) {
                                        const transaction = result.transaction;
                                        if (transaction && transaction.redirectUrl) {
                                            window.location.href = transaction.redirectUrl;
                                        }
                                    });
                                }
                             }">

                            {{-- Pay button --}}
                            <div class="text-center">
                                <button type="button"
                                        x-on:click="openWompi()"
                                        x-bind:disabled="!ready"
                                        class="inline-flex w-full items-center justify-center gap-3 rounded-xl bg-wc-accent px-8 py-4 text-lg font-bold text-white shadow-lg shadow-red-500/25 transition hover:bg-red-700 disabled:cursor-wait disabled:opacity-50">
                                    <template x-if="!ready">
                                        <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="ready">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                        </svg>
                                    </template>
                                    <span x-text="ready ? 'Pagar ${{ number_format($total, 0, ',', '.') }} COP' : 'Cargando pasarela...'"></span>
                                </button>
                                <p class="mt-3 text-xs text-wc-text-tertiary">Se abrira la ventana segura de Wompi para completar tu pago.</p>
                            </div>

                            {{-- Payment info --}}
                            <div class="mt-6 space-y-2 border-t border-wc-border pt-4">
                                <div class="flex items-center justify-between text-xs text-wc-text-tertiary">
                                    <span>Referencia</span>
                                    <span class="font-mono">{{ $paymentReference }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-wc-text-tertiary">
                                    <span>Total a pagar</span>
                                    <span class="font-data font-bold text-wc-accent">${{ number_format($total, 0, ',', '.') }} COP</span>
                                </div>
                                @if($wompiSandbox)
                                    <div class="mt-2 rounded-lg bg-yellow-500/10 px-3 py-2 text-center text-xs text-yellow-500">
                                        Modo sandbox activo - usa tarjeta de prueba: 4242 4242 4242 4242
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Fallback: no API keys configured --}}
                        <div class="mt-8 rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-wc-accent/10">
                                <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                </svg>
                            </div>
                            <p class="mt-4 text-sm font-medium text-wc-text">Pasarela de pago no disponible</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">Contactanos por WhatsApp para completar tu inscripcion.</p>
                            <a href="https://wa.me/573124904720?text=Hola%2C%20quiero%20inscribirme%20al%20plan%20{{ urlencode($planInfo['name']) }}"
                               target="_blank"
                               class="mt-4 inline-flex items-center gap-2 rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 0 0 .611.611l4.458-1.495A11.953 11.953 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.396 0-4.612-.804-6.39-2.157l-.152-.12-3.192 1.07 1.07-3.192-.12-.152A9.965 9.965 0 0 1 2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                                Pagar por WhatsApp
                            </a>
                        </div>
                    @endif

                    <div class="mt-6 flex items-center justify-between">
                        <button type="button" wire:click="goToStep(2)" class="inline-flex items-center gap-2 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                            Volver
                        </button>
                    </div>

                    {{-- Security --}}
                    <div class="mt-8 flex items-center justify-center gap-2 text-xs text-wc-text-tertiary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                        <span>Pago 100% seguro con Wompi &middot; Cifrado SSL 256 bits &middot; No almacenamos datos de tarjetas</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Order Summary Sidebar --}}
            <div class="lg:col-span-2">
                <div class="sticky top-20 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-wc-text">Resumen</h3>

                    @if($plan)
                        <div class="mt-4 border-b border-wc-border pb-4">
                            <p class="text-sm font-medium text-wc-text">Plan {{ $planInfo['name'] }}</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">{{ $planInfo['desc'] }}</p>
                        </div>

                        {{-- Discount Code --}}
                        <div class="mt-4">
                            <label class="block text-xs font-medium text-wc-text-tertiary">Codigo de descuento</label>
                            <div class="mt-1 flex gap-2">
                                <input type="text" wire:model="codigoDescuento" class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="CODIGO">
                                <button type="button" wire:click="aplicarDescuento" class="shrink-0 rounded-lg border border-wc-border px-3 py-2 text-xs font-medium text-wc-text-secondary hover:text-wc-text">
                                    Aplicar
                                </button>
                            </div>
                            @if($descuentoMensaje)
                                <p class="mt-1 text-xs {{ $descuento > 0 ? 'text-green-500' : 'text-red-500' }}">{{ $descuentoMensaje }}</p>
                            @endif
                        </div>

                        {{-- Totals --}}
                        <div class="mt-4 space-y-2 border-t border-wc-border pt-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-wc-text-secondary">Subtotal</span>
                                <span class="text-wc-text">${{ number_format($price, 0, ',', '.') }}</span>
                            </div>
                            @if($descuento > 0)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-green-500">Descuento</span>
                                <span class="text-green-500">-${{ number_format($descuento, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            <div class="flex items-center justify-between border-t border-wc-border pt-2">
                                <span class="text-sm font-semibold text-wc-text">Total</span>
                                <span class="font-data text-xl font-bold text-wc-accent">${{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                            <p class="text-xs text-wc-text-tertiary">COP &middot; Pago mensual</p>
                        </div>

                        {{-- User info summary in step 3 --}}
                        @if($step === 3 && $nombre)
                            <div class="mt-4 space-y-1 border-t border-wc-border pt-4">
                                <p class="text-xs font-medium text-wc-text-tertiary">Datos del comprador</p>
                                <p class="text-sm text-wc-text">{{ $nombre }}</p>
                                <p class="text-xs text-wc-text-secondary">{{ $email }}</p>
                                <p class="text-xs text-wc-text-secondary">{{ $whatsapp }}</p>
                            </div>
                        @endif
                    @else
                        <p class="mt-4 text-sm text-wc-text-tertiary">Selecciona un plan para ver el resumen.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
