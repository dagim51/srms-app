<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Inbox & Messages') }}</x-slot>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => route('dashboard')],
            ['label' => __('Messages')],
        ]" />
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-page-header
            title="Inquiries & Messaging"
            description="Direct communication channel between verified buyers, agents, owners, and developers with real-time audit logs."
            icon="chat"
            badge="Communications" />

        <div class="cinematic-fade-up cinematic-stagger-2 card overflow-hidden flex flex-col min-h-0">
            {{-- Header --}}
            <div class="p-4 border-b border-warm dark:border-[#273244] flex items-center justify-between bg-surface/50 dark:bg-surface/30">
                <h2 class="text-lg font-bold font-display text-muted dark:text-white">{{ __('Recent Conversations') }}</h2>
                <x-badge variant="brand" size="xs" dot>
                    {{ $conversations->count() }} {{ __('Active') }}
                </x-badge>
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
                    <x-empty-state
                        icon="chat"
                        :title="__('No Messages Yet')"
                        :description="__('When you inquire about a property or someone contacts you, the conversation will appear here.')"
                        size="default">
                        <x-slot:actions>
                            <a href="{{ route('properties.index') }}" class="btn btn-outline btn-sm">{{ __('Explore Properties') }}</a>
                        </x-slot:actions>
                    </x-empty-state>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.dashboard>
