<div class="space-y-6">

    {{-- Page header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">VIDEOS</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tips y demostraciones en video de tu coach.</p>
    </div>

    {{-- Search bar --}}
    <div class="relative">
        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar video..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-10 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none sm:max-w-sm"
            aria-label="Buscar video"
        />
        <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
            <svg class="h-4 w-4 animate-spin text-wc-accent" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>
    </div>

    {{-- Video player (shown when a video is selected) --}}
    @if($playingVideo)
        <div class="rounded-xl border border-wc-accent/30 bg-wc-bg-secondary" wire:transition id="video-player" aria-live="polite">

            {{-- Player header --}}
            <div class="flex items-center justify-between border-b border-wc-border px-5 py-4">
                <div>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">{{ $playingVideo->title }}</h2>
                    @if($playingVideo->duration_sec)
                        @php
                            $minutes = intdiv($playingVideo->duration_sec, 60);
                            $seconds = $playingVideo->duration_sec % 60;
                            $duration = sprintf('%d:%02d', $minutes, $seconds);
                        @endphp
                        <p class="mt-0.5 flex items-center gap-1.5 text-xs text-wc-text-tertiary">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            {{ $duration }}
                        </p>
                    @endif
                </div>
                <button
                    wire:click="play({{ $playingVideo->id }})"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-wc-text-secondary transition-colors hover:bg-wc-bg-tertiary hover:text-wc-text focus:outline-none focus:ring-2 focus:ring-wc-accent"
                    aria-label="Cerrar reproductor"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Player body --}}
            <div class="p-5">
                @php
                    $videoUrl = $playingVideo->video_url ?? '';
                    $isYoutube = str_contains($videoUrl, 'youtube.com') || str_contains($videoUrl, 'youtu.be');

                    if ($isYoutube) {
                        if (str_contains($videoUrl, 'youtu.be/')) {
                            $ytId = explode('youtu.be/', $videoUrl)[1];
                            $ytId = explode('?', $ytId)[0];
                        } elseif (preg_match('/[?&]v=([^&]+)/', $videoUrl, $m)) {
                            $ytId = $m[1];
                        } elseif (str_contains($videoUrl, '/embed/')) {
                            $ytId = explode('/embed/', $videoUrl)[1];
                            $ytId = explode('?', $ytId)[0];
                        } else {
                            $ytId = null;
                        }
                        $embedUrl = $ytId ? "https://www.youtube.com/embed/{$ytId}?rel=0&modestbranding=1&autoplay=1" : null;
                    }
                @endphp

                @if($isYoutube && isset($embedUrl))
                    <div class="aspect-video overflow-hidden rounded-xl">
                        <iframe
                            src="{{ $embedUrl }}"
                            title="{{ $playingVideo->title }}"
                            class="h-full w-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    </div>
                @elseif($videoUrl)
                    <div class="aspect-video overflow-hidden rounded-xl bg-wc-bg">
                        <video
                            src="{{ $videoUrl }}"
                            class="h-full w-full rounded-xl"
                            controls
                            autoplay
                            preload="auto"
                            title="{{ $playingVideo->title }}"
                        ></video>
                    </div>
                @else
                    <div class="flex aspect-video items-center justify-center rounded-xl bg-wc-bg">
                        <p class="text-sm text-wc-text-tertiary">Video no disponible.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Video grid --}}
    @if($videos->isEmpty())
        {{-- Empty state --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            <p class="mt-3 text-sm font-medium text-wc-text-secondary">
                @if($search)
                    Sin videos que coincidan con "{{ $search }}".
                @else
                    Tu coach no ha publicado videos aun.
                @endif
            </p>
            @if($search)
                <button
                    wire:click="$set('search', '')"
                    class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-xs font-semibold text-white hover:bg-wc-accent/90"
                >
                    Limpiar busqueda
                </button>
            @endif
        </div>
    @else
        {{-- Results count --}}
        <p class="text-xs text-wc-text-tertiary" wire:loading.class="opacity-50" aria-live="polite">
            {{ $videos->count() }} {{ $videos->count() === 1 ? 'video' : 'videos' }}
            @if($search)
                que coinciden con "<span class="text-wc-accent">{{ $search }}</span>"
            @endif
        </p>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($videos as $video)
                @php
                    $isPlaying = $playingVideo && $playingVideo->id === $video->id;
                    $minutes = intdiv($video->duration_sec ?? 0, 60);
                    $seconds = ($video->duration_sec ?? 0) % 60;
                    $duration = sprintf('%d:%02d', $minutes, $seconds);
                @endphp
                <button
                    wire:click="play({{ $video->id }})"
                    class="group cursor-pointer rounded-xl border text-left transition-all focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg
                        {{ $isPlaying
                            ? 'border-wc-accent bg-wc-accent/5'
                            : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}"
                    aria-label="{{ $isPlaying ? 'Detener' : 'Reproducir' }}: {{ $video->title }}"
                    aria-pressed="{{ $isPlaying ? 'true' : 'false' }}"
                >
                    {{-- Thumbnail --}}
                    <div class="relative aspect-video overflow-hidden rounded-t-xl bg-wc-bg-secondary">
                        @if($video->thumbnail_url)
                            <img
                                src="{{ $video->thumbnail_url }}"
                                alt="{{ $video->title }}"
                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                loading="lazy"
                            />
                        @else
                            <div class="flex h-full items-center justify-center">
                                <svg class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                        @endif

                        {{-- Play / Playing overlay --}}
                        <div class="absolute inset-0 flex items-center justify-center bg-black/30
                            {{ $isPlaying ? 'opacity-100' : 'opacity-0 transition-opacity group-hover:opacity-100' }}">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full shadow-lg
                                {{ $isPlaying ? 'bg-wc-accent' : 'bg-wc-accent/90' }}">
                                @if($isPlaying)
                                    {{-- Pause icon --}}
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                                    </svg>
                                @else
                                    {{-- Play icon --}}
                                    <svg class="ml-0.5 h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                @endif
                            </div>
                        </div>

                        {{-- Duration badge --}}
                        @if($video->duration_sec)
                            <span class="absolute bottom-2 right-2 rounded-md bg-black/70 px-1.5 py-0.5 font-data text-[11px] font-medium text-white">
                                {{ $duration }}
                            </span>
                        @endif

                        {{-- Playing badge --}}
                        @if($isPlaying)
                            <span class="absolute left-2 top-2 flex items-center gap-1 rounded-full bg-wc-accent px-2 py-0.5 text-[10px] font-semibold text-white">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-75"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-white"></span>
                                </span>
                                REPRODUCIENDO
                            </span>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="p-4">
                        <h3 class="text-sm font-semibold leading-snug text-wc-text">{{ $video->title }}</h3>
                        @if($video->duration_sec)
                            <p class="mt-1 flex items-center gap-1 text-xs text-wc-text-tertiary">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                {{ $duration }}
                            </p>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    @endif
</div>
