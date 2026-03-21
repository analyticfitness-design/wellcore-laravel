<div class="space-y-6 animate-pulse">
    {{-- Header skeleton --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-2">
            <div class="h-8 w-56 rounded-lg bg-wc-bg-tertiary"></div>
            <div class="h-4 w-80 rounded bg-wc-bg-tertiary"></div>
        </div>
        <div class="h-10 w-36 rounded-lg bg-wc-bg-tertiary"></div>
    </div>

    {{-- Stats cards skeleton --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        @for ($i = 0; $i < 4; $i++)
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <div class="h-3 w-20 rounded bg-wc-bg-secondary"></div>
                    <div class="h-8 w-8 rounded-lg bg-wc-bg-secondary"></div>
                </div>
                <div class="mt-3 h-8 w-16 rounded bg-wc-bg-secondary"></div>
                <div class="mt-1 h-3 w-24 rounded bg-wc-bg-secondary"></div>
            </div>
        @endfor
    </div>

    {{-- Content blocks skeleton --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @for ($i = 0; $i < 2; $i++)
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="h-5 w-40 rounded bg-wc-bg-secondary"></div>
                <div class="mt-4 space-y-3">
                    @for ($j = 0; $j < 3; $j++)
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 shrink-0 rounded-full bg-wc-bg-secondary"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 w-3/4 rounded bg-wc-bg-secondary"></div>
                                <div class="h-3 w-1/2 rounded bg-wc-bg-secondary"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        @endfor
    </div>
</div>
