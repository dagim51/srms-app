@props([
    'placement' => 'home_header',
    'campaign' => null,
])

@php
    use App\Domains\Advertising\Models\MarketingCampaign;

    $activeCampaign = $campaign;

    if (! $activeCampaign) {
        $activeCampaign = MarketingCampaign::where('placement', $placement)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
            })
            ->latest()
            ->first();
    }
@endphp

@if ($activeCampaign)
    <div 
        id="ad-banner-{{ $activeCampaign->id }}"
        data-ad-campaign-id="{{ $activeCampaign->id }}"
        data-ad-placement="{{ $placement }}"
        data-impression-url="{{ url('/api/v1/ads/' . $activeCampaign->id . '/impression') }}"
        {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 text-white shadow-lg shadow-emerald-950/20 my-6 transition-all duration-300 hover:shadow-xl']) }}
    >
        {{-- Background decorative elements --}}
        <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 h-48 w-48 rounded-full bg-emerald-400/20 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6 p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur-md uppercase tracking-wider">
                    Sponsored
                </span>
                <div>
                    <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-white">
                        {{ $activeCampaign->headline ?? $activeCampaign->name }}
                    </h3>
                    @if ($activeCampaign->subheadline)
                        <p class="mt-1 text-sm sm:text-base text-emerald-100 max-w-2xl line-clamp-2">
                            {{ $activeCampaign->subheadline }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex-shrink-0">
                <a 
                    href="{{ url('/api/v1/ads/' . $activeCampaign->id . '/click') }}"
                    onclick="if (window.trackAdClick) { window.trackAdClick('{{ $activeCampaign->id }}'); }"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-semibold text-emerald-900 bg-white hover:bg-emerald-50 shadow-md transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0"
                >
                    <span>{{ $activeCampaign->cta_text ?? 'Learn More' }}</span>
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>

        <script>
            (function() {
                const banner = document.getElementById('ad-banner-{{ $activeCampaign->id }}');
                if (!banner) return;

                const impressionUrl = banner.getAttribute('data-impression-url');
                let recorded = false;

                if ('IntersectionObserver' in window && impressionUrl) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting && !recorded) {
                                recorded = true;
                                fetch(impressionUrl, {
                                    method: 'POST',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                    }
                                }).catch(() => {});
                                observer.disconnect();
                            }
                        });
                    }, { threshold: 0.5 });
                    observer.observe(banner);
                }
            })();
        </script>
    </div>
@else
    {{-- Optional fallback promotional banner when no active campaign is configured --}}
    <div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 p-6 my-6 text-center']) }}>
        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">
            Reach thousands of verified Ethiopian buyers and renters.
            <a href="{{ route('dashboard.listings.index') }}" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline ml-1">
                Promote your property &rarr;
            </a>
        </p>
    </div>
@endif
