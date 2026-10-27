@props([
    'property' => null,
    'existingMedia' => null,
    'name' => 'photos',
])

@php
    $propertyId = $property ? (string) $property->id : null;
    $mediaItems = $existingMedia ?? ($property ? $property->media : collect());

    $initialItems = $mediaItems->map(function ($m, $idx) {
        return [
            'id' => (string) $m->id,
            'url' => $m->url,
            'kind' => $m->kind ?? 'image',
            'mime_type' => $m->mime_type ?? 'image/jpeg',
            'width' => $m->width,
            'height' => $m->height,
            'sort_order' => $m->sort_order ?? $idx,
            'is_primary' => ($m->sort_order === 0) || ($idx === 0),
            'status' => 'uploaded',
            'progress' => 100,
            'name' => basename($m->storage_key ?? 'Media ' . ($idx + 1)),
        ];
    })->values();
@endphp

<div x-data="{
    propertyId: {{ Js::from($propertyId) }},
    items: {{ Js::from($initialItems) }},
    isDragging: false,
    uploadingCount: 0,
    previewModalOpen: false,
    previewItem: null,
    previewIndex: 0,
    errorMessage: '',

    handleDrop(e) {
        this.isDragging = false;
        const files = Array.from(e.dataTransfer.files);
        if (!this.propertyId && this.$refs.fileInput) {
            try {
                this.$refs.fileInput.files = e.dataTransfer.files;
            } catch (err) {
                // Browser security fallback
            }
        }
        this.processFiles(files);
    },

    handleFileSelect(e) {
        const files = Array.from(e.target.files);
        this.processFiles(files);
        if (this.propertyId) {
            e.target.value = '';
        }
    },

    processFiles(files) {
        this.errorMessage = '';
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'video/mp4', 'video/quicktime', 'application/pdf'];
        const maxSize = 20 * 1024 * 1024; // 20MB

        files.forEach(file => {
            if (!allowedTypes.includes(file.type)) {
                this.errorMessage = 'Unsupported file format: ' + file.name + '. Allowed: JPG, PNG, WEBP, MP4, PDF.';
                return;
            }

            if (file.size > maxSize) {
                this.errorMessage = 'File too large: ' + file.name + ' (Maximum 20MB allowed).';
                return;
            }

            const tempId = 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
            const objectUrl = file.type.startsWith('image/') ? URL.createObjectURL(file) : '';
            const kind = file.type.startsWith('video/') ? 'video' : (file.type === 'application/pdf' ? 'document' : 'image');

            const item = {
                id: tempId,
                file: file,
                url: objectUrl,
                kind: kind,
                mime_type: file.type,
                name: file.name,
                size: (file.size / (1024 * 1024)).toFixed(1) + ' MB',
                sort_order: this.items.length,
                is_primary: this.items.length === 0,
                status: this.propertyId ? 'uploading' : 'queued',
                progress: this.propertyId ? 0 : 100,
                error: null
            };

            this.items.push(item);

            if (this.propertyId) {
                this.uploadFile(item);
            }
        });
    },

    uploadFile(item) {
        this.uploadingCount++;
        const formData = new FormData();
        formData.append('file', item.file);

        const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/dashboard/properties/' + this.propertyId + '/media', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                item.progress = Math.round((e.loaded / e.total) * 100);
            }
        };

        xhr.onload = () => {
            this.uploadingCount--;
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    const media = response.media || response;
                    item.id = media.id;
                    item.url = media.url || item.url;
                    item.status = 'uploaded';
                    item.progress = 100;
                    item.sort_order = media.sort_order;
                    item.width = media.width;
                    item.height = media.height;
                } catch(err) {
                    item.status = 'uploaded';
                }
            } else {
                item.status = 'failed';
                item.error = 'Upload failed with status ' + xhr.status;
            }
        };

        xhr.onerror = () => {
            this.uploadingCount--;
            item.status = 'failed';
            item.error = 'Network error during upload.';
        };

        xhr.send(formData);
    },

    retryUpload(item) {
        if (!item.file || !this.propertyId) return;
        item.status = 'uploading';
        item.progress = 0;
        item.error = null;
        this.uploadFile(item);
    },

    setPrimary(index) {
        if (index === 0) return;
        const target = this.items[index];
        this.items.splice(index, 1);
        this.items.unshift(target);
        this.reindexSortOrder();

        if (this.propertyId && target.status === 'uploaded') {
            const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
            fetch('/dashboard/properties/' + this.propertyId + '/media/' + target.id + '/set-primary', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
        }
    },

    moveItem(fromIndex, toIndex) {
        if (toIndex < 0 || toIndex >= this.items.length) return;
        const item = this.items.splice(fromIndex, 1)[0];
        this.items.splice(toIndex, 0, item);
        this.reindexSortOrder();
        this.syncOrderToServer();
    },

    removeItem(index) {
        const item = this.items[index];
        const isServerItem = this.propertyId && item.status === 'uploaded';

        if (isServerItem) {
            const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
            fetch('/dashboard/properties/' + this.propertyId + '/media/' + item.id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
        }

        this.items.splice(index, 1);
        this.reindexSortOrder();
    },

    reindexSortOrder() {
        this.items.forEach((item, idx) => {
            item.sort_order = idx;
            item.is_primary = idx === 0;
        });
    },

    syncOrderToServer() {
        if (!this.propertyId) return;
        const uploadedIds = this.items.filter(i => i.status === 'uploaded').map(i => i.id);
        if (uploadedIds.length === 0) return;

        const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
        fetch('/dashboard/properties/' + this.propertyId + '/media/order', {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ order: uploadedIds })
        });
    },

    openPreview(index) {
        this.previewIndex = index;
        this.previewItem = this.items[index];
        this.previewModalOpen = true;
    },

    nextPreview() {
        if (this.previewIndex < this.items.length - 1) {
            this.previewIndex++;
            this.previewItem = this.items[this.previewIndex];
        }
    },

    prevPreview() {
        if (this.previewIndex > 0) {
            this.previewIndex--;
            this.previewItem = this.items[this.previewIndex];
        }
    }
}" class="space-y-5 font-sans">

    {{-- Error Banner --}}
    <div x-show="errorMessage" class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 p-3 rounded-xl flex items-center justify-between text-xs text-red-700 dark:text-red-300" x-cloak>
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span x-text="errorMessage"></span>
        </div>
        <button type="button" @click="errorMessage = ''" class="text-red-500 hover:text-red-700">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    {{-- Drag & Drop Upload Zone --}}
    <div 
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop($event)"
        class="relative border-2 border-dashed rounded-2xl p-6 sm:p-8 text-center transition-all duration-200 cursor-pointer group"
        :class="isDragging 
            ? 'border-orange-500 bg-orange-50/50 dark:bg-orange-950/20 ring-4 ring-orange-500/10' 
            : 'border-warm dark:border-[#273244] hover:border-orange-400 dark:hover:border-orange-500/50 bg-surface/60 dark:bg-[#111827]/40 hover:bg-surface dark:hover:bg-[#172033]/40'"
        @click="$refs.fileInput.click()"
    >
        <input 
            type="file" 
            x-ref="fileInput" 
            name="{{ $name }}[]"
            multiple 
            accept="image/jpeg,image/png,image/webp,video/mp4,application/pdf"
            @change="handleFileSelect($event)" 
            class="hidden" 
        >

        <div class="max-w-md mx-auto space-y-3 pointer-events-none">
            <div class="w-14 h-14 rounded-2xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center mx-auto transition-transform group-hover:scale-105 shadow-sm shadow-orange-500/20">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
            </div>

            <div>
                <p class="text-sm font-semibold text-muted dark:text-white">
                    Drop images, videos, or floor plans here, or <span class="text-orange-600 dark:text-orange-400 underline decoration-2">{{ __('browse files') }}</span>
                </p>
                <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Supports JPG, PNG, WEBP, MP4, and PDF documents up to 20MB each.') }}</p>
            </div>

            <div class="flex items-center justify-center gap-4 pt-1 text-xs text-muted dark:text-muted font-medium">
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    High-Res Photos
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    Floor Plans (PDF)
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    Video Walkthroughs
                </span>
            </div>
        </div>
    </div>

    {{-- Gallery Stats & Controls Header --}}
    <div class="flex items-center justify-between pt-1" x-show="items.length > 0" x-cloak>
        <div class="flex items-center gap-2">
            <h4 class="font-display font-semibold text-sm text-muted dark:text-white">
                Uploaded Media (<span x-text="items.length"></span>)
            </h4>
            <span class="text-xs text-muted dark:text-muted">{{ __('First photo is used as primary cover') }}</span>
        </div>

        <div class="flex items-center gap-2">
            <span x-show="uploadingCount > 0" class="inline-flex items-center gap-1.5 text-xs text-orange-600 dark:text-orange-400 font-medium" x-cloak>
                <svg class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                Uploading (<span x-text="uploadingCount"></span>)...
            </span>
        </div>
    </div>

    {{-- Media Grid Gallery --}}
    <div x-show="items.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" x-cloak>
        <template x-for="(item, index) in items" :key="item.id">
            <div 
                class="card group relative bg-white dark:bg-[#111827] rounded-xl border overflow-hidden transition-all duration-200"
                :class="item.is_primary 
                    ? 'border-orange-500 ring-2 ring-orange-500/20' 
                    : 'border-warm dark:border-[#273244] hover:border-warm dark:hover:border-warm'"
            >
                {{-- Media Thumbnail Viewport --}}
                <div class="relative aspect-4/3 bg-surface-subtle dark:bg-surface overflow-hidden cursor-pointer" @click="openPreview(index)">
                    {{-- Image Thumbnail --}}
                    <template x-if="item.kind === 'image' && item.url">
                        <img :src="item.url" :alt="item.name" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    </template>

                    {{-- Video Thumbnail --}}
                    <template x-if="item.kind === 'video'">
                        <div class="w-full h-full flex flex-col items-center justify-center bg-surface text-white p-3">
                            <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-xs flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                            </div>
                            <span class="text-xs text-muted truncate max-w-full" x-text="item.name"></span>
                        </div>
                    </template>

                    {{-- Document Thumbnail --}}
                    <template x-if="item.kind === 'document'">
                        <div class="w-full h-full flex flex-col items-center justify-center bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 p-3">
                            <svg class="w-10 h-10 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span class="text-xs font-semibold truncate max-w-full" x-text="item.name"></span>
                            <span class="text-xs text-muted">{{ __('PDF Document') }}</span>
                        </div>
                    </template>

                    {{-- Upload Progress Overlay --}}
                    <div x-show="item.status === 'uploading'" class="absolute inset-0 bg-black/60 backdrop-blur-xs flex flex-col items-center justify-center p-4 text-white">
                        <div class="w-full bg-surface rounded-full h-1.5 overflow-hidden mb-2">
                            <div class="bg-orange-500 h-1.5 transition-all duration-150" :style="'width: ' + item.progress + '%'"></div>
                        </div>
                        <span class="text-xs font-semibold" x-text="item.progress + '%'"></span>
                    </div>

                    {{-- Error Overlay --}}
                    <div x-show="item.status === 'failed'" class="absolute inset-0 bg-red-900/80 backdrop-blur-xs flex flex-col items-center justify-center p-3 text-white text-center">
                        <svg class="w-6 h-6 text-red-200 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <span class="text-xs leading-tight mb-2" x-text="item.error || 'Failed'"></span>
                        <button type="button" @click.stop="retryUpload(item)" class="text-xs bg-white text-red-700 font-semibold px-2 py-1 rounded cursor-pointer hover:bg-surface-subtle">{{ __('Retry') }}</button>
                    </div>

                    {{-- Primary Cover Photo Badge --}}
                    <div x-show="item.is_primary" class="absolute top-2 left-2 z-10" x-cloak>
                        <span class="inline-flex items-center gap-1 bg-orange-600 text-white font-semibold text-xs px-2 py-0.5 rounded-full shadow-md shadow-orange-600/30">
                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            Cover Photo
                        </span>
                    </div>

                    {{-- Sort Order Pill --}}
                    <div class="absolute bottom-2 left-2 z-10 bg-black/60 backdrop-blur-xs text-white text-xs font-mono px-1.5 py-0.5 rounded">
                        #<span x-text="index + 1"></span>
                    </div>

                    {{-- Quick Zoom / Lightbox Trigger --}}
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                        <span class="inline-flex items-center gap-1 bg-white/90 dark:bg-black/80 text-muted dark:text-white px-2.5 py-1 rounded-full text-xs font-medium shadow-md">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" /></svg>
                            Preview
                        </span>
                    </div>
                </div>

                {{-- Action Bar under thumbnail --}}
                <div class="p-2.5 flex items-center justify-between bg-white dark:bg-[#111827] border-t border-warm dark:border-[#273244] text-xs">
                    {{-- Set Primary Button --}}
                    <div>
                        <button 
                            type="button" 
                            x-show="!item.is_primary && item.kind === 'image'" 
                            @click="setPrimary(index)"
                            class="text-xs font-medium text-muted hover:text-orange-600 dark:hover:text-orange-400 transition-colors flex items-center gap-1 cursor-pointer"
                            title="Set as cover photo"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                            Make cover
                        </button>
                    </div>

                    {{-- Reorder & Delete Buttons --}}
                    <div class="flex items-center gap-1">
                        {{-- Move Left --}}
                        <button 
                            type="button" 
                            @click="moveItem(index, index - 1)" 
                            :disabled="index === 0" 
                            class="p-1 rounded text-muted hover:text-muted dark:hover:text-muted disabled:opacity-30 disabled:pointer-events-none hover:bg-surface-subtle dark:hover:bg-surface transition-colors cursor-pointer"
                            title="Move left"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                        </button>

                        {{-- Move Right --}}
                        <button 
                            type="button" 
                            @click="moveItem(index, index + 1)" 
                            :disabled="index === items.length - 1" 
                            class="p-1 rounded text-muted hover:text-muted dark:hover:text-muted disabled:opacity-30 disabled:pointer-events-none hover:bg-surface-subtle dark:hover:bg-surface transition-colors cursor-pointer"
                            title="Move right"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </button>

                        {{-- Delete --}}
                        <button 
                            type="button" 
                            @click="removeItem(index)" 
                            class="p-1 rounded text-muted hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors cursor-pointer ml-1"
                            title="Delete file"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- ═══ LIGHTBOX / FULL PREVIEW MODAL ═══ --}}
    <div 
        x-show="previewModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        x-cloak
    >
        <div class="fixed inset-0 bg-black/90 backdrop-blur-md transition-opacity" @click="previewModalOpen = false"></div>

        <div class="relative max-w-4xl w-full bg-[#111827] rounded-2xl overflow-hidden shadow-2xl border border-warm z-10 flex flex-col max-h-full" @click.stop>
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-warm text-white">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-mono text-muted">#<span x-text="previewIndex + 1"></span> of <span x-text="items.length"></span></span>
                    <h3 class="text-sm font-semibold truncate max-w-md" x-text="previewItem?.name"></h3>
                    <span x-show="previewItem?.is_primary" class="badge badge-brand text-xs bg-orange-500/20 text-orange-400">{{ __('Cover') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="previewItem?.url" target="_blank" download class="p-1.5 text-muted hover:text-white rounded-lg transition-colors" title="Download original">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    </a>
                    <button type="button" @click="previewModalOpen = false" class="p-1.5 text-muted hover:text-white rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            {{-- Modal Body Media Display --}}
            <div class="flex-1 overflow-auto flex items-center justify-center p-4 bg-black/60 relative">
                <template x-if="previewItem?.kind === 'image'">
                    <img :src="previewItem?.url" :alt="previewItem?.name" class="max-h-full max-w-full object-contain rounded-lg shadow-lg">
                </template>

                <template x-if="previewItem?.kind === 'video'">
                    <video :src="previewItem?.url" controls class="max-h-full max-w-full rounded-lg shadow-lg"></video>
                </template>

                <template x-if="previewItem?.kind === 'document'">
                    <iframe :src="previewItem?.url" class="w-full h-full rounded-lg border border-warm"></iframe>
                </template>

                {{-- Previous Button --}}
                <button 
                    type="button" 
                    @click="prevPreview()" 
                    x-show="previewIndex > 0" 
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/50 hover:bg-black/80 text-white flex items-center justify-center backdrop-blur-xs transition-colors cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                </button>

                {{-- Next Button --}}
                <button 
                    type="button" 
                    @click="nextPreview()" 
                    x-show="previewIndex < items.length - 1" 
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/50 hover:bg-black/80 text-white flex items-center justify-center backdrop-blur-xs transition-colors cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </button>
            </div>

            {{-- Modal Footer --}}
            <div class="px-5 py-3 bg-[#0B0F19] border-t border-warm flex items-center justify-between text-xs text-muted">
                <div class="flex items-center gap-4">
                    <span x-show="previewItem?.width && previewItem?.height" x-text="previewItem?.width + ' × ' + previewItem?.height + ' px'"></span>
                    <span class="capitalize" x-text="previewItem?.kind"></span>
                    <span x-text="previewItem?.mime_type"></span>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        x-show="!previewItem?.is_primary && previewItem?.kind === 'image'" 
                        @click="setPrimary(previewIndex); previewModalOpen = false"
                        class="btn btn-outline text-xs px-3 py-1 cursor-pointer"
                    >{{ __('Make Cover Photo') }}</button>
                </div>
            </div>
        </div>
    </div>

</div>
