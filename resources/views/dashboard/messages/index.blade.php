<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Inbox & Messages') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('Messages') }}</span>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden flex flex-col min-h-0">
            {{-- Header --}}
            <div class="p-4 border-b border-warm dark:border-[#273244] flex items-center justify-between bg-surface/50 dark:bg-surface/30">
                <h2 class="text-lg font-bold font-display text-muted dark:text-white">{{ __('Recent Conversations') }}</h2>
                <span class="badge badge-primary px-2.5 py-1 text-xs">
                    {{ $conversations->count() }} {{ __('Active') }}
                </span>
            </div>

            {{-- Conversations List --}}
            <div class="divide-y divide-warm dark:divide-[#273244] flex-1">
                @forelse($conversations as $conv)
                    @php
                        $participant = $conv->participants->where('user_id', auth()->id())->first();
                        $lastMessage = $conv->messages->first();
                        $isUnread = $lastMessage && (!$participant->last_read_at || $lastMessage->created_at > $participant->last_read_at);
                    @endphp
                    
                    <a href="{{ route('dashboard.messages.show', $conv->id) }}" 
                       class="block p-4 hover:bg-surface dark:hover:bg-[#1A2333] transition-colors relative {{ $isUnread ? 'bg-orange-50/30 dark:bg-orange-950/10' : '' }}">
                        
                        @if($isUnread)
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary-500 rounded-r-full"></div>
                        @endif

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-500/20 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-lg flex-shrink-0 shadow-sm border border-orange-200 dark:border-orange-500/30">
                                {{ strtoupper(substr($conv->subject, 0, 1)) }}
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="text-sm font-bold text-muted dark:text-white truncate pr-4 {{ $isUnread ? 'text-primary-600 dark:text-primary-400' : '' }}">
                                        {{ $conv->subject }}
                                    </h3>
                                    <span class="text-xs font-medium text-muted whitespace-nowrap">
                                        {{ $lastMessage ? $lastMessage->created_at->diffForHumans() : $conv->created_at->format('M d') }}
                                    </span>
                                </div>
                                
                                <p class="text-xs text-muted dark:text-muted truncate pr-8">
                                    @if($lastMessage)
                                        <span class="font-medium text-muted dark:text-dark-subtle">{{ $lastMessage->sender_id === auth()->id() ? __('You') . ': ' : '' }}</span>
                                        {{ Str::limit($lastMessage->body, 80) }}
                                    @else
                                        <span class="italic">{{ __('No messages yet.') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center p-12 text-center h-full">
                        <div class="w-16 h-16 bg-surface-subtle dark:bg-surface rounded-2xl flex items-center justify-center mb-4 text-muted">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>
                        </div>
                        <h3 class="text-base font-bold text-muted dark:text-white mb-1">{{ __('No Messages Yet') }}</h3>
                        <p class="text-xs text-muted max-w-sm">{{ __('When you inquire about a property or someone contacts you, the conversation will appear here.') }}</p>
                        <a href="{{ route('properties.index') }}" class="btn btn-outline btn-sm mt-6">{{ __('Explore Properties') }}</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.dashboard>
