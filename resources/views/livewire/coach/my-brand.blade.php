<div class="space-y-6" x-data="{ copied: false, copiedManifest: false }">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full" style="background-color: {{ $color_primary }}20;">
                <svg class="h-7 w-7" style="color: {{ $color_primary }};" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
                </svg>
            </div>
            <div>
                <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Mi Marca</h1>
                <p class="mt-0.5 text-sm text-wc-text-tertiary">{{ $coachName }} — Personaliza tu marca, PWA y pagina publica</p>
            </div>
        </div>
    </div>

    {{-- Tab bar --}}
    <div class="flex items-center gap-1 border-b border-wc-border">
        <button wire:click="switchTab('brand')"
                class="relative px-4 py-2.5 text-sm font-medium transition-colors
                       {{ $activeTab === 'brand' ? 'text-wc-accent' : 'text-wc-text-tertiary hover:text-wc-text' }}">
            <span class="flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
                </svg>
                Marca
            </span>
            @if($activeTab === 'brand')
                <span class="absolute bottom-0 left-0 h-0.5 w-full bg-wc-accent"></span>
            @endif
        </button>
        <button wire:click="switchTab('pwa')"
                class="relative px-4 py-2.5 text-sm font-medium transition-colors
                       {{ $activeTab === 'pwa' ? 'text-wc-accent' : 'text-wc-text-tertiary hover:text-wc-text' }}">
            <span class="flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                </svg>
                PWA Builder
            </span>
            @if($activeTab === 'pwa')
                <span class="absolute bottom-0 left-0 h-0.5 w-full bg-wc-accent"></span>
            @endif
        </button>
        <button wire:click="switchTab('preview')"
                class="relative px-4 py-2.5 text-sm font-medium transition-colors
                       {{ $activeTab === 'preview' ? 'text-wc-accent' : 'text-wc-text-tertiary hover:text-wc-text' }}">
            <span class="flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Preview
            </span>
            @if($activeTab === 'preview')
                <span class="absolute bottom-0 left-0 h-0.5 w-full bg-wc-accent"></span>
            @endif
        </button>
    </div>

    {{-- ==================== BRAND TAB ==================== --}}
    @if($activeTab === 'brand')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">

        {{-- Form (left, 3 cols) --}}
        <div class="space-y-5 lg:col-span-3">

            {{-- Visual Identity --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Identidad Visual</h3>
                <div class="mt-4 space-y-4">

                    {{-- Color Primary --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Color Principal</label>
                        <div class="flex items-center gap-3">
                            <input type="color" wire:model.live="color_primary"
                                   class="h-10 w-14 cursor-pointer rounded-lg border border-wc-border bg-wc-bg-secondary p-1">
                            <input type="text" wire:model.live="color_primary"
                                   class="w-28 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 font-mono text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                                   placeholder="#E31E24" maxlength="7">
                            <div class="h-10 w-10 rounded-lg border border-wc-border" style="background-color: {{ $color_primary }};"></div>
                        </div>
                    </div>

                    {{-- Logo URL --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Logo URL</label>
                        <input type="url" wire:model="logo_url"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                               placeholder="https://ejemplo.com/mi-logo.png">
                        <p class="mt-1 text-xs text-wc-text-tertiary">URL directa a tu logotipo (PNG o SVG recomendado)</p>
                    </div>

                    {{-- Photo URL --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Foto de Perfil URL</label>
                        <input type="url" wire:model="photo_url"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                               placeholder="https://ejemplo.com/mi-foto.jpg">
                    </div>
                </div>
            </div>

            {{-- Bio & Description --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Descripcion</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Bio</label>
                        <textarea wire:model="bio" rows="3"
                                  class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                                  placeholder="Describe tu enfoque como coach, tu filosofia de entrenamiento..."></textarea>
                    </div>
                </div>
            </div>

            {{-- Social Links --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Redes Sociales</h3>
                <div class="mt-4 space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">WhatsApp</label>
                            <div class="flex items-center gap-2">
                                <span class="text-wc-text-tertiary">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                </span>
                                <input type="text" wire:model="whatsapp"
                                       class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                                       placeholder="+52 81 1234 5678">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Instagram</label>
                            <div class="flex items-center gap-2">
                                <span class="text-wc-text-tertiary">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069ZM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0Zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881Z"/></svg>
                                </span>
                                <input type="text" wire:model="instagram"
                                       class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                                       placeholder="@miinstagram">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Public Profile --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Perfil Publico</h3>
                <div class="mt-4 space-y-4">

                    {{-- Slug --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">URL personalizada (slug)</label>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-wc-text-tertiary whitespace-nowrap">wellcore.test/coach/</span>
                            <input type="text" wire:model.live="slug"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                                   placeholder="mi-nombre">
                        </div>
                        <p class="mt-1 text-xs text-wc-text-tertiary">URL: <span class="font-mono text-wc-accent">wellcore.test/coach/{{ $slug }}</span></p>
                    </div>

                    {{-- Public Visible Toggle --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-wc-text">Perfil visible publicamente</p>
                            <p class="text-xs text-wc-text-tertiary">Los clientes potenciales podran ver tu pagina</p>
                        </div>
                        <button wire:click="$toggle('public_visible')" type="button"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-wc-accent/30 focus:ring-offset-2 focus:ring-offset-wc-bg
                                       {{ $public_visible ? 'bg-wc-accent' : 'bg-wc-border' }}">
                            <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                         {{ $public_visible ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Save Brand --}}
            <div class="flex items-center gap-4">
                <button wire:click="saveBrand"
                        class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-wc-accent/90 active:scale-95">
                    <svg class="h-4 w-4" wire:loading.class="animate-spin" wire:target="saveBrand" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Guardar Marca
                </button>
                @if($brandSaved)
                    <span class="text-sm font-medium text-emerald-400" x-data x-init="setTimeout(() => $wire.set('brandSaved', false), 3000)">
                        Marca guardada correctamente
                    </span>
                @endif
            </div>
        </div>

        {{-- Preview card (right, 2 cols) --}}
        <div class="space-y-5 lg:col-span-2">
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text mb-4">Vista Rapida</h3>

                {{-- Brand card preview --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-secondary overflow-hidden">
                    {{-- Color banner --}}
                    <div class="h-20 relative" style="background: linear-gradient(135deg, {{ $color_primary }}, {{ $color_primary }}99);">
                        @if($logo_url)
                            <img src="{{ $logo_url }}" alt="Logo" class="absolute bottom-2 left-4 h-10 w-10 rounded-lg bg-white/20 p-1 object-contain">
                        @endif
                    </div>
                    {{-- Info --}}
                    <div class="p-4 space-y-2">
                        <div class="flex items-center gap-2">
                            @if($photo_url)
                                <img src="{{ $photo_url }}" alt="Foto" class="h-10 w-10 rounded-full object-cover border-2" style="border-color: {{ $color_primary }};">
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded-full text-white text-sm font-bold" style="background-color: {{ $color_primary }};">
                                    {{ substr($coachName, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <p class="text-sm font-semibold text-wc-text">{{ $coachName }}</p>
                                <p class="text-xs text-wc-text-tertiary">Coach WellCore</p>
                            </div>
                        </div>
                        @if($bio)
                            <p class="text-xs text-wc-text-secondary line-clamp-2">{{ $bio }}</p>
                        @endif
                        <div class="flex items-center gap-3 pt-1">
                            @if($whatsapp)
                                <span class="inline-flex items-center gap-1 text-xs text-emerald-400">
                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                    WA
                                </span>
                            @endif
                            @if($instagram)
                                <span class="inline-flex items-center gap-1 text-xs text-pink-400">
                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069ZM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0Zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881Z"/></svg>
                                    {{ $instagram }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Public status --}}
                <div class="mt-4 flex items-center gap-2">
                    <div class="h-2 w-2 rounded-full {{ $public_visible ? 'bg-emerald-400' : 'bg-wc-text-tertiary' }}"></div>
                    <span class="text-xs text-wc-text-tertiary">{{ $public_visible ? 'Perfil publico activo' : 'Perfil oculto' }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ==================== PWA TAB ==================== --}}
    @if($activeTab === 'pwa')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">

        {{-- PWA Config Form (left, 3 cols) --}}
        <div class="space-y-5 lg:col-span-3">

            {{-- App Identity --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Configuracion PWA</h3>
                <p class="mt-1 text-xs text-wc-text-tertiary">Configura como se vera tu app al instalarse en el telefono del cliente</p>
                <div class="mt-4 space-y-4">

                    {{-- App Name --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Nombre de la App</label>
                        <input type="text" wire:model.live.debounce.300ms="pwa_app_name"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                               placeholder="Mi App Fitness" maxlength="60">
                        <p class="mt-1 text-xs text-wc-text-tertiary">{{ strlen($pwa_app_name) }}/60 caracteres</p>
                    </div>

                    {{-- Theme Color --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Color del Tema</label>
                        <div class="flex items-center gap-3">
                            <input type="color" wire:model.live="pwa_color"
                                   class="h-10 w-14 cursor-pointer rounded-lg border border-wc-border bg-wc-bg-secondary p-1">
                            <input type="text" wire:model.live="pwa_color"
                                   class="w-28 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 font-mono text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                                   placeholder="#E31E24" maxlength="7">
                            <div class="h-10 w-10 rounded-lg border border-wc-border" style="background-color: {{ $pwa_color }};"></div>
                        </div>
                    </div>

                    {{-- Icon URL --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Icono de la App (URL)</label>
                        <input type="url" wire:model.live.debounce.300ms="pwa_icon_url"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                               placeholder="https://ejemplo.com/icon-512.png">
                        <p class="mt-1 text-xs text-wc-text-tertiary">PNG de 512x512px recomendado</p>
                    </div>

                    {{-- Subdomain --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Subdominio</label>
                        <div class="flex items-center gap-2">
                            <input type="text" wire:model="pwa_subdomain"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                                   placeholder="mi-marca" maxlength="40">
                            <span class="text-sm text-wc-text-tertiary whitespace-nowrap">.wellcore.fit</span>
                        </div>
                        @if($pwa_subdomain)
                            <p class="mt-1 text-xs text-wc-text-tertiary">Tu app estara en: <span class="font-mono text-wc-accent">{{ $pwa_subdomain }}.wellcore.fit</span></p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Save PWA --}}
            <div class="flex items-center gap-4">
                <button wire:click="savePwa"
                        class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-wc-accent/90 active:scale-95">
                    <svg class="h-4 w-4" wire:loading.class="animate-spin" wire:target="savePwa" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Guardar PWA
                </button>
                @if($pwaSaved)
                    <span class="text-sm font-medium text-emerald-400" x-data x-init="setTimeout(() => $wire.set('pwaSaved', false), 3000)">
                        Configuracion PWA guardada
                    </span>
                @endif
            </div>

            {{-- Installation Steps --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Pasos de Instalacion PWA</h3>
                <p class="mt-1 text-xs text-wc-text-tertiary">Comparte estos pasos con tus clientes para que instalen tu app</p>
                <ol class="mt-4 space-y-3 text-sm text-wc-text-secondary">
                    <li class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white" style="background-color: {{ $pwa_color ?: '#E31E24' }};">1</span>
                        <span>Abre <span class="font-mono text-wc-accent">{{ $pwa_subdomain ?: 'tu-marca' }}.wellcore.fit</span> en Chrome (Android) o Safari (iOS)</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white" style="background-color: {{ $pwa_color ?: '#E31E24' }};">2</span>
                        <span><strong>Android:</strong> Toca el menu (tres puntos) y selecciona "Agregar a pantalla de inicio"</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white" style="background-color: {{ $pwa_color ?: '#E31E24' }};">3</span>
                        <span><strong>iOS:</strong> Toca el boton de compartir y selecciona "Agregar a pantalla de inicio"</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white" style="background-color: {{ $pwa_color ?: '#E31E24' }};">4</span>
                        <span>La app aparecera en tu pantalla de inicio con el icono y nombre personalizados</span>
                    </li>
                </ol>
            </div>
        </div>

        {{-- Manifest Preview (right, 2 cols) --}}
        <div class="space-y-5 lg:col-span-2">

            {{-- manifest.json preview --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-display text-lg tracking-wide text-wc-text">manifest.json</h3>
                    <button
                        x-on:click="navigator.clipboard.writeText($refs.manifestCode.textContent).then(() => { copiedManifest = true; setTimeout(() => copiedManifest = false, 2000) })"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                        <template x-if="!copiedManifest">
                            <span class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                                </svg>
                                Copiar
                            </span>
                        </template>
                        <template x-if="copiedManifest">
                            <span class="flex items-center gap-1.5 text-emerald-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                Copiado
                            </span>
                        </template>
                    </button>
                </div>
                <pre x-ref="manifestCode" class="overflow-x-auto rounded-lg bg-wc-bg p-4 text-xs leading-relaxed text-wc-text-secondary font-mono border border-wc-border">{{ $manifestJson }}</pre>
            </div>

            {{-- App Icon Preview --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text mb-3">Preview del Icono</h3>
                <div class="flex items-center gap-4">
                    {{-- App icon mockup --}}
                    <div class="flex flex-col items-center gap-2">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl shadow-lg" style="background-color: {{ $pwa_color }};">
                            @if($pwa_icon_url)
                                <img src="{{ $pwa_icon_url }}" alt="App Icon" class="h-12 w-12 rounded-xl object-contain">
                            @else
                                <span class="font-display text-2xl text-white">{{ substr($pwa_app_name ?: 'W', 0, 1) }}</span>
                            @endif
                        </div>
                        <span class="text-[10px] text-wc-text-secondary text-center max-w-[70px] truncate">{{ $pwa_app_name ?: 'Mi App' }}</span>
                    </div>
                    <div class="text-xs text-wc-text-tertiary">
                        <p>Asi se vera tu app en la pantalla de inicio del celular.</p>
                    </div>
                </div>
            </div>

            {{-- Service Worker Info --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text mb-2">Service Worker</h3>
                <div class="space-y-2 text-xs text-wc-text-tertiary">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-emerald-400"></div>
                        <span>Cache de assets estaticos</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-emerald-400"></div>
                        <span>Modo offline basico</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-amber-400"></div>
                        <span>Push notifications (proximamente)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ==================== PREVIEW TAB ==================== --}}
    @if($activeTab === 'preview')
    <div class="flex flex-col items-center gap-6">

        <p class="text-sm text-wc-text-tertiary">Asi se vera tu pagina de coach para clientes potenciales</p>

        {{-- Phone mockup --}}
        <div class="relative mx-auto" style="width: 320px;">
            {{-- Phone frame --}}
            <div class="rounded-[2.5rem] border-4 border-wc-border bg-wc-bg-secondary shadow-2xl overflow-hidden" style="aspect-ratio: 9/19.5;">

                {{-- Status bar --}}
                <div class="flex items-center justify-between px-6 pt-3 pb-1" style="background-color: {{ $color_primary }};">
                    <span class="text-[10px] font-medium text-white/80">9:41</span>
                    <div class="flex items-center gap-1">
                        <div class="h-2 w-4 rounded-sm bg-white/80"></div>
                        <div class="h-2 w-2 rounded-full bg-white/80"></div>
                    </div>
                </div>

                {{-- Header with brand color --}}
                <div class="relative px-5 pb-16 pt-6" style="background: linear-gradient(180deg, {{ $color_primary }}, {{ $color_primary }}CC);">
                    {{-- Logo --}}
                    @if($logo_url)
                        <img src="{{ $logo_url }}" alt="Logo" class="h-8 w-auto mb-3 opacity-90">
                    @else
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-6 w-6 rounded bg-white/20 flex items-center justify-center">
                                <span class="text-xs font-bold text-white">{{ substr($coachName, 0, 1) }}</span>
                            </div>
                            <span class="text-xs font-semibold text-white/80 uppercase tracking-wider">{{ $pwa_app_name ?: 'Fitness' }}</span>
                        </div>
                    @endif
                    <h2 class="text-lg font-bold text-white leading-tight">{{ $coachName }}</h2>
                    <p class="text-xs text-white/70 mt-0.5">Coach WellCore</p>
                </div>

                {{-- Coach photo overlay --}}
                <div class="flex justify-center -mt-10 relative z-10">
                    @if($photo_url)
                        <img src="{{ $photo_url }}" alt="{{ $coachName }}" class="h-20 w-20 rounded-full object-cover border-4 shadow-lg" style="border-color: {{ $color_primary }};">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-full text-white text-2xl font-bold border-4 shadow-lg" style="background-color: {{ $color_primary }}; border-color: {{ $color_primary }}44;">
                            {{ substr($coachName, 0, 1) }}
                        </div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="px-5 pt-3 pb-6 space-y-4">
                    {{-- Bio --}}
                    @if($bio)
                        <p class="text-center text-xs text-wc-text-secondary leading-relaxed">{{ Str::limit($bio, 120) }}</p>
                    @else
                        <p class="text-center text-xs text-wc-text-tertiary italic">Tu bio aparecera aqui...</p>
                    @endif

                    {{-- Social buttons --}}
                    <div class="flex justify-center gap-3">
                        @if($whatsapp)
                            <div class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-medium text-white" style="background-color: #25D366;">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                WhatsApp
                            </div>
                        @endif
                        @if($instagram)
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 px-3 py-1.5 text-[10px] font-medium text-white">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069ZM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0Zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881Z"/></svg>
                                {{ $instagram }}
                            </div>
                        @endif
                    </div>

                    {{-- CTA Button --}}
                    <div class="pt-2">
                        <div class="w-full rounded-xl py-2.5 text-center text-xs font-bold text-white shadow-lg" style="background-color: {{ $color_primary }};">
                            Comenzar Ahora
                        </div>
                    </div>

                    {{-- Feature cards --}}
                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                            <div class="mx-auto mb-1 flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: {{ $color_primary }}20;">
                                <svg class="h-3.5 w-3.5" style="color: {{ $color_primary }};" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                            </div>
                            <p class="text-[10px] font-medium text-wc-text">Entrenamiento</p>
                        </div>
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                            <div class="mx-auto mb-1 flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: {{ $color_primary }}20;">
                                <svg class="h-3.5 w-3.5" style="color: {{ $color_primary }};" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                                </svg>
                            </div>
                            <p class="text-[10px] font-medium text-wc-text">Nutricion</p>
                        </div>
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                            <div class="mx-auto mb-1 flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: {{ $color_primary }}20;">
                                <svg class="h-3.5 w-3.5" style="color: {{ $color_primary }};" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                            </div>
                            <p class="text-[10px] font-medium text-wc-text">Progreso</p>
                        </div>
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                            <div class="mx-auto mb-1 flex h-7 w-7 items-center justify-center rounded-lg" style="background-color: {{ $color_primary }}20;">
                                <svg class="h-3.5 w-3.5" style="color: {{ $color_primary }};" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                </svg>
                            </div>
                            <p class="text-[10px] font-medium text-wc-text">Coaching</p>
                        </div>
                    </div>

                    {{-- Bottom nav mockup --}}
                    <div class="mt-3 flex items-center justify-around rounded-2xl border border-wc-border bg-wc-bg-tertiary py-2">
                        <div class="flex flex-col items-center gap-0.5">
                            <svg class="h-4 w-4" style="color: {{ $color_primary }};" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            <span class="text-[8px]" style="color: {{ $color_primary }};">Inicio</span>
                        </div>
                        <div class="flex flex-col items-center gap-0.5 text-wc-text-tertiary">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                            </svg>
                            <span class="text-[8px]">Train</span>
                        </div>
                        <div class="flex flex-col items-center gap-0.5 text-wc-text-tertiary">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Z" />
                            </svg>
                            <span class="text-[8px]">Stats</span>
                        </div>
                        <div class="flex flex-col items-center gap-0.5 text-wc-text-tertiary">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span class="text-[8px]">Perfil</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Home indicator --}}
            <div class="flex justify-center pb-2">
                <div class="h-1 w-28 rounded-full bg-wc-text-tertiary/30"></div>
            </div>
        </div>

        {{-- Color scheme info --}}
        <div class="w-full max-w-sm">
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text mb-3">Paleta de tu Marca</h3>
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg border border-wc-border" style="background-color: {{ $color_primary }};"></div>
                        <div>
                            <p class="text-xs font-medium text-wc-text">Color Principal</p>
                            <p class="font-mono text-xs text-wc-text-tertiary">{{ $color_primary }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg border border-wc-border" style="background-color: {{ $pwa_color }};"></div>
                        <div>
                            <p class="text-xs font-medium text-wc-text">Color PWA</p>
                            <p class="font-mono text-xs text-wc-text-tertiary">{{ $pwa_color }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg border border-wc-border bg-[#0A0A0A]"></div>
                        <div>
                            <p class="text-xs font-medium text-wc-text">Fondo</p>
                            <p class="font-mono text-xs text-wc-text-tertiary">#0A0A0A</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
