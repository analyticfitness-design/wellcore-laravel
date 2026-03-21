<div x-data="{ open: false, country: localStorage.getItem('wc_country') || 'CO' }" class="relative">
    <button @click="open = !open" class="flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text btn-press">
        <span x-text="{ CO: '🇨🇴', MX: '🇲🇽', CL: '🇨🇱', PE: '🇵🇪', AR: '🇦🇷', US: '🇺🇸' }[country] || '🌎'"></span>
        <span x-text="country">CO</span>
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
    </button>
    <div x-show="open" @click.away="open = false" x-transition
         class="absolute right-0 top-full mt-1 w-44 overflow-hidden rounded-lg border border-wc-border bg-wc-bg-secondary shadow-xl z-50">
        @foreach(['CO' => 'Colombia', 'MX' => 'Mexico', 'CL' => 'Chile', 'PE' => 'Peru', 'AR' => 'Argentina', 'US' => 'USA'] as $code => $name)
        <button @click="country = '{{ $code }}'; localStorage.setItem('wc_country', '{{ $code }}'); open = false; $dispatch('country-changed', { country: '{{ $code }}' })"
                class="flex w-full items-center gap-2 px-3 py-2 text-xs hover:bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text">
            <span>{{ ['CO'=>'🇨🇴','MX'=>'🇲🇽','CL'=>'🇨🇱','PE'=>'🇵🇪','AR'=>'🇦🇷','US'=>'🇺🇸'][$code] }}</span>
            {{ $name }}
        </button>
        @endforeach
    </div>
</div>
