{{-- Google Analytics GA4 --}}
@if(config('services.google.analytics_id'))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ config('services.google.analytics_id') }}', {
        anonymize_ip: true,
        cookie_flags: 'SameSite=None;Secure'
    });
</script>
@endif
