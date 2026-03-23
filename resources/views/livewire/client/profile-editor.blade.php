<div
    x-data="{
        showToast: @entangle('showSuccess'),
        init() {
            Livewire.on('profile-saved', () => {
                this.showToast = true;
                setTimeout(() => { this.showToast = false; }, 3000);
            });
        }
    }"
>
    {{-- Success Toast --}}
    <div
        x-show="showToast"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-24 right-4 z-50 flex items-center gap-3 rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 shadow-lg backdrop-blur-sm lg:bottom-6 lg:right-6"
        x-cloak
    >
        <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <span class="text-sm font-medium text-green-400">Perfil actualizado correctamente</span>
    </div>

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">MI PERFIL</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Actualiza tu información personal y datos de entrenamiento</p>
    </div>

    <form wire:submit="save">
        <div class="grid gap-8 lg:grid-cols-2">

            {{-- Left Column: Personal Info --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-display text-xl tracking-wide text-wc-text">DATOS PERSONALES</h2>
                        <p class="text-xs text-wc-text-tertiary">Información básica de tu cuenta</p>
                    </div>
                </div>

                <div class="space-y-4">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Nombre completo</label>
                        <input
                            wire:model="name"
                            type="text"
                            id="name"
                            class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                            placeholder="Tu nombre"
                        >
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Email</label>
                        <input
                            wire:model="email"
                            type="email"
                            id="email"
                            class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                            placeholder="tu@email.com"
                        >
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- City --}}
                    <div>
                        <label for="city" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Ciudad</label>
                        <input
                            wire:model="city"
                            type="text"
                            id="city"
                            class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                            placeholder="Tu ciudad"
                        >
                        @error('city') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Birth Date --}}
                    <div>
                        <label for="birthDate" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Fecha de nacimiento</label>
                        <input
                            wire:model="birthDate"
                            type="date"
                            id="birthDate"
                            class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                        >
                        @error('birthDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- WhatsApp --}}
                    <div>
                        <label for="whatsapp" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">WhatsApp</label>
                        <input
                            wire:model="whatsapp"
                            type="text"
                            id="whatsapp"
                            class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                            placeholder="+52 123 456 7890"
                        >
                        @error('whatsapp') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label for="bio" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Bio</label>
                        <textarea
                            wire:model="bio"
                            id="bio"
                            rows="3"
                            class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                            placeholder="Cuéntanos sobre ti..."
                        ></textarea>
                        @error('bio') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Right Column: Fitness Info --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-display text-xl tracking-wide text-wc-text">DATOS FITNESS</h2>
                        <p class="text-xs text-wc-text-tertiary">Tu información de entrenamiento</p>
                    </div>
                </div>

                <div class="space-y-4">
                    {{-- Peso + Altura (inline) --}}
                    <div class="grid grid-cols-1 gap-4 xs:grid-cols-2 sm:grid-cols-2">
                        <div>
                            <label for="peso" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Peso (kg)</label>
                            <input
                                wire:model="peso"
                                type="number"
                                step="0.1"
                                id="peso"
                                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                                placeholder="75.0"
                            >
                            @error('peso') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="altura" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Altura (cm)</label>
                            <input
                                wire:model="altura"
                                type="number"
                                step="0.1"
                                id="altura"
                                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                                placeholder="175.0"
                            >
                            @error('altura') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Objetivo --}}
                    <div>
                        <label for="objetivo" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Objetivo</label>
                        <input
                            wire:model="objetivo"
                            type="text"
                            id="objetivo"
                            class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                            placeholder="Ej: Perder grasa, ganar músculo..."
                        >
                        @error('objetivo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Nivel --}}
                    <div>
                        <label for="nivel" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Nivel</label>
                        <select
                            wire:model="nivel"
                            id="nivel"
                            class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                        >
                            <option value="">Selecciona tu nivel</option>
                            <option value="principiante">Principiante</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="avanzado">Avanzado</option>
                        </select>
                        @error('nivel') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Lugar de Entreno --}}
                    <div>
                        <label for="lugarEntreno" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Lugar de entrenamiento</label>
                        <select
                            wire:model="lugarEntreno"
                            id="lugarEntreno"
                            class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                        >
                            <option value="">Selecciona lugar</option>
                            <option value="gym">Gimnasio</option>
                            <option value="casa">Casa</option>
                            <option value="ambos">Ambos</option>
                        </select>
                        @error('lugarEntreno') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Dias Disponibles --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-wc-text-secondary">Dias disponibles</label>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $dia)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm transition-colors hover:border-wc-accent/50">
                                    <input
                                        type="checkbox"
                                        wire:model="diasDisponibles"
                                        value="{{ strtolower($dia) }}"
                                        class="h-4 w-4 rounded border-wc-border bg-wc-bg-secondary text-wc-accent focus:ring-wc-accent/30"
                                    >
                                    <span class="text-wc-text-secondary">{{ $dia }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Restricciones --}}
                    <div>
                        <label for="restricciones" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Restricciones o lesiones</label>
                        <textarea
                            wire:model="restricciones"
                            id="restricciones"
                            rows="3"
                            class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                            placeholder="Ej: Lesion en rodilla derecha, alergia al gluten..."
                        ></textarea>
                        @error('restricciones') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="mt-8 flex justify-end">
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="btn-press flex w-full items-center justify-center gap-2 rounded-xl bg-wc-accent px-8 py-3.5 text-sm font-semibold text-white transition-all hover:bg-wc-accent-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto sm:py-3"
            >
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="save">Guardar Cambios</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>
