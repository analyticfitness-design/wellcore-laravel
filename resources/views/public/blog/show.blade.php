<x-layouts.public>
    <x-slot:title>{{ $article['title'] }} - WellCore Blog</x-slot:title>
    <x-slot:description>{{ $article['excerpt'] }}</x-slot:description>

    {{-- Article Header --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            {{-- Back Link --}}
            <a href="{{ route('blog.index') }}" class="mb-8 inline-flex items-center gap-2 text-sm text-wc-text-secondary transition-colors hover:text-wc-accent">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Volver al Blog
            </a>

            <div class="mx-auto max-w-3xl pt-4">
                {{-- Category --}}
                <span class="inline-flex rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-wc-accent">
                    {{ $article['category'] }}
                </span>

                {{-- Title --}}
                <h1 class="mt-4 font-display text-3xl leading-tight tracking-wide text-wc-text sm:text-4xl lg:text-5xl">
                    {{ $article['title'] }}
                </h1>

                {{-- Meta --}}
                <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-wc-text-tertiary">
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        {{ $article['author'] }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v9.75" />
                        </svg>
                        {{ \Carbon\Carbon::parse($article['date'])->translatedFormat('d \d\e F, Y') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        {{ $article['reading_time'] }} de lectura
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- Article Content --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="article-content space-y-6 text-base leading-relaxed text-wc-text-secondary
                [&>h3]:font-display [&>h3]:text-xl [&>h3]:tracking-wide [&>h3]:text-wc-text [&>h3]:mt-10 [&>h3]:mb-4
                [&>p]:text-wc-text-secondary
                [&>ul]:my-4 [&>ul]:space-y-2 [&>ul]:pl-0
                [&>ul>li]:rounded-lg [&>ul>li]:border [&>ul>li]:border-wc-border [&>ul>li]:bg-wc-bg-tertiary [&>ul>li]:p-4 [&>ul>li]:text-sm [&>ul>li]:text-wc-text-secondary
                [&>ul>li>strong]:text-wc-text [&>ul>li>strong]:font-semibold
                [&_em]:text-wc-text-tertiary [&_em]:not-italic [&_em]:text-sm">
                {!! $article['content'] !!}
            </div>
        </div>
    </section>

    {{-- Related Articles --}}
    <section class="border-t border-wc-border bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="font-display text-2xl tracking-wide text-wc-text">ARTICULOS RELACIONADOS</h2>
            <p class="mt-2 text-sm text-wc-text-tertiary">Sigue aprendiendo con mas contenido basado en evidencia.</p>

            @php
                $related = collect($articles)
                    ->filter(fn($a) => $a['slug'] !== $article['slug'])
                    ->shuffle()
                    ->take(3);
            @endphp

            <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ($related as $relatedArticle)
                    <article class="group flex flex-col overflow-hidden rounded-xl border border-wc-border bg-wc-bg transition-colors hover:border-wc-accent/30">
                        <div class="flex flex-1 flex-col p-6">
                            <div class="mb-3">
                                <span class="inline-flex rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-wc-accent">
                                    {{ $relatedArticle['category'] }}
                                </span>
                            </div>
                            <h3 class="mb-2 text-base font-semibold text-wc-text transition-colors group-hover:text-wc-accent">
                                <a href="{{ route('blog.show', $relatedArticle['slug']) }}">
                                    {{ $relatedArticle['title'] }}
                                </a>
                            </h3>
                            <p class="flex-1 text-sm text-wc-text-secondary line-clamp-2">
                                {{ $relatedArticle['excerpt'] }}
                            </p>
                            <div class="mt-4 flex items-center justify-between border-t border-wc-border pt-3">
                                <span class="text-xs text-wc-text-tertiary">{{ \Carbon\Carbon::parse($relatedArticle['date'])->translatedFormat('d M Y') }}</span>
                                <span class="text-xs text-wc-text-tertiary">{{ $relatedArticle['reading_time'] }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="border-t border-wc-border bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">LISTO PARA TRANSFORMARTE?</h2>
            <p class="mx-auto mt-3 max-w-lg text-sm text-wc-text-secondary">El conocimiento sin accion no produce resultados. Da el primer paso con coaching personalizado basado en ciencia.</p>
            <div class="mt-8">
                <a href="{{ route('inscripcion') }}" class="inline-flex items-center rounded-lg bg-wc-accent px-6 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover">
                    Comenzar Ahora
                    <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

</x-layouts.public>
