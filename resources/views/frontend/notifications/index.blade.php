<x-layouts.app>
    <x-slot name="title">{{ __('Notifications - SRMS') }}</x-slot>

    <div class="page-container pt-32 pb-24 max-w-5xl relative z-10 page-enter">
        
        <x-page-header
            :title="__('Notifications')"
            :description="__('Stay updated on booking inquiries, offer status changes, system messages, and account events.')"
            icon="sparkles"
            badge="Activity Feed"
        >
            <x-slot:actions>
                <a href="{{ route('dashboard.preferences') }}" class="btn btn-secondary border-orange-200 text-[#C2410C] hover:bg-orange-50 dark:hover:bg-orange-950/30 inline-flex items-center gap-2 font-semibold shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span>{{ __('Preferences') }}</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300 font-medium flex items-center gap-2 border border-emerald-100 dark:border-emerald-800/50">
                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="card overflow-hidden">
            @forelse($notifications as $notification)
                <div class="p-6 sm:p-8 flex items-start gap-4 sm:gap-6 border-b border-[#EBDBC9] dark:border-[#273244] last:border-0 transition-colors {{ $notification->read_at ? 'opacity-70 bg-surface/50 dark:bg-surface/30' : 'bg-white dark:bg-[#111827]' }}">
                    
                    {{-- Icon indicator based on notification type --}}
                    <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 shadow-sm border {{ $notification->read_at ? 'bg-surface-subtle dark:bg-surface border-warm dark:border-warm text-muted' : 'bg-primary-50 dark:bg-primary-900/30 border-primary-200 dark:border-primary-800/50 text-primary-600 dark:text-primary-400' }}">
                        @if(str_contains($notification->type, 'Message'))
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                        @elseif(str_contains($notification->type, 'Booking'))
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @endif
                    </div>

                    <div class="flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1">
                            <h3 class="text-lg font-bold text-muted dark:text-white leading-tight">
                                {{ $notification->data['title'] ?? 'Notification' }}
                                @if(!$notification->read_at)
                                    <span class="inline-block w-2 h-2 rounded-full bg-rose-500 ml-2 animate-pulse"></span>
                                @endif
                            </h3>
                            <span class="text-xs font-medium text-muted shrink-0 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>
                        
                        <p class="text-muted dark:text-dark-subtle mb-4">{{ $notification->data['message'] ?? '' }}</p>
                        
                        <div class="flex items-center gap-3">
                            @if(isset($notification->data['action_url']))
                                <a href="{{ $notification->data['action_url'] }}" class="text-sm font-bold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">View Details</a>
                            @endif
                            
                            @if(!$notification->read_at)
                                @if(isset($notification->data['action_url']))
                                    <span class="text-muted dark:text-muted">&bull;</span>
                                @endif
                                <form action="{{ route('dashboard.notifications.read', $notification->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white transition-colors">{{ __('Mark as read') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-20 px-4">
                    <div class="w-20 h-20 mx-auto bg-surface dark:bg-surface rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-muted dark:text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-muted dark:text-white">You're all caught up!</h3>
                    <p class="text-muted dark:text-muted mt-2 max-w-sm mx-auto">{{ __('You don\'t have any new notifications at the moment. We\'ll let you know when something comes up.') }}</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="mt-8">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
