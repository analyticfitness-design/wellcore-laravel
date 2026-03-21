<x-layouts.public>
    <x-slot:title>Blog - WellCore Fitness</x-slot:title>
    <x-slot:description>Articulos sobre entrenamiento, nutricion y ciencia del fitness. Informacion basada en evidencia para tu transformacion.</x-slot:description>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-24 lg:px-8">
            <h1 class="font-display text-5xl tracking-wide text-wc-text sm:text-6xl lg:text-7xl">BLOG</h1>
            <p class="mt-3 text-lg text-wc-text-secondary">Ciencia del entrenamiento y nutricion</p>
            <p class="mt-2 max-w-xl text-sm text-wc-text-tertiary">Articulos basados en evidencia cientifica para que tomes decisiones informadas sobre tu entrenamiento, nutricion y recuperacion.</p>
        </div>
    </section>

    {{-- Articles Grid --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">

            @php
                $articles = \App\Http\Controllers\BlogController::getArticles();
            @endphp

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <article class="group flex flex-col overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary transition-colors hover:border-wc-accent/30">
                        {{-- Card Body --}}
                        <div class="flex flex-1 flex-col p-6">
                            {{-- Category Badge --}}
                            <div class="mb-3">
                                <span class="inline-flex rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-wc-accent">
                                    {{ $article['category'] }}
                                </span>
                            </div>

                            {{-- Title --}}
                            <h2 class="mb-2 text-lg font-semibold text-wc-text transition-colors group-hover:text-wc-accent">
                                <a href="{{ route('blog.show', $article['slug']) }}" class="after:absolute after:inset-0">
                                    {{ $article['title'] }}
                                </a>
                            </h2>

                            {{-- Excerpt --}}
                            <p class="mb-4 flex-1 text-sm leading-relaxed text-wc-text-secondary line-clamp-3">
                                {{ $article['excerpt'] }}
                            </p>

                            {{-- Footer --}}
                            <div class="flex items-center justify-between border-t border-wc-border pt-4">
                                <span class="text-xs text-wc-text-tertiary">
                                    {{ \Carbon\Carbon::parse($article['date'])->translatedFormat('d M Y') }}
                                </span>
                                <span class="flex items-center gap-1 text-xs text-wc-text-tertiary">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    {{ $article['reading_time'] }}
                                </span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

        </div>
    </section>

    {{-- CTA Section --}}
    <section class="border-t border-wc-border bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">LISTO PARA TRANSFORMARTE?</h2>
            <p class="mx-auto mt-3 max-w-lg text-sm text-wc-text-secondary">Deja de leer sobre resultados y empieza a vivirlos. Coaching 1:1 basado en la misma ciencia de nuestros articulos.</p>
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
