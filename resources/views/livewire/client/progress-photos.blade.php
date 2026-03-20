<div
    x-data="{
        showToast: @entangle('showSuccess'),
        init() {
            Livewire.on('photos-uploaded', () => {
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
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl border border-green-500/30 bg-green-500/10 px-5 py-3 shadow-lg backdrop-blur-sm"
        x-cloak
    >
        <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <span class="text-sm font-medium text-green-400">Fotos subidas correctamente</span>
    </div>

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">FOTOS DE PROGRESO</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Registra tu transformacion con fotos periodicas</p>
    </div>

    {{-- Upload Section --}}
    <div class="mb-8 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
        <div class="mb-5 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10">
                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
            </div>
            <div>
                <h2 class="font-display text-xl tracking-wide text-wc-text">SUBIR FOTOS</h2>
                <p class="text-xs text-wc-text-tertiary">Sube fotos de frente, lado y espalda</p>
            </div>
        </div>

        <form wire:submit="uploadPhotos">
            {{-- Date Picker --}}
            <div class="mb-5">
                <label for="uploadDate" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Fecha</label>
                <input
                    wire:model="uploadDate"
                    type="date"
                    id="uploadDate"
                    class="w-full max-w-xs rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                >
                @error('uploadDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Upload Zones --}}
            <div class="grid gap-4 sm:grid-cols-3">
                {{-- Frente --}}
                <div>
                    <label
                        for="photoFrente"
                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-wc-border bg-wc-bg-secondary px-4 py-8 text-center transition-colors hover:border-wc-accent/50"
                    >
                        @if($photoFrente)
                            <svg class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <p class="mt-2 text-sm font-medium text-green-400">Foto seleccionada</p>
                        @else
                            <svg class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                            <p class="mt-2 text-sm font-medium text-wc-text-secondary">Frente</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">Click para seleccionar</p>
                        @endif
                        <input wire:model="photoFrente" type="file" id="photoFrente" accept="image/*" class="hidden">
                    </label>
                    @error('photoFrente') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Lado --}}
                <div>
                    <label
                        for="photoLado"
                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-wc-border bg-wc-bg-secondary px-4 py-8 text-center transition-colors hover:border-wc-accent/50"
                    >
                        @if($photoLado)
                            <svg class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <p class="mt-2 text-sm font-medium text-green-400">Foto seleccionada</p>
                        @else
                            <svg class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                            <p class="mt-2 text-sm font-medium text-wc-text-secondary">Lado</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">Click para seleccionar</p>
                        @endif
                        <input wire:model="photoLado" type="file" id="photoLado" accept="image/*" class="hidden">
                    </label>
                    @error('photoLado') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Espalda --}}
                <div>
                    <label
                        for="photoEspalda"
                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-wc-border bg-wc-bg-secondary px-4 py-8 text-center transition-colors hover:border-wc-accent/50"
                    >
                        @if($photoEspalda)
                            <svg class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <p class="mt-2 text-sm font-medium text-green-400">Foto seleccionada</p>
                        @else
                            <svg class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                            <p class="mt-2 text-sm font-medium text-wc-text-secondary">Espalda</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">Click para seleccionar</p>
                        @endif
                        <input wire:model="photoEspalda" type="file" id="photoEspalda" accept="image/*" class="hidden">
                    </label>
                    @error('photoEspalda') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            @error('upload') <p class="mt-3 text-sm text-red-500">{{ $message }}</p> @enderror

            {{-- Upload Button --}}
            <div class="mt-5 flex justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="flex items-center gap-2 rounded-xl bg-wc-accent px-6 py-3 text-sm font-semibold text-white transition-all hover:bg-wc-accent-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <svg wire:loading wire:target="uploadPhotos" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg wire:loading.remove wire:target="uploadPhotos" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    <span wire:loading.remove wire:target="uploadPhotos">Subir Fotos</span>
                    <span wire:loading wire:target="uploadPhotos">Subiendo...</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Photo Gallery --}}
    @if($photos->isEmpty())
        {{-- Empty State --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
            <h3 class="mt-4 font-display text-xl text-wc-text">SIN FOTOS AUN</h3>
            <p class="mt-2 text-sm text-wc-text-secondary">Sube tu primera foto de progreso para empezar a registrar tu transformacion.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($photos as $date => $datePhotos)
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                    {{-- Date Header --}}
                    <button
                        wire:click="selectDate('{{ $date }}')"
                        class="flex w-full items-center justify-between px-5 py-4 text-left transition-colors hover:bg-wc-bg-secondary"
                    >
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            <span class="font-display text-lg tracking-wide text-wc-text">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
                            <span class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-xs text-wc-text-tertiary">{{ $datePhotos->count() }} {{ $datePhotos->count() === 1 ? 'foto' : 'fotos' }}</span>
                        </div>
                        <svg class="h-5 w-5 text-wc-text-tertiary transition-transform {{ $selectedDate === $date ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    {{-- Photos Grid --}}
                    @if($selectedDate === $date)
                        <div class="border-t border-wc-border px-5 py-4">
                            <div class="grid gap-4 sm:grid-cols-3">
                                @php
                                    $tipos = ['frente', 'lado', 'espalda'];
                                    $labels = ['frente' => 'Frente', 'lado' => 'Lado', 'espalda' => 'Espalda'];
                                @endphp

                                @foreach($tipos as $tipo)
                                    @php $photo = $datePhotos->firstWhere('tipo', $tipo); @endphp
                                    <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary">
                                        @if($photo && $photo->filename && Storage::disk('public')->exists($photo->filename))
                                            <img
                                                src="{{ Storage::url($photo->filename) }}"
                                                alt="{{ $labels[$tipo] }}"
                                                class="aspect-[3/4] w-full object-cover"
                                            >
                                        @else
                                            {{-- Placeholder --}}
                                            <div class="flex aspect-[3/4] w-full flex-col items-center justify-center bg-wc-bg-secondary">
                                                <svg class="h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                                </svg>
                                                @if($photo)
                                                    <p class="mt-2 text-xs text-wc-text-tertiary">Archivo no disponible</p>
                                                @else
                                                    <p class="mt-2 text-xs text-wc-text-tertiary">Sin foto</p>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="px-3 py-2 text-center">
                                            <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">{{ $labels[$tipo] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
