<div class="space-y-6">
    {{-- Header skeleton --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-2">
            <div class="h-8 w-56 skeleton-premium"></div>
            <div class="h-4 w-80 skeleton-premium" style="animation-delay: 100ms;"></div>
        </div>
        <div class="h-10 w-36 skeleton-premium skeleton-pill" style="animation-delay: 200ms;"></div>
    </div>

    {{-- Stats cards skeleton — premium with accent borders --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        @for ($i = 0; $i < 4; $i++)
            <div class="skeleton-card bg-wc-bg-tertiary p-4 sm:p-5" style="animation-delay: {{ $i * 80 }}ms;">
                <div class="flex items-center justify-between">
                    <div class="h-3 w-20 skeleton-premium" style="animation-delay: {{ 300 + $i * 80 }}ms;"></div>
                    <div class="h-8 w-8 skeleton-premium skeleton-circle" style="animation-delay: {{ 350 + $i * 80 }}ms;"></div>
                </div>
                <div class="mt-3 h-8 w-16 skeleton-premium" style="animation-delay: {{ 400 + $i * 80 }}ms;"></div>
                <div class="mt-2 h-1.5 w-full rounded-full bg-wc-bg-secondary">
                    <div class="h-1.5 rounded-full skeleton-premium" style="width: {{ rand(30, 80) }}%; animation-delay: {{ 450 + $i * 80 }}ms;"></div>
                </div>
            </div>
        @endfor
    </div>

    {{-- Content blocks skeleton --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @for ($i = 0; $i < 2; $i++)
            <div class="skeleton-card bg-wc-bg-tertiary p-5">
                <div class="flex items-center justify-between">
                    <div class="h-5 w-40 skeleton-premium" style="animation-delay: {{ 500 + $i * 100 }}ms;"></div>
                    <div class="h-4 w-16 skeleton-premium skeleton-pill" style="animation-delay: {{ 550 + $i * 100 }}ms;"></div>
                </div>
                <div class="mt-4 space-y-3">
                    @for ($j = 0; $j < 3; $j++)
                        <div class="flex items-center gap-3" style="animation-delay: {{ 600 + ($i * 3 + $j) * 60 }}ms;">
                            <div class="h-10 w-10 shrink-0 skeleton-premium skeleton-circle"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 skeleton-premium" style="width: {{ rand(60, 90) }}%;"></div>
                                <div class="h-3 skeleton-premium" style="width: {{ rand(40, 60) }}%;"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        @endfor
    </div>

    {{-- Chart placeholder skeleton --}}
    <div class="skeleton-card bg-wc-bg-tertiary p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="h-5 w-48 skeleton-premium"></div>
            <div class="flex gap-2">
                <div class="h-8 w-16 skeleton-premium skeleton-pill"></div>
                <div class="h-8 w-16 skeleton-premium skeleton-pill"></div>
            </div>
        </div>
        <div class="h-48 w-full skeleton-premium" style="border-radius: 8px;"></div>
    </div>
</div>
