<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Viewing Details') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.viewings.index') }}" class="hover:text-primary-600 transition-colors">{{ __('Viewings') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('Details') }}</span>
    </x-slot>

    @php
        $isAgent = $viewing->agent_id === auth()->id();
        $statusColors = [
            'requested' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
            'confirmed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
            'rescheduled' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
            'completed' => 'bg-surface-subtle text-muted dark:bg-surface dark:text-dark-subtle',
            'cancelled' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300',
        ];
        $badgeColor = $statusColors[$viewing->status] ?? $statusColors['requested'];
        $canEdit = in_array($viewing->status, ['requested', 'confirmed', 'rescheduled']);
    @endphp

    <div class="max-w-4xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Viewing Appointment Details"
            :description="__('Detailed on-site walkthrough schedule, buyer/agent contact details, and viewing feedback for') . ' ' . ($viewing->listing->property->title ?? 'this property') . '.'"
            icon="calendar"
            badge="Viewings" />

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        @error('scheduled_time')
            <div class="p-4 rounded-xl bg-rose-50 text-rose-800 text-sm font-medium border border-rose-200">
                {{ $message }}
            </div>
        @enderror

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden">
            {{-- Header section --}}
            <div class="p-6 sm:p-8 border-b border-warm dark:border-warm flex flex-col sm:flex-row sm:items-start justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-block px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider {{ $badgeColor }}">
                            {{ $viewing->status }}
                        </span>
                        <span class="text-sm font-bold text-muted">ID: {{ substr($viewing->id, 0, 8) }}</span>
                    </div>
                    <h2 class="text-2xl font-bold font-display text-muted dark:text-white mb-2">{{ __('Property Viewing') }}</h2>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 text-sm text-muted dark:text-muted">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span class="font-bold text-muted dark:text-white">{{ $viewing->scheduled_at->format('l, F j, Y') }}</span>
                        </div>
                        <div class="hidden sm:block text-muted">•</div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="font-bold text-muted dark:text-white">{{ $viewing->scheduled_at->format('g:i A') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if($canEdit)
                    <div class="flex items-center gap-2 w-full sm:w-auto" x-data="{ showReschedule: false }">
                        @if($isAgent && $viewing->status !== 'confirmed')
                            <form action="{{ route('dashboard.viewings.update', $viewing) }}" method="POST" class="w-full sm:w-auto">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn btn-primary w-full shadow-sm text-sm py-2">{{ __('Confirm') }}</button>
                            </form>
                        @endif
                        
                        <button @click="showReschedule = !showReschedule" class="btn btn-outline btn-sm w-full sm:w-auto shadow-sm">{{ __('Reschedule') }}</button>
                        
                        <form action="{{ route('dashboard.viewings.update', $viewing) }}" method="POST" class="w-full sm:w-auto" onsubmit="return confirm('Are you sure you want to cancel this viewing?');">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-outline btn-sm w-full shadow-sm text-rose-600 border-rose-200 hover:bg-rose-50 dark:hover:bg-rose-900/20">{{ __('Cancel') }}</button>
                        </form>

                        {{-- Reschedule Dropdown/Modal --}}
                        <div x-show="showReschedule" x-cloak class="absolute right-8 mt-12 bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] shadow-xl rounded-2xl p-6 w-80 z-20" @click.away="showReschedule = false">
                            <h4 class="font-bold text-muted dark:text-white mb-4">{{ __('Reschedule Viewing') }}</h4>
                            <form action="{{ route('dashboard.viewings.update', $viewing) }}" method="POST" class="space-y-4">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="rescheduled">
                                
                                <div>
                                    <label class="block text-xs font-bold text-muted dark:text-dark-subtle mb-1">{{ __('New Date') }}</label>
                                    <input type="date" name="scheduled_date" required min="{{ date('Y-m-d') }}" class="form-input w-full rounded-xl text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-bold text-muted dark:text-dark-subtle mb-1">{{ __('New Time') }}</label>
                                    <select name="scheduled_time" required class="form-input w-full rounded-xl text-sm">
                                        <option value="09:00:00">{{ __('09:00 AM') }}</option>
                                        <option value="10:00:00">{{ __('10:00 AM') }}</option>
                                        <option value="11:30:00">{{ __('11:30 AM') }}</option>
                                        <option value="13:00:00">{{ __('01:00 PM') }}</option>
                                        <option value="14:30:00">{{ __('02:30 PM') }}</option>
                                        <option value="16:00:00">{{ __('04:00 PM') }}</option>
                                        <option value="17:00:00">{{ __('05:00 PM') }}</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-full btn-sm mt-2">{{ __('Update Schedule') }}</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-warm dark:divide-dark-border">
                {{-- Property Info --}}
                <div class="p-6 md:col-span-2">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('Property Details') }}</h3>
                    
                    @if($viewing->listing && $viewing->listing->property)
                        <div class="flex gap-4">
                            @php $primaryImg = $viewing->listing->property->media->first() ? ($viewing->listing->property->media->first()->url ?? $viewing->listing->property->media->first()->storage_key) : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=300&q=80'; @endphp
                            <div class="w-32 h-24 rounded-xl overflow-hidden flex-shrink-0 border border-warm dark:border-warm">
                                <img src="{{ $primaryImg }}" alt="{{ $viewing->listing->property->title }}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h4 class="font-bold text-muted dark:text-white text-lg">{{ $viewing->listing->property->title }}</h4>
                                <p class="text-sm text-muted mt-1">{{ $viewing->listing->formatted_price }} • {{ $viewing->listing->purpose }}</p>
                                <a href="{{ route('properties.show', $viewing->listing->property->id) }}" class="btn btn-outline btn-sm mt-3 inline-flex items-center gap-1 text-xs">
                                    View Listing
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </a>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-muted italic">{{ __('Property details unavailable.') }}</p>
                    @endif
                    
                    @if($viewing->notes)
                        <div class="mt-8">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-2">{{ __('Additional Notes') }}</h3>
                            <div class="p-4 bg-surface dark:bg-surface/50 rounded-xl text-sm text-muted dark:text-dark-subtle leading-relaxed border border-warm dark:border-warm">
                                {{ $viewing->notes }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Contact Info --}}
                <div class="p-6 bg-surface/50 dark:bg-surface/20">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ $isAgent ? 'Requested By' : 'Agent Contact' }}</h3>
                    
                    @php $contact = $isAgent ? $viewing->requester : $viewing->agent; @endphp
                    
                    @if($contact)
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 flex items-center justify-center font-bold text-lg border border-primary-200 dark:border-primary-500/30">
                                {{ strtoupper(substr($contact->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-muted dark:text-white">{{ $contact->name ?? 'User' }}</h4>
                                <p class="text-xs text-muted">Member since {{ $contact->created_at?->format('Y') ?? date('Y') }}</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <a href="mailto:{{ $contact->email }}" class="flex items-center justify-center gap-2 w-full btn btn-outline btn-sm font-medium">
                                <svg class="w-4 h-4 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                Email Contact
                            </a>
                            <a href="{{ route('dashboard.messages.index') }}" class="flex items-center justify-center gap-2 w-full btn btn-primary btn-sm font-medium">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                                Send Message
                            </a>
                        </div>
                    @else
                        <p class="text-sm text-muted italic">{{ __('Contact details unavailable.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
