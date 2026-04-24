<div>

    {{-- ─── MOBILE: Hero + Quick Actions ─── --}}
    <div class="px-4 pt-3 lg:hidden">
        @include('livewire.coach.partials.hero-today')
        @include('livewire.coach.partials.kpi-cards')
    </div>

    {{-- ─── DESKTOP: Alert bar + Hero + KPIs ─── --}}
    <div class="hidden lg:block px-6 pt-6">
        @include('livewire.coach.partials.alert-bar')
        @include('livewire.coach.partials.hero-today')
        @include('livewire.coach.partials.kpi-cards')
    </div>

    {{-- ─── MAIN CONTENT AREA ─── --}}
    <div class="px-4 lg:px-6 lg:grid lg:grid-cols-12 lg:gap-5">

        {{-- Left column: 8/12 on desktop --}}
        <div class="lg:col-span-8">
            @include('livewire.coach.partials.urgent-clients')
            @include('livewire.coach.partials.today-activity')
            @include('livewire.coach.partials.charts-section')
        </div>

        {{-- Right column: 4/12 on desktop --}}
        <div class="lg:col-span-4">
            @include('livewire.coach.partials.recent-messages')
            @include('livewire.coach.partials.tickets')
        </div>

    </div>

</div>
