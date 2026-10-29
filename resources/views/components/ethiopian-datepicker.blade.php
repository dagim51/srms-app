@props([
    'name' => 'date',
    'id' => null,
    'value' => null,
    'label' => null,
    'required' => false,
    'placeholder' => 'Select date...',
    'helperText' => null,
])

@php
    $inputId = $id ?? $name;
    $initialValue = old($name, $value ?? '');
@endphp

<div x-data="ethiopianDatePicker({
        name: '{{ $name }}',
        initialDate: '{{ $initialValue }}',
        locale: '{{ app()->getLocale() }}'
    })"
    class="relative w-full text-slate-800 dark:text-slate-100"
    @click.away="isOpen = false">

    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 font-bold">*</span>
            @endif
        </label>
    @endif

    <!-- Hidden canonical input holding standard YYYY-MM-DD Gregorian for form submissions -->
    <input type="hidden" name="{{ $name }}" id="{{ $inputId }}" :value="gregorianDate" @if($required) required @endif>

    <!-- Interactive Display Field & Calendar Mode Switcher -->
    <div class="relative flex items-center rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm focus-within:ring-2 focus-within:ring-amber-500 focus-within:border-amber-500 transition-all overflow-hidden">
        
        <!-- Toggle Mode Button (ዓ.ም / G.C.) -->
        <button type="button" 
                @click="toggleMode()"
                class="px-3 py-2 text-xs font-bold transition-colors border-r border-slate-200 dark:border-slate-800 flex items-center gap-1"
                :class="calendarMode === 'ethiopian' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                title="Toggle between Ethiopian (ዓ.ም) and Gregorian (G.C.) calendar">
            <span x-text="calendarMode === 'ethiopian' ? 'ዓ.ም (Eth)' : 'G.C. (Greg)'"></span>
        </button>

        <!-- Main Display Input -->
        <input type="text"
               readonly
               @click="isOpen = !isOpen"
               :value="displayDate"
               placeholder="{{ $placeholder }}"
               class="w-full py-2 px-3 text-sm bg-transparent border-0 focus:outline-none focus:ring-0 cursor-pointer placeholder-slate-400 dark:placeholder-slate-500">

        <!-- Calendar Picker Icon Button -->
        <button type="button" 
                @click="isOpen = !isOpen"
                class="p-2 text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors focus:outline-none">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </button>
    </div>

    @if($helperText)
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $helperText }}</p>
    @endif

    <!-- Dropdown Calendar Modal -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
         x-cloak
         class="absolute z-50 mt-2 w-80 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl backdrop-blur-md">
        
        <!-- Header: Month & Year Selector & Nav -->
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
            <button type="button" @click="prevMonth()" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <div class="text-sm font-bold text-center">
                <span x-text="currentMonthName" class="text-amber-600 dark:text-amber-400"></span>
                <span x-text="currentYear" class="text-slate-900 dark:text-white ml-1"></span>
                <div class="text-[10px] text-slate-400 font-normal" x-text="dualSubtitle"></div>
            </div>

            <button type="button" @click="nextMonth()" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <!-- Days of Week Header -->
        <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold text-slate-400 dark:text-slate-500 mb-2">
            <template x-for="dayName in dayHeaders" :key="dayName">
                <div x-text="dayName" class="py-1"></div>
            </template>
        </div>

        <!-- Days Grid -->
        <div class="grid grid-cols-7 gap-1 text-center text-sm">
            <!-- Leading empty cells -->
            <template x-for="blank in leadingBlanks" :key="'blank-' + blank">
                <div class="h-8"></div>
            </template>

            <!-- Active Month Days -->
            <template x-for="d in daysInCurrentMonth" :key="'day-' + d">
                <button type="button"
                        @click="selectDay(d)"
                        class="h-8 w-8 mx-auto flex items-center justify-center rounded-lg text-xs font-medium transition-all"
                        :class="isSelected(d) ? 'bg-amber-500 text-white font-bold shadow-md shadow-amber-500/30' : (isToday(d) ? 'border border-amber-500 text-amber-600 dark:text-amber-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200')"
                        x-text="d">
                </button>
            </template>
        </div>

        <!-- Footer: Today Quick Button & Mode Indicator -->
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <button type="button" @click="selectToday()" class="text-amber-600 dark:text-amber-400 hover:underline font-semibold">
                {{ __('Today / ዛሬ') }}
            </button>
            <button type="button" @click="clearDate()" class="text-slate-400 hover:text-rose-500 transition-colors">
                {{ __('Clear / አጥፋ') }}
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('ethiopianDatePicker', (config) => ({
        name: config.name,
        locale: config.locale || 'en',
        calendarMode: 'ethiopian', // 'ethiopian' or 'gregorian'
        isOpen: false,
        gregorianDate: config.initialDate || '',
        
        // Internal viewing state
        viewYear: 2017,
        viewMonth: 2, // 1 to 13 (Meskerem to Pagume)
        selectedEth: null,

        monthsEthAm: ['መስከረም', 'ጥቅምት', 'ኅዳር', 'ታኅሣሥ', 'ጥር', 'የካቲት', 'መጋቢት', 'ሚያዝያ', 'ግንቦት', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉሜ'],
        monthsEthEn: ['Meskerem', 'Tikimt', 'Hidar', 'Tahsas', 'Tir', 'Yekatit', 'Megabit', 'Miyazya', 'Ginbot', 'Sene', 'Hamle', 'Nehase', 'Pagume'],
        monthsGregEn: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        daysAm: ['እሑ', 'ሰኞ', 'ማክ', 'ረቡ', 'ሐሙ', 'አር', 'ቅዳ'],
        daysEn: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],

        init() {
            if (this.gregorianDate) {
                const parts = this.gregorianDate.split('-').map(Number);
                if (parts.length === 3) {
                    const eth = this.gregToEth(parts[0], parts[1], parts[2]);
                    this.selectedEth = eth;
                    this.viewYear = eth.year;
                    this.viewMonth = eth.month;
                }
            } else {
                const now = new Date();
                const eth = this.gregToEth(now.getFullYear(), now.getMonth() + 1, now.getDate());
                this.viewYear = eth.year;
                this.viewMonth = eth.month;
            }
        },

        toggleMode() {
            this.calendarMode = this.calendarMode === 'ethiopian' ? 'gregorian' : 'ethiopian';
        },

        get displayDate() {
            if (!this.gregorianDate) return '';
            const parts = this.gregorianDate.split('-').map(Number);
            if (parts.length !== 3) return '';
            const eth = this.gregToEth(parts[0], parts[1], parts[2]);
            const mEth = this.monthsEthAm[eth.month - 1];
            const mGreg = this.monthsGregEn[parts[1] - 1].substring(0, 3);

            if (this.calendarMode === 'ethiopian') {
                return `${mEth} ${eth.day}፣ ${eth.year} ዓ.ም. (${mGreg} ${parts[2]}, ${parts[0]})`;
            } else {
                return `${mGreg} ${parts[2]}, ${parts[0]} (${mEth} ${eth.day}፣ ${eth.year} ዓ.ም.)`;
            }
        },

        get currentMonthName() {
            if (this.calendarMode === 'ethiopian') {
                return this.monthsEthAm[this.viewMonth - 1] || '';
            }
            return this.monthsGregEn[this.viewMonth - 1] || '';
        },

        get currentYear() {
            return this.viewYear;
        },

        get dualSubtitle() {
            if (this.calendarMode === 'ethiopian') {
                return this.monthsEthEn[this.viewMonth - 1] + ' (Eth. Calendar)';
            }
            return 'Gregorian Calendar';
        },

        get dayHeaders() {
            return this.calendarMode === 'ethiopian' ? this.daysAm : this.daysEn;
        },

        get daysInCurrentMonth() {
            if (this.calendarMode === 'ethiopian') {
                if (this.viewMonth < 13) return 30;
                // Pagume: 6 days on leap years, 5 days normally
                return (this.viewYear % 4 === 3) ? 6 : 5;
            }
            return new Date(this.viewYear, this.viewMonth, 0).getDate();
        },

        get leadingBlanks() {
            // Calculate day of week (0 = Sunday) for 1st of active month
            let dow = 0;
            if (this.calendarMode === 'ethiopian') {
                const greg = this.ethToGreg(this.viewYear, this.viewMonth, 1);
                dow = new Date(greg.year, greg.month - 1, greg.day).getDay();
            } else {
                dow = new Date(this.viewYear, this.viewMonth - 1, 1).getDay();
            }
            return Array.from({ length: dow }, (_, i) => i);
        },

        prevMonth() {
            if (this.calendarMode === 'ethiopian') {
                this.viewMonth--;
                if (this.viewMonth < 1) {
                    this.viewMonth = 13;
                    this.viewYear--;
                }
            } else {
                this.viewMonth--;
                if (this.viewMonth < 1) {
                    this.viewMonth = 12;
                    this.viewYear--;
                }
            }
        },

        nextMonth() {
            if (this.calendarMode === 'ethiopian') {
                this.viewMonth++;
                if (this.viewMonth > 13) {
                    this.viewMonth = 1;
                    this.viewYear++;
                }
            } else {
                this.viewMonth++;
                if (this.viewMonth > 12) {
                    this.viewMonth = 1;
                    this.viewYear++;
                }
            }
        },

        selectDay(day) {
            let y, m, d;
            if (this.calendarMode === 'ethiopian') {
                const greg = this.ethToGreg(this.viewYear, this.viewMonth, day);
                y = greg.year;
                m = greg.month;
                d = greg.day;
                this.selectedEth = { year: this.viewYear, month: this.viewMonth, day: day };
            } else {
                y = this.viewYear;
                m = this.viewMonth;
                d = day;
                this.selectedEth = this.gregToEth(y, m, d);
            }
            this.gregorianDate = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            this.isOpen = false;
        },

        selectToday() {
            const now = new Date();
            const y = now.getFullYear();
            const m = now.getMonth() + 1;
            const d = now.getDate();
            this.gregorianDate = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const eth = this.gregToEth(y, m, d);
            this.selectedEth = eth;
            this.viewYear = this.calendarMode === 'ethiopian' ? eth.year : y;
            this.viewMonth = this.calendarMode === 'ethiopian' ? eth.month : m;
            this.isOpen = false;
        },

        clearDate() {
            this.gregorianDate = '';
            this.selectedEth = null;
            this.isOpen = false;
        },

        isSelected(day) {
            if (!this.gregorianDate) return false;
            if (this.calendarMode === 'ethiopian') {
                return this.selectedEth && this.selectedEth.year === this.viewYear && this.selectedEth.month === this.viewMonth && this.selectedEth.day === day;
            }
            const parts = this.gregorianDate.split('-').map(Number);
            return parts[0] === this.viewYear && parts[1] === this.viewMonth && parts[2] === day;
        },

        isToday(day) {
            const now = new Date();
            if (this.calendarMode === 'ethiopian') {
                const eth = this.gregToEth(now.getFullYear(), now.getMonth() + 1, now.getDate());
                return eth.year === this.viewYear && eth.month === this.viewMonth && eth.day === day;
            }
            return now.getFullYear() === this.viewYear && (now.getMonth() + 1) === this.viewMonth && now.getDate() === day;
        },

        // Ethiopian / Gregorian conversion algorithms (Julian Day Number)
        gregToJdn(y, m, d) {
            let a = Math.floor((14 - m) / 12);
            let yr = y + 4800 - a;
            let mn = m + 12 * a - 3;
            return d + Math.floor((153 * mn + 2) / 5) + 365 * yr + Math.floor(yr / 4) - Math.floor(yr / 100) + Math.floor(yr / 400) - 32045;
        },

        jdnToGreg(jdn) {
            let a = jdn + 32044;
            let b = Math.floor((4 * a + 3) / 146097);
            let c = a - Math.floor((146097 * b) / 4);
            let d = Math.floor((4 * c + 3) / 1461);
            let e = c - Math.floor((1461 * d) / 4);
            let m = Math.floor((5 * e + 2) / 153);
            let day = e - Math.floor((153 * m + 2) / 5) + 1;
            let month = m + 3 - 12 * Math.floor(m / 10);
            let year = 100 * b + d - 4800 + Math.floor(m / 10);
            return { year, month, day };
        },

        gregToEth(y, m, d) {
            const jdn = this.gregToJdn(y, m, d);
            const EPOCH = 1723856;
            const r = (jdn - EPOCH) % 1461;
            const n = (r % 365) + 365 * Math.floor(r / 1460);
            const ethYear = 4 * Math.floor((jdn - EPOCH) / 1461) + Math.floor(r / 365) - Math.floor(r / 1460);
            const ethMonth = Math.floor(n / 30) + 1;
            const ethDay = (n % 30) + 1;
            return { year: ethYear, month: ethMonth, day: ethDay };
        },

        ethToGreg(y, m, d) {
            const EPOCH = 1723856;
            const jdn = EPOCH + 365 * y + Math.floor(y / 4) + 30 * (m - 1) + d - 1;
            return this.jdnToGreg(jdn);
        }
    }));
});
</script>
