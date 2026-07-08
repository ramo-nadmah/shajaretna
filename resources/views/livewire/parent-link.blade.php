<div class="max-w-lg mx-auto">
    <div class="mb-8">
        <a href="{{ route('people.index') }}" class="text-muted text-sm hover:text-parchment transition-colors flex items-center gap-1.5 mb-4 w-fit">
            <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            العودة للقائمة
        </a>
        <h1 class="text-2xl font-semibold text-parchment">ربط الوالدين</h1>
        <p class="text-gold text-base mt-1 font-medium">{{ $person->name_ar }}</p>
    </div>

    <form wire:submit="save" class="card space-y-6">

        {{-- Father --}}
        <div>
            <label class="block text-sm text-muted mb-2 flex items-center gap-2">
                <span class="text-jade-bright">♂</span> الأب
            </label>
            <select wire:model="fatherId" class="select-field">
                <option value="">— لا يوجد / غير معروف —</option>
                @foreach ($fathers as $father)
                    <option value="{{ $father->id }}">{{ $father->name_ar }}</option>
                @endforeach
            </select>
        </div>

        {{-- Mother --}}
        <div>
            <label class="block text-sm text-muted mb-2 flex items-center gap-2">
                <span class="text-pink-400">♀</span> الأم
            </label>
            <select wire:model="motherId" class="select-field">
                <option value="">— لا توجد / غير معروفة —</option>
                @foreach ($mothers as $mother)
                    <option value="{{ $mother->id }}">{{ $mother->name_ar }}</option>
                @endforeach
            </select>
        </div>

        <p class="text-muted/70 text-sm">
            اختيار والد غير موجود في القائمة؟ أضفه أولاً من
            <a href="{{ route('people.create') }}" class="text-gold hover:underline">صفحة إضافة شخص</a>.
        </p>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">
                <span wire:loading.remove>حفظ الروابط</span>
                <span wire:loading>جاري الحفظ...</span>
            </button>
            <a href="{{ route('people.index') }}" class="btn-ghost">إلغاء</a>
        </div>

    </form>
</div>
