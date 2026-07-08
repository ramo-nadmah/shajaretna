<div class="max-w-lg mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-parchment">إضافة شخص جديد</h1>
        <p class="text-muted text-sm mt-1">أدخل الاسم الكامل بالعربية وحدّد الجنس</p>
    </div>

    <form wire:submit="save" class="card space-y-6">

        {{-- Name --}}
        <div>
            <label class="block text-sm text-muted mb-2">الاسم الكامل</label>
            <input
                wire:model="nameAr"
                type="text"
                placeholder="مثال: أحمد بن خالد الحمدان"
                class="input-field text-base"
                dir="rtl"
                autocomplete="off"
            >
            @error('nameAr')
                <p class="text-red-400 text-sm mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Gender --}}
        <div>
            <label class="block text-sm text-muted mb-3">الجنس</label>
            <div class="flex gap-3">
                <label class="flex-1 cursor-pointer">
                    <input wire:model="gender" type="radio" value="male" class="sr-only peer">
                    <div class="card text-center py-3 peer-checked:border-jade-bright peer-checked:bg-jade/20 transition-all">
                        <span class="text-jade-bright text-2xl">♂</span>
                        <p class="text-sm mt-1 text-parchment">ذكر</p>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input wire:model="gender" type="radio" value="female" class="sr-only peer">
                    <div class="card text-center py-3 peer-checked:border-pink-400 peer-checked:bg-pink-950/30 transition-all">
                        <span class="text-pink-400 text-2xl">♀</span>
                        <p class="text-sm mt-1 text-parchment">أنثى</p>
                    </div>
                </label>
            </div>
            @error('gender')
                <p class="text-red-400 text-sm mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">
                <span wire:loading.remove>حفظ الشخص</span>
                <span wire:loading>جاري الحفظ...</span>
            </button>
            <a href="{{ route('people.index') }}" class="btn-ghost">إلغاء</a>
        </div>

    </form>
</div>
