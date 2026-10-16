<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Edit Accommodation Specs') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.accommodations.index') }}" class="hover:text-primary-600 transition-colors">{{ __('Hosting') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.accommodations.show', $accommodation) }}" class="hover:text-primary-600 transition-colors">{{ __('Manage') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('Edit') }}</span>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden p-6 sm:p-8">
            <div class="mb-8 border-b border-warm dark:border-warm pb-6">
                <h2 class="text-xl font-bold font-display text-muted dark:text-white mb-2">{{ __('Edit Accommodation Specs') }}</h2>
                <p class="text-muted text-sm">{{ __('Update room configurations and house rules for') }} {{ $accommodation->property->title }}.</p>
            </div>

            <form action="{{ route('dashboard.accommodations.update', $accommodation) }}" method="POST" class="space-y-6">
                @csrf @method('PUT')

                <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('Room & Unit Configuration') }}</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Max Guests') }}</label>
                        <input type="number" name="max_guests" required min="1" value="{{ old('max_guests', $accommodation->max_guests) }}" class="form-input w-full rounded-xl text-center font-bold">
                        @error('max_guests') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Bedrooms') }}</label>
                        <input type="number" name="bedrooms" required min="0" value="{{ old('bedrooms', $accommodation->bedrooms) }}" class="form-input w-full rounded-xl text-center font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Beds') }}</label>
                        <input type="number" name="beds" required min="1" value="{{ old('beds', $accommodation->beds) }}" class="form-input w-full rounded-xl text-center font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Bathrooms') }}</label>
                        <input type="number" name="bathrooms" required min="0" step="0.5" value="{{ old('bathrooms', $accommodation->bathrooms) }}" class="form-input w-full rounded-xl text-center font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-warm dark:border-warm">
                    <div>
                        <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Check-in Time') }}</label>
                        <input type="time" name="check_in_time" required value="{{ old('check_in_time', \Carbon\Carbon::parse($accommodation->check_in_time)->format('H:i')) }}" class="form-input w-full rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Check-out Time') }}</label>
                        <input type="time" name="check_out_time" required value="{{ old('check_out_time', \Carbon\Carbon::parse($accommodation->check_out_time)->format('H:i')) }}" class="form-input w-full rounded-xl">
                    </div>
                </div>

                <div class="pt-4 border-t border-warm dark:border-warm">
                    <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('House Rules') }}</label>
                    <textarea name="rules" rows="4" class="form-input w-full rounded-xl resize-none" placeholder="{{ __('E.g., No smoking, no pets, quiet hours after 10 PM.') }}">{{ old('rules', $accommodation->rules) }}</textarea>
                    @error('rules') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-6 mt-6 border-t border-warm dark:border-warm flex items-center gap-4 justify-end">
                    <a href="{{ route('dashboard.accommodations.show', $accommodation) }}" class="btn btn-ghost font-bold">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary font-bold px-8 shadow-md">
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
