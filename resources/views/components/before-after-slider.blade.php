@props(['before' => null, 'after' => null, 'height' => 'h-64'])

<div x-data="{ position: 50 }" class="relative overflow-hidden rounded-xl {{ $height }}">
    {{-- After image (full width, behind) --}}
    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-emerald-500/5">
        @if($after)
            <img src="{{ $after }}" alt="Despues" class="h-full w-full object-cover" loading="lazy" decoding="async">
        @else
            <div class="flex h-full items-center justify-center">
                <span class="font-display text-lg text-emerald-400/60">DESPUES</span>
            </div>
        @endif
    </div>

    {{-- Before image (clipped by slider position) --}}
    <div class="absolute inset-0 bg-gradient-to-br from-zinc-500/20 to-zinc-500/5" :style="'clip-path: inset(0 ' + (100 - position) + '% 0 0)'">
        @if($before)
            <img src="{{ $before }}" alt="Antes" class="h-full w-full object-cover" loading="lazy" decoding="async">
        @else
            <div class="flex h-full items-center justify-center">
                <span class="font-display text-lg text-wc-text-tertiary/60">ANTES</span>
            </div>
        @endif
    </div>

    {{-- Slider line --}}
    <div class="absolute inset-y-0 z-10 w-0.5 bg-white/80" :style="'left: ' + position + '%'">
        {{-- Drag handle --}}
        <div class="absolute left-1/2 top-1/2 flex h-10 w-10 -translate-x-1/2 -translate-y-1/2 cursor-grab items-center justify-center rounded-full border-2 border-white bg-wc-bg shadow-lg active:cursor-grabbing"
             x-on:mousedown.prevent="
                const onMove = (e) => { position = Math.max(5, Math.min(95, (e.clientX - $el.closest('.relative').getBoundingClientRect().left) / $el.closest('.relative').offsetWidth * 100)); };
                const onUp = () => { document.removeEventListener('mousemove', onMove); document.removeEventListener('mouseup', onUp); };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
             "
             x-on:touchstart.prevent="
                const onMove = (e) => { const t = e.touches[0]; position = Math.max(5, Math.min(95, (t.clientX - $el.closest('.relative').getBoundingClientRect().left) / $el.closest('.relative').offsetWidth * 100)); };
                const onUp = () => { document.removeEventListener('touchmove', onMove); document.removeEventListener('touchend', onUp); };
                document.addEventListener('touchmove', onMove);
                document.addEventListener('touchend', onUp);
             ">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
        </div>
    </div>
</div>
