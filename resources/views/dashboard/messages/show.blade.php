<x-layouts.dashboard>
    <x-slot name="page-title">{{ $conversation->subject }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.messages.index') }}" class="hover:text-primary-600 transition-colors">{{ __('Messages') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium truncate max-w-52 inline-block align-bottom">{{ $conversation->subject }}</span>
    </x-slot>

    <div class="max-w-5xl mx-auto h-[calc(100vh-140px)] flex flex-col bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden">
        
        {{-- Header --}}
        <div class="p-4 border-b border-warm dark:border-[#273244] bg-surface/50 dark:bg-surface/30 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard.messages.index') }}" class="btn btn-ghost btn-sm btn-icon rounded-full lg:hidden">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                
                <div class="flex flex-col">
                    <h2 class="text-base font-bold font-display text-muted dark:text-white flex items-center gap-2">
                        {{ $conversation->subject }}
                    </h2>
                    @if($conversation->property)
                        <a href="{{ route('properties.show', $conversation->property->id) }}" class="text-xs text-primary-600 hover:underline flex items-center gap-1 mt-0.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            {{ __('View Related Property') }}
                        </a>
                    @endif
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <div class="flex -space-x-2 overflow-hidden">
                    @foreach($conversation->participants as $part)
                        <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-[#1E293B] bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 flex items-center justify-center text-xs font-bold font-display shadow-sm" title="{{ $part->user->name ?? __('User') }}">
                            {{ strtoupper(substr($part->user->name ?? 'U', 0, 1)) }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Messages Area --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-6" id="messages-container" x-data="{}" x-init="$el.scrollTop = $el.scrollHeight">
            @forelse($conversation->messages->sortBy('created_at') as $msg)
                @php
                    $isMe = $msg->sender_id === auth()->id();
                @endphp
                <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                    <div class="flex items-end gap-2 max-w-[85%] {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                        {{-- Avatar --}}
                        <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold {{ $isMe ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-surface-subtle text-muted dark:bg-surface dark:text-dark-subtle' }}">
                            {{ strtoupper(substr($msg->sender->name ?? 'U', 0, 1)) }}
                        </div>

                        {{-- Bubble --}}
                        <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed {{ $isMe ? 'bg-primary-600 text-white rounded-br-sm' : 'bg-surface-subtle dark:bg-surface text-muted dark:text-muted rounded-bl-sm border border-warm dark:border-warm/50' }}">
                            {!! nl2br(e($msg->body)) !!}
                        </div>
                    </div>
                    
                    {{-- Timestamp --}}
                    <span class="text-xs text-muted mt-1.5 mx-10">
                        {{ $msg->created_at->format('M d, g:i A') }}
                    </span>
                </div>
            @empty
                <div class="flex items-center justify-center h-full text-sm text-muted">
                    {{ __('No messages yet. Send a message to start the conversation!') }}
                </div>
            @endforelse
        </div>

        {{-- Composer --}}
        <div class="p-4 bg-white dark:bg-[#1E293B] border-t border-warm dark:border-[#273244] flex-shrink-0">
            <form action="{{ route('dashboard.messages.store', $conversation) }}" method="POST" class="flex items-end gap-3" x-data="{ msg: '', loading: false }" @submit="loading = true">
                @csrf
                
                {{-- Attachment Picker --}}
                <label class="btn btn-ghost btn-icon rounded-xl flex-shrink-0 cursor-pointer text-muted hover:text-primary-500 transition-colors" title="{{ __('Attach file') }}">
                    <input type="file" class="hidden" accept="image/*,.pdf,.doc,.docx" disabled>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                </label>

                <div class="flex-1 relative">
                    <textarea name="body" x-model="msg" rows="1" required 
                              class="form-input w-full rounded-2xl py-3 px-4 pr-12 resize-none text-sm bg-surface dark:bg-[#172033] border-transparent focus:border-primary-500 focus:bg-white dark:focus:bg-[#1E293B] transition-all" 
                              placeholder="{{ __('Type a message...') }}"
                              @keydown.enter.prevent="if(!loading && msg.trim() !== '') { $el.closest('form').submit(); loading = true; }"
                              x-ref="input"
                              x-init="$watch('msg', () => { 
                                  $refs.input.style.height = 'auto'; 
                                  $refs.input.style.height = ($refs.input.scrollHeight > 150 ? 150 : $refs.input.scrollHeight) + 'px';
                              })"></textarea>
                              
                    {{-- Quick Send Button --}}
                    <button type="submit" class="absolute right-2 bottom-2 p-1.5 rounded-xl bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" :disabled="msg.trim() === '' || loading">
                        <svg class="w-4 h-4 translate-x-0.5 -translate-y-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" /></svg>
                    </button>
                </div>
            </form>
            @error('body')
                <p class="text-xs text-rose-500 mt-2 ml-14">{{ $message }}</p>
            @enderror
        </div>
    </div>
</x-layouts.dashboard>
