<x-layouts.dashboard>
    <x-slot name="page-title">{{ $conversation->subject }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.messages.index') }}" class="hover:text-primary-600 transition-colors">{{ __('Messages') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium truncate max-w-52 inline-block align-bottom">{{ $conversation->subject }}</span>
    </x-slot>

    <div class="max-w-5xl mx-auto page-enter space-y-4">
        <x-page-header
            :title="$conversation->subject"
            description="Direct message thread between verified buyers, agents, owners, and developers with real-time audit logs."
            icon="chat"
            badge="Messages" />

        <div class="card h-[calc(100vh-280px)] min-h-[500px] flex flex-col bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl overflow-hidden">
        
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
        <div class="flex-1 overflow-y-auto p-4 space-y-6" id="messages-container" x-data="{ reportModalOpen: false, reportedMessageId: null, reportReason: 'offensive_content', reportDesc: '' }" x-init="$el.scrollTop = $el.scrollHeight;
            if (window.Echo) {
                window.Echo.private('chat.{{ $conversation->id }}')
                    .listen('.message.sent', (e) => {
                        window.location.reload();
                    });
            }">
            @forelse($conversation->messages->sortBy('created_at') as $msg)
                @php
                    $isMe = $msg->sender_id === auth()->id();
                @endphp
                <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}" id="message-{{ $msg->id }}">
                    <div class="flex items-end gap-2 max-w-[85%] {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                        {{-- Avatar --}}
                        <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold {{ $isMe ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-surface-subtle text-muted dark:bg-surface dark:text-dark-subtle' }}">
                            {{ strtoupper(substr($msg->sender->name ?? 'U', 0, 1)) }}
                        </div>

                        {{-- Bubble --}}
                        <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed {{ $isMe ? 'bg-primary-600 text-white rounded-br-sm' : 'bg-surface-subtle dark:bg-surface text-muted dark:text-muted rounded-bl-sm border border-warm dark:border-warm/50' }}">
                            @if($msg->body)
                                <p>{!! nl2br(e($msg->body)) !!}</p>
                            @endif

                            {{-- Attachments --}}
                            @if($msg->attachments && $msg->attachments->isNotEmpty())
                                <div class="mt-2 space-y-1.5 border-t border-white/20 dark:border-warm pt-2">
                                    @foreach($msg->attachments as $att)
                                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="flex items-center gap-2 text-xs font-medium hover:underline {{ $isMe ? 'text-white' : 'text-primary-600' }}">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                            <span class="truncate max-w-[200px]">{{ $att->file_name ?? __('View Attachment') }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Report Flag for incoming messages --}}
                        @if(!$isMe)
                            <button type="button" @click="reportModalOpen = true; reportedMessageId = '{{ $msg->id }}'" class="text-xs text-slate-400 hover:text-rose-500 opacity-60 hover:opacity-100 transition-opacity p-1" title="{{ __('Report offensive message') }}">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
                            </button>
                        @endif
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

            {{-- Report Offensive Content Modal --}}
            <div x-show="reportModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="card bg-white dark:bg-[#1E293B] border border-warm rounded-2xl max-w-md w-full p-6 space-y-4" @click.away="reportModalOpen = false">
                    <h3 class="text-lg font-bold font-display text-muted dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        {{ __('Report Message') }}
                    </h3>
                    <p class="text-xs text-muted">{{ __('Please report offensive, abusive, fraudulent, or harassing messages for moderator review.') }}</p>

                    <form :action="'/dashboard/messages/' + reportedMessageId + '/report'" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="card-kv-label">{{ __('Reason') }}</label>
                            <select name="reason" x-model="reportReason" class="form-input w-full text-sm mt-1">
                                <option value="offensive_content">{{ __('Offensive / Abusive Language') }}</option>
                                <option value="fraud_scam">{{ __('Suspected Fraud or Scam') }}</option>
                                <option value="harassment">{{ __('Harassment or Stalking') }}</option>
                                <option value="spam">{{ __('Spam / Solicitation') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="card-kv-label">{{ __('Details (optional)') }}</label>
                            <textarea name="description" x-model="reportDesc" rows="2" class="form-input w-full text-sm mt-1" placeholder="{{ __('Describe the issue...') }}"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="reportModalOpen = false" class="btn btn-secondary btn-sm">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary btn-sm bg-rose-600 hover:bg-rose-700 text-white">{{ __('Submit Report') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Composer --}}
        <div class="p-4 bg-white dark:bg-[#1E293B] border-t border-warm dark:border-[#273244] flex-shrink-0" x-data="{ msg: '', loading: false, attachmentName: '' }">
            <template x-if="attachmentName">
                <div class="flex items-center gap-2 mb-2 px-3 py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 rounded-lg text-xs w-max">
                    <span x-text="attachmentName"></span>
                    <button type="button" @click="attachmentName = ''; $refs.fileInput.value = ''" class="hover:text-rose-500 font-bold">&times;</button>
                </div>
            </template>

            <form action="{{ route('dashboard.messages.store', $conversation) }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-3" @submit="loading = true">
                @csrf
                
                {{-- Attachment Picker --}}
                <label class="btn btn-ghost btn-icon rounded-xl flex-shrink-0 cursor-pointer text-muted hover:text-primary-500 transition-colors" title="{{ __('Attach file') }}">
                    <input type="file" name="attachment" x-ref="fileInput" class="hidden" accept="image/*,.pdf,.doc,.docx" @change="attachmentName = $event.target.files[0]?.name || ''">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                </label>

                <div class="flex-1 relative">
                    <textarea name="body" x-model="msg" rows="1" 
                              class="form-input w-full rounded-2xl py-3 px-4 pr-12 resize-none text-sm bg-surface dark:bg-[#172033] border-transparent focus:border-primary-500 focus:bg-white dark:focus:bg-[#1E293B] transition-all" 
                              placeholder="{{ __('Type a message...') }}"
                              @keydown.enter.prevent="if(!loading && (msg.trim() !== '' || attachmentName !== '')) { $el.closest('form').submit(); loading = true; }"
                              x-ref="input"
                              x-init="$watch('msg', () => { 
                                  $refs.input.style.height = 'auto'; 
                                  $refs.input.style.height = ($refs.input.scrollHeight > 150 ? 150 : $refs.input.scrollHeight) + 'px';
                              })"></textarea>
                              
                    {{-- Quick Send Button --}}
                    <button type="submit" class="absolute right-2 bottom-2 p-1.5 rounded-xl bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" :disabled="(msg.trim() === '' && attachmentName === '') || loading">
                        <svg class="w-4 h-4 translate-x-0.5 -translate-y-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" /></svg>
                    </button>
                </div>
            </form>
            @error('body')
                <p class="text-xs text-rose-500 mt-2 ml-14">{{ $message }}</p>
            @enderror
            @error('attachment')
                <p class="text-xs text-rose-500 mt-2 ml-14">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
</x-layouts.dashboard>
