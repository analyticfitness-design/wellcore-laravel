<x-layouts.public>
    <x-slot:title>Blog - WellCore Fitness</x-slot:title>
    <x-slot:description>Articulos sobre entrenamiento, nutricion y ciencia del fitness. Informacion basada en evidencia para tu transformacion.</x-slot:description>

    @php
        $articles = \App\Http\Controllers\BlogController::getArticles();
    @endphp

    {{-- Hero --}}
    <section class="hero-gradient relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
        {{-- Parallax decorative orbs --}}
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15"></div>
            <div class="parallax-orb parallax-orb-4" data-parallax-speed="0.25"></div>
            <div class="parallax-orb parallax-orb-5" data-parallax-speed="0.1"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-24 lg:px-8" data-animate="fadeInUp">
            <h1 class="font-display text-5xl tracking-wide text-wc-text sm:text-6xl lg:text-7xl">BLOG</h1>
            <p class="mt-3 text-lg text-wc-text-secondary">Ciencia del entrenamiento y nutricion</p>
            <p class="mt-2 max-w-xl text-sm text-wc-text-tertiary">Articulos basados en evidencia cientifica para que tomes decisiones informadas sobre tu entrenamiento, nutricion y recuperacion.</p>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="section-divider" aria-hidden="true"></div>

    {{-- Articles Grid --}}
    <section class="bg-wc-bg"
        x-data="{
            category: 'all',
            search: '',
            articles: {{ json_encode(collect($articles)->map(fn($a) => ['slug' => $a['slug'], 'title' => $a['title'], 'category' => $a['category']])->values()) }},
            matchesCategory(cat) {
                return this.category === 'all' || cat === this.category;
            },
            matchesSearch(title) {
                return this.search.trim() === '' || title.toLowerCase().includes(this.search.toLowerCase());
            }
        }">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">

            {{-- Search Bar --}}
            <div class="relative max-w-md mx-auto mb-10">
                <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-wc-text-tertiary pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Buscar articulo..."
                    class="w-full rounded-full border border-wc-border bg-wc-bg-secondary py-3 pl-12 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary outline-none ring-0 transition-colors focus:border-wc-accent/50 focus:ring-1 focus:ring-wc-accent/30"
                />
            </div>

            {{-- Category Filter --}}
            <div class="mb-10 flex flex-wrap items-center justify-center gap-2">
                <button
                    @click="category = 'all'"
                    :class="category === 'all' ? 'bg-wc-accent text-white' : 'border border-wc-border text-wc-text-secondary hover:text-wc-text hover:border-wc-accent/40'"
                    class="rounded-full px-5 py-2 text-xs font-semibold uppercase tracking-wider transition-all duration-200">
                    Todos
                </button>
                <button
                    @click="category = 'Entrenamiento'"
                    :class="category === 'Entrenamiento' ? 'bg-wc-accent text-white' : 'border border-wc-border text-wc-text-secondary hover:text-wc-text hover:border-wc-accent/40'"
                    class="rounded-full px-5 py-2 text-xs font-semibold uppercase tracking-wider transition-all duration-200">
                    Entrenamiento
                </button>
                <button
                    @click="category = 'Nutricion'"
                    :class="category === 'Nutricion' ? 'bg-wc-accent text-white' : 'border border-wc-border text-wc-text-secondary hover:text-wc-text hover:border-wc-accent/40'"
                    class="rounded-full px-5 py-2 text-xs font-semibold uppercase tracking-wider transition-all duration-200">
                    Nutricion
                </button>
                <button
                    @click="category = 'Ciencia'"
                    :class="category === 'Ciencia' ? 'bg-wc-accent text-white' : 'border border-wc-border text-wc-text-secondary hover:text-wc-text hover:border-wc-accent/40'"
                    class="rounded-full px-5 py-2 text-xs font-semibold uppercase tracking-wider transition-all duration-200">
                    Ciencia
                </button>
            </div>

            {{-- Grid --}}
            <div class="stagger-grid grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $index => $article)
                    @php $delay = (($index % 3) + 1) * 100; @endphp
                    <article
                        class="card-hover-lift scroll-reveal group relative flex flex-col overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary transition-colors hover:border-wc-accent/30"
                        data-animate="fadeInUp"
                        data-delay="{{ $delay }}"
                        data-category="{{ $article['category'] }}"
                        x-show="matchesCategory('{{ $article['category'] }}') && matchesSearch('{{ addslashes($article['title']) }}')"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95">

                        {{-- Gradient Header --}}
                        <div class="shimmer relative h-32 overflow-hidden bg-gradient-to-br {{ $article['gradient'] ?? 'from-wc-accent/20 to-wc-bg-tertiary' }}">
                            {{-- Dot pattern overlay --}}
                            <div class="absolute inset-0" style="background-image: radial-gradient(circle, currentColor 0.5px, transparent 0.5px); background-size: 12px 12px; opacity: 0.04;"></div>
                            {{-- Decorative lines --}}
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full border border-current opacity-[0.06]"></div>
                            <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full border border-current opacity-[0.08]"></div>
                            <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-wc-border to-transparent"></div>
                            {{-- Category Badge overlaid --}}
                            <div class="absolute bottom-3 left-4">
                                <span class="badge-shine inline-flex rounded-full bg-wc-bg/80 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-wc-accent backdrop-blur-sm ring-1 ring-wc-border/50">
                                    {{ $article['category'] }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="flex flex-1 flex-col p-6">
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

    {{-- Section Divider --}}
    <div class="section-divider" aria-hidden="true"></div>

    {{-- CTA Section --}}
    <section class="relative overflow-hidden border-t border-wc-border bg-wc-bg-tertiary">
        {{-- Gradient orbs --}}
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-wc-accent/5 blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-wc-accent/5 blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">LISTO PARA TRANSFORMARTE?</h2>
            <p class="mx-auto mt-3 max-w-lg text-sm text-wc-text-secondary">Deja de leer sobre resultados y empieza a vivirlos. Coaching 1:1 basado en la misma ciencia de nuestros articulos.</p>
            <div class="mt-8">
                <a href="{{ route('inscripcion') }}" class="btn-press pulse-glow inline-flex items-center rounded-lg bg-wc-accent px-6 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover">
                    Comenzar Ahora
                    <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

</x-layouts.public>
