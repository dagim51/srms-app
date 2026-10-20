<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Setup Accommodation') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.accommodations.index') }}" class="hover:text-primary-600 transition-colors">{{ __('Hosting') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('New') }}</span>
    </x-slot>

    <div class="max-w-3xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Setup New Accommodation"
            description="Convert one of your properties into a bookable short-term stay with customized night rates and guest policies."
            icon="key"
            badge="Accommodations" />

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden p-6 sm:p-8">

            @if($properties->isEmpty())
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-medium">
                    {{ __("You don't have any available properties to set up as accommodations. You must first create a property listing.") }}
                </div>
            @else
                <form action="{{ route('dashboard.accommodations.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Select Property') }}</label>
                        <select name="property_id" required class="form-input w-full rounded-xl">
                            <option value="">{{ __('-- Choose a property --') }}</option>
                            @foreach($properties as $prop)
                                <option value="{{ $prop->id }}" {{ old('property_id') == $prop->id ? 'selected' : '' }}>
                                    {{ $prop->title }} ({{ $prop->property_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('property_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Max Guests') }}</label>
                            <input type="number" name="max_guests" required min="1" value="{{ old('max_guests', 2) }}" class="form-input w-full rounded-xl text-center font-bold">
                            @error('max_guests') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Bedrooms') }}</label>
                            <input type="number" name="bedrooms" required min="0" value="{{ old('bedrooms', 1) }}" class="form-input w-full rounded-xl text-center font-bold">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Beds') }}</label>
                            <input type="number" name="beds" required min="1" value="{{ old('beds', 1) }}" class="form-input w-full rounded-xl text-center font-bold">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Bathrooms') }}</label>
                            <input type="number" name="bathrooms" required min="0" step="0.5" value="{{ old('bathrooms', 1.0) }}" class="form-input w-full rounded-xl text-center font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Check-in Time') }}</label>
                            <input type="time" name="check_in_time" required value="{{ old('check_in_time', '15:00') }}" class="form-input w-full rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('Check-out Time') }}</label>
                            <input type="time" name="check_out_time" required value="{{ old('check_out_time', '11:00') }}" class="form-input w-full rounded-xl">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">{{ __('House Rules') }}</label>
                        <textarea name="rules" rows="4" class="form-input w-full rounded-xl resize-none" placeholder="{{ __('E.g., No smoking, no pets, quiet hours after 10 PM.') }}">{{ old('rules', "1. No smoking inside.\n2. No parties or events.\n3. Respect quiet hours (10 PM - 7 AM).") }}</textarea>
                        @error('rules') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-6 mt-6 border-t border-warm dark:border-warm flex items-center gap-4 justify-end">
                        <a href="{{ route('dashboard.accommodations.index') }}" class="btn btn-ghost font-bold">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary font-bold px-8 shadow-md">
                            {{ __('Setup Accommodation') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
