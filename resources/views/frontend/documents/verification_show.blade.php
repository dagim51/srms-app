<x-layouts.dashboard>
    <x-slot name="page-title">Verification: {{ $verification['id'] }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            :title="$verification['subject']"
            :description="__('Verification') . ' ' . $verification['id'] . ' • ' . __('Type:') . ' ' . __($verification['type']) . ' • ' . __('Status:') . ' ' . str_replace('_', ' ', $verification['status'])"
            icon="document"
            :badge="$verification['status']">
            <x-slot:actions>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('dashboard.documents.verifications') }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        {{ __('Back to Verifications') }}
                    </a>
                    <form method="POST" action="{{ route('dashboard.verifications.review', $verification['id']) }}" class="inline">
                        @csrf
                        <input type="hidden" name="decision" value="reject">
                        <button type="submit" class="btn btn-secondary text-error-600 border-error-200 hover:bg-error-50 dark:hover:bg-error-900/20 text-xs">{{ __('Reject') }}</button>
                    </form>
                    <form method="POST" action="{{ route('dashboard.verifications.review', $verification['id']) }}" class="inline">
                        @csrf
                        <input type="hidden" name="decision" value="approve">
                        <button type="submit" class="btn btn-primary bg-success-600 hover:bg-success-700 border-success-600 text-xs">{{ __('Approve') }}</button>
                    </form>
                </div>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Evidence Review -->
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('Evidence Submitted') }}</h2>
                    
                    <div class="space-y-6">
                        @foreach($evidence as $doc)
                            <div class="card-nested overflow-hidden">
                                <div class="bg-warm-50/50 dark:bg-surface/50 p-4 border-b border-[#EBDBC9] dark:border-[#273244] flex justify-between items-center">
                                    <div>
                                        <p class="font-bold text-muted dark:text-white">{{ $doc['name'] }}</p>
                                        <p class="text-xs text-muted">{{ $doc['type'] }}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button class="btn btn-sm btn-secondary">{{ __('Reject Image') }}</button>
                                        <button class="btn btn-sm btn-secondary text-success-600">{{ __('Verify Match') }}</button>
                                    </div>
                                </div>
                                <div class="bg-surface-subtle dark:bg-surface h-64 flex items-center justify-center p-4 relative group">
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 bg-black/50 transition-opacity">
                                        <button class="btn btn-primary">{{ __('Enlarge Preview') }}</button>
                                    </div>
                                    <p class="text-muted font-bold">{{ __('Secure Image Viewer') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Context Info -->
            <div class="space-y-8">
                <div class="card p-6">
                    <h3 class="card-title text-base mb-4">{{ __('Risk Assessment') }}</h3>
                    <div class="flex items-center gap-4 p-4 rounded-xl {{ $verification['risk_score'] === 'low' ? 'bg-success-50 dark:bg-success-900/20 border border-success-100' : 'bg-warning-50 dark:bg-warning-900/20 border border-warning-100' }}">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 {{ $verification['risk_score'] === 'low' ? 'bg-success-100 text-success-600' : 'bg-warning-100 text-warning-600' }}">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                        <div>
                            <p class="font-black text-lg text-muted dark:text-white capitalize">{{ $verification['risk_score'] }} Risk Score</p>
                            <p class="text-xs text-muted dark:text-dark-subtle mt-1">{{ __('Automated system checks passed.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="card p-6">
                    <h3 class="card-title text-base mb-4">{{ __('Review Notes') }}</h3>
                    <form method="POST" action="{{ route('dashboard.verifications.review', $verification['id']) }}">
                        @csrf
                        <input type="hidden" name="decision" value="{{ $verification['status'] === 'approved' ? 'approve' : 'reject' }}">
                        <textarea name="notes" class="input w-full h-32 mb-4" placeholder="{{ __('Enter notes regarding this verification case...') }}">{{ $verification['notes'] }}</textarea>
                        <button type="submit" class="btn btn-secondary w-full justify-center">{{ __('Save Notes') }}</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
