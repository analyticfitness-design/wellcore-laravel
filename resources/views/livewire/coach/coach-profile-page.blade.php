<div class="space-y-6" x-data="{ copied: false }">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-wc-accent/20">
                <span class="font-display text-2xl text-wc-accent">{{ substr($coachName, 0, 1) }}</span>
            </div>
            <div>
                <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Mi Perfil</h1>
                <p class="mt-0.5 text-sm text-wc-text-tertiary">{{ $coachName }} — Gestiona tu perfil, referidos y revenue</p>
            </div>
        </div>
    </div>

    {{-- Tab bar --}}
    <div class="flex items-center gap-1 border-b border-wc-border">
        <button wire:click="switchTab('profile')"
                class="relative px-4 py-2.5 text-sm font-medium transition-colors
                       {{ $activeTab === 'profile' ? 'text-wc-accent' : 'text-wc-text-tertiary hover:text-wc-text' }}">
            <span class="flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Perfil
            </span>
            @if($activeTab === 'profile')
                <span class="absolute bottom-0 left-0 h-0.5 w-full bg-wc-accent"></span>
            @endif
        </button>
        <button wire:click="switchTab('referrals')"
                class="relative px-4 py-2.5 text-sm font-medium transition-colors
                       {{ $activeTab === 'referrals' ? 'text-wc-accent' : 'text-wc-text-tertiary hover:text-wc-text' }}">
            <span class="flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                Referidos
            </span>
            @if($activeTab === 'referrals')
                <span class="absolute bottom-0 left-0 h-0.5 w-full bg-wc-accent"></span>
            @endif
        </button>
        <button wire:click="switchTab('revenue')"
                class="relative px-4 py-2.5 text-sm font-medium transition-colors
                       {{ $activeTab === 'revenue' ? 'text-wc-accent' : 'text-wc-text-tertiary hover:text-wc-text' }}">
            <span class="flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Revenue
            </span>
            @if($activeTab === 'revenue')
                <span class="absolute bottom-0 left-0 h-0.5 w-full bg-wc-accent"></span>
            @endif
        </button>
    </div>

    {{-- ==================== PROFILE TAB ==================== --}}
    @if($activeTab === 'profile')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">

        {{-- Form (left, 3 cols) --}}
        <div class="space-y-5 lg:col-span-3">

            {{-- Bio & Basic Info --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Informacion basica</h3>
                <div class="mt-4 space-y-4">

                    {{-- Bio --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Bio</label>
                        <textarea wire:model="bio" rows="3"
                                  class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                                  placeholder="Describe tu experiencia y enfoque como coach..."></textarea>
                    </div>

                    {{-- City + Experience --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Ciudad</label>
                            <input type="text" wire:model="city"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                                   placeholder="Monterrey, MX">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Experiencia</label>
                            <input type="text" wire:model="experience"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                                   placeholder="5 anos">
                        </div>
                    </div>

                    {{-- Specializations --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Especialidades <span class="font-normal text-wc-text-tertiary">(separadas por coma)</span></label>
                        <input type="text" wire:model="specializations_input"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                               placeholder="Hipertrofia, Fuerza, Nutricion deportiva">
                        @if($specializations_input)
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach(array_filter(array_map('trim', explode(',', $specializations_input))) as $spec)
                                    <span class="inline-flex rounded-full bg-wc-accent/10 px-2.5 py-0.5 text-xs font-medium text-wc-accent">{{ $spec }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Contact & Social --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Contacto y redes</h3>
                <div class="mt-4 space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">WhatsApp</label>
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-emerald-500" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.149.567 4.163 1.555 5.903L0 24l6.335-1.523A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75c-1.97 0-3.834-.55-5.437-1.503l-.39-.231-3.756.904.948-3.633-.254-.404A9.693 9.693 0 0 1 2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75z"/>
                                </svg>
                                <input type="text" wire:model="whatsapp"
                                       class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                                       placeholder="+52 81 1234 5678">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Instagram</label>
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-pink-500" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>
                                </svg>
                                <input type="text" wire:model="instagram"
                                       class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                                       placeholder="@tu_usuario">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Branding --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Branding</h3>
                <div class="mt-4 space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Color primario</label>
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model="color_primary"
                                       class="h-10 w-14 cursor-pointer rounded-lg border border-wc-border bg-wc-bg-secondary">
                                <span class="font-mono text-sm text-wc-text-secondary">{{ $color_primary }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Perfil publico</label>
                            <label class="mt-2 flex items-center gap-3 cursor-pointer">
                                <button wire:click="$toggle('public_visible')" type="button"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out
                                               {{ $public_visible ? 'bg-wc-accent' : 'bg-wc-border' }}">
                                    <span class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                                 {{ $public_visible ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                                <span class="text-sm text-wc-text-secondary">{{ $public_visible ? 'Visible' : 'Oculto' }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Referral code (read-only) --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Codigo de referido</h3>
                <div class="mt-3 flex items-center gap-3">
                    <div class="flex-1 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2">
                        <span class="font-mono text-sm font-semibold text-wc-accent tracking-wider">{{ $referral_code }}</span>
                    </div>
                    <button
                        x-on:click="navigator.clipboard.writeText('{{ $referral_code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm font-medium text-wc-text-secondary hover:text-wc-text hover:border-wc-accent/30 transition-colors">
                        <template x-if="!copied">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                            </svg>
                        </template>
                        <template x-if="copied">
                            <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </template>
                        <span x-text="copied ? 'Copiado' : 'Copiar'"></span>
                    </button>
                </div>
                <p class="mt-2 text-xs text-wc-text-tertiary">Comparte este codigo para obtener comisiones por referidos</p>
            </div>

            {{-- Save --}}
            <div class="flex items-center gap-3">
                <button wire:click="saveProfile"
                        wire:loading.attr="disabled"
                        class="btn-press inline-flex items-center gap-2 rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50">
                    <svg wire:loading.remove wire:target="saveProfile" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <svg wire:loading wire:target="saveProfile" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span wire:loading.remove wire:target="saveProfile">Guardar perfil</span>
                    <span wire:loading wire:target="saveProfile">Guardando...</span>
                </button>
                @if($saved)
                    <span class="text-sm font-medium text-emerald-500" wire:transition>Guardado correctamente</span>
                @endif
            </div>
        </div>

        {{-- Preview (right, 2 cols) --}}
        <div class="lg:col-span-2">
            <div class="sticky top-24 space-y-4">
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Vista previa del perfil</p>
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                    {{-- Cover / color strip --}}
                    <div class="h-20" style="background-color: {{ $color_primary }}"></div>

                    {{-- Avatar overlay --}}
                    <div class="relative px-5">
                        <div class="-mt-8 flex h-16 w-16 items-center justify-center rounded-full border-4 border-wc-bg-tertiary bg-wc-bg-secondary"
                             style="border-color: {{ $color_primary }}20">
                            @if($photo_url)
                                <img src="{{ $photo_url }}" alt="" class="h-full w-full rounded-full object-cover" loading="lazy" decoding="async">
                            @else
                                <span class="font-display text-2xl" style="color: {{ $color_primary }}">{{ substr($coachName, 0, 1) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="px-5 pb-5 pt-2">
                        <h4 class="font-display text-xl tracking-wide text-wc-text">{{ $coachName }}</h4>

                        @if($city || $experience)
                            <div class="mt-1 flex items-center gap-2 text-xs text-wc-text-tertiary">
                                @if($city)
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                        </svg>
                                        {{ $city }}
                                    </span>
                                @endif
                                @if($experience)
                                    <span>{{ $experience }} exp.</span>
                                @endif
                            </div>
                        @endif

                        @if($bio)
                            <p class="mt-3 text-sm text-wc-text-secondary leading-relaxed">{{ Str::limit($bio, 150) }}</p>
                        @endif

                        @if($specializations_input)
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach(array_slice(array_filter(array_map('trim', explode(',', $specializations_input))), 0, 4) as $spec)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold text-white"
                                          style="background-color: {{ $color_primary }}">{{ $spec }}</span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Social links preview --}}
                        <div class="mt-4 flex items-center gap-3 border-t border-wc-border pt-3">
                            @if($whatsapp)
                                <span class="flex items-center gap-1 text-xs text-emerald-500">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                    WhatsApp
                                </span>
                            @endif
                            @if($instagram)
                                <span class="flex items-center gap-1 text-xs text-pink-500">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                                    {{ $instagram }}
                                </span>
                            @endif
                        </div>

                        @if(!$public_visible)
                            <div class="mt-3 flex items-center gap-2 rounded-lg bg-orange-500/10 px-3 py-2 text-xs text-orange-500">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                                Este perfil no es visible publicamente
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ==================== REFERRALS TAB ==================== --}}
    @if($activeTab === 'referrals')
    <div class="space-y-5">

        {{-- Stats row --}}
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Total referidos</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
                        <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $totalReferrals }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">referidos totales</p>
            </div>

            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Pendientes</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10">
                        <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 font-data text-3xl font-bold text-orange-500">{{ $pendingReferrals }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">esperando registro</p>
            </div>

            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Registrados</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
                        <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $registeredReferrals }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">ya registrados</p>
            </div>

            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Convertidos</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
                        <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 font-data text-3xl font-bold text-emerald-500">{{ $convertedReferrals }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">pagaron plan</p>
            </div>
        </div>

        {{-- Referral link + commission --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 lg:col-span-2">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Tu link de referidos</h3>
                <p class="mt-1 text-xs text-wc-text-tertiary">Comparte este link para que nuevos clientes se registren con tu codigo</p>
                <div class="mt-3 flex items-center gap-2">
                    <div class="flex-1 overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5">
                        <p class="truncate font-mono text-sm text-wc-accent">{{ $referralLink }}</p>
                    </div>
                    <button
                        x-on:click="navigator.clipboard.writeText('{{ $referralLink }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="flex shrink-0 items-center gap-2 rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
                        <template x-if="!copied">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                            </svg>
                        </template>
                        <template x-if="copied">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </template>
                        <span x-text="copied ? 'Copiado!' : 'Copiar link'"></span>
                    </button>
                </div>

                @if($totalClicks > 0)
                    <div class="mt-4 flex items-center gap-4 text-xs text-wc-text-tertiary">
                        <span class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            {{ $totalClicks }} clics en tu link
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="h-3 w-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            {{ $convertedClicks }} convertidos desde link
                        </span>
                    </div>
                @endif
            </div>

            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">Comision</h3>
                <div class="mt-4 flex items-baseline gap-1">
                    <span class="font-data text-4xl font-bold text-wc-accent">{{ $commissionRate }}%</span>
                </div>
                <p class="mt-2 text-xs text-wc-text-tertiary">Por cada referido que se convierta en cliente activo</p>
                <div class="mt-4 rounded-lg bg-wc-accent/5 border border-wc-accent/10 px-3 py-2">
                    <p class="text-xs text-wc-text-secondary">Convertidos: <span class="font-semibold text-emerald-500">{{ $convertedReferrals }}</span></p>
                </div>
            </div>
        </div>

        {{-- Referrals table --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Historial de referidos</h3>

            @if(count($referralsList) > 0)
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-wc-border text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
                                <th class="pb-3 pr-4">Email</th>
                                <th class="pb-3 pr-4">Estado</th>
                                <th class="pb-3 pr-4">Cliente</th>
                                <th class="pb-3 pr-4">Fecha</th>
                                <th class="pb-3 pr-4">Conversion</th>
                                <th class="pb-3">Recompensa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-wc-border">
                            @foreach($referralsList as $ref)
                                <tr class="text-wc-text-secondary">
                                    <td class="py-3 pr-4 text-wc-text">{{ $ref['email'] }}</td>
                                    <td class="py-3 pr-4">
                                        @if($ref['status'] === 'pending')
                                            <span class="inline-flex rounded-full bg-orange-500/10 px-2 py-0.5 text-[10px] font-semibold text-orange-500">Pendiente</span>
                                        @elseif($ref['status'] === 'registered')
                                            <span class="inline-flex rounded-full bg-sky-500/10 px-2 py-0.5 text-[10px] font-semibold text-sky-500">Registrado</span>
                                        @elseif($ref['status'] === 'converted')
                                            <span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-500">Convertido</span>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4">{{ $ref['client_name'] }}</td>
                                    <td class="py-3 pr-4 font-mono text-xs">{{ $ref['created_at'] }}</td>
                                    <td class="py-3 pr-4 font-mono text-xs">{{ $ref['converted_at'] }}</td>
                                    <td class="py-3">
                                        @if($ref['reward_granted'])
                                            <span class="inline-flex items-center gap-1 text-emerald-500">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                                Si
                                            </span>
                                        @else
                                            <span class="text-wc-text-tertiary">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="mt-6 flex flex-col items-center py-8 text-center">
                    <svg class="h-10 w-10 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    <p class="mt-3 text-sm font-medium text-wc-text-secondary">Sin referidos aun</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Comparte tu link para empezar a referir clientes</p>
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ==================== REVENUE TAB ==================== --}}
    @if($activeTab === 'revenue')
    <div class="space-y-5">

        {{-- Stats row --}}
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Revenue total</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
                        <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 font-data text-2xl font-bold text-wc-text sm:text-3xl">${{ number_format($totalRevenue, 0, ',', '.') }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">COP contribuido</p>
            </div>

            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Clientes activos</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
                        <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $revenueActiveClients }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">clientes activos</p>
            </div>

            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Tasa comision</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 14.25 6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 font-data text-3xl font-bold text-wc-accent">{{ $commissionRate }}%</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">sobre revenue</p>
            </div>

            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Comision est.</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
                        <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 font-data text-2xl font-bold text-emerald-500 sm:text-3xl">${{ number_format($estimatedCommission, 0, ',', '.') }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">COP estimada</p>
            </div>
        </div>

        {{-- Monthly revenue chart (CSS bars) --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Revenue mensual</h3>
            <p class="mt-1 text-xs text-wc-text-tertiary">Ultimos 6 meses de pagos aprobados de tus clientes</p>

            @php
                $maxRevenue = max(array_column($monthlyRevenue, 'amount') ?: [1]);
            @endphp

            <div class="mt-6 flex items-end gap-2 sm:gap-4" style="height: 200px">
                @foreach($monthlyRevenue as $month)
                    @php
                        $pct = $maxRevenue > 0 ? ($month['amount'] / $maxRevenue) * 100 : 0;
                        $barHeight = max($pct, 2);
                    @endphp
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <span class="text-[10px] font-data font-semibold text-wc-text-secondary">
                            @if($month['amount'] > 0)
                                ${{ number_format($month['amount'] / 1000, 0) }}k
                            @else
                                $0
                            @endif
                        </span>
                        <div class="w-full rounded-t-md transition-all duration-500"
                             style="height: {{ $barHeight }}%; background-color: {{ $month['amount'] > 0 ? '#10B981' : 'var(--color-wc-border)' }}; min-height: 4px;">
                        </div>
                        <span class="text-[10px] font-medium text-wc-text-tertiary whitespace-nowrap">{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Client contributions table --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Contribucion por cliente</h3>
            <p class="mt-1 text-xs text-wc-text-tertiary">Desglose de pagos por cliente asignado a ti</p>

            @if(count($clientContributions) > 0)
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-wc-border text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
                                <th class="pb-3 pr-4">Cliente</th>
                                <th class="pb-3 pr-4">Plan</th>
                                <th class="pb-3 pr-4 text-right">Total pagado</th>
                                <th class="pb-3 pr-4 text-right">Pagos</th>
                                <th class="pb-3 text-right">Ultimo pago</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-wc-border">
                            @foreach($clientContributions as $contrib)
                                <tr class="text-wc-text-secondary">
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                                                <span class="text-xs font-semibold text-wc-accent">{{ substr($contrib['name'], 0, 1) }}</span>
                                            </div>
                                            <span class="text-sm font-medium text-wc-text">{{ $contrib['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span class="inline-flex rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent capitalize">{{ $contrib['plan'] }}</span>
                                    </td>
                                    <td class="py-3 pr-4 text-right font-data font-semibold text-emerald-500">${{ number_format($contrib['total'], 0, ',', '.') }}</td>
                                    <td class="py-3 pr-4 text-right font-data">{{ $contrib['payments'] }}</td>
                                    <td class="py-3 text-right font-mono text-xs">{{ $contrib['last_payment'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-wc-border">
                                <td colspan="2" class="py-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Total</td>
                                <td class="py-3 pr-4 text-right font-data text-lg font-bold text-emerald-500">${{ number_format($totalRevenue, 0, ',', '.') }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="mt-6 flex flex-col items-center py-8 text-center">
                    <svg class="h-10 w-10 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p class="mt-3 text-sm font-medium text-wc-text-secondary">Sin datos de revenue</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Los pagos de tus clientes asignados apareceran aqui</p>
                </div>
            @endif
        </div>
    </div>
    @endif

</div>
