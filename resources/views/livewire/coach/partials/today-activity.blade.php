<section class="mb-4">
    <h2 class="font-display text-sm uppercase tracking-wider text-wc-text mb-3">
        {{ __('coach_dashboard.section_activity') }}
    </h2>

    @if(!empty($todayActivity))
    <div class="relative">
        {{-- Vertical timeline line --}}
        <div class="absolute left-[15px] top-0 bottom-0 w-px" style="background: var(--color-wc-border)"></div>

        <div class="space-y-3 pl-8">
            @foreach($todayActivity as $event)
            <div class="relative">
                {{-- Timeline dot --}}
                <div class="absolute -left-[25px] w-3 h-3 rounded-full border-2 border-wc-bg-tertiary
                    {{ $event['type'] === 'checkin'
                        ? 'bg-emerald-500'
                        : ($event['type'] === 'training' ? 'bg-blue-500' : 'bg-wc-accent') }}">
                </div>
                <div class="text-sm text-wc-text">
                    @if($event['type'] === 'checkin')
                        {{ __('coach_dashboard.activity_checkin', ['name' => $event['client_name']]) }}
                    @elseif($event['type'] === 'training')
                        {{ __('coach_dashboard.activity_training', ['name' => $event['client_name']]) }}
                    @else
                        {{ __('coach_dashboard.activity_message', ['name' => $event['client_name']]) }}
                    @endif
                </div>
                <div class="text-[11px] text-wc-text-tertiary">{{ $event['time_ago'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <p class="text-sm text-wc-text-tertiary">{{ __('coach_dashboard.empty_activity') }}</p>
    @endif
</section>
