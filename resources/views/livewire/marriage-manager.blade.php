<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-parchment">الزواجات</h1>
        <p class="text-muted text-sm mt-1">ربط الأزواج داخل شجرة العائلة</p>
    </div>

    {{-- Add form --}}
    <div class="card mb-8">
        <h2 class="text-base text-muted mb-4">إضافة زواج جديد</h2>
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">

            <div class="flex-1">
                <label class="block text-sm text-muted mb-1.5 flex items-center gap-1.5">
                    <span class="text-jade-bright">♂</span> الزوج
                </label>
                <select wire:model="husbandId" class="select-field">
                    <option value="">اختر...</option>
                    @foreach ($husbands as $husband)
                        <option value="{{ $husband->id }}">{{ $husband->name_ar }}</option>
                    @endforeach
                </select>
                @error('husbandId') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="text-muted/40 text-xl self-center pb-1 hidden sm:block">⟷</div>

            <div class="flex-1">
                <label class="block text-sm text-muted mb-1.5 flex items-center gap-1.5">
                    <span class="text-pink-400">♀</span> الزوجة
                </label>
                <select wire:model="wifeId" class="select-field">
                    <option value="">اختر...</option>
                    @foreach ($wives as $wife)
                        <option value="{{ $wife->id }}">{{ $wife->name_ar }}</option>
                    @endforeach
                </select>
                @error('wifeId') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button wire:click="add" class="btn-primary whitespace-nowrap self-end">
                <span wire:loading.remove wire:target="add">+ إضافة</span>
                <span wire:loading wire:target="add">...</span>
            </button>

        </div>
    </div>

    {{-- Grouped marriages list --}}
    @if ($groupedMarriages->isEmpty())
        <div class="card text-center py-16">
            <p class="text-muted">لا توجد زواجات مسجّلة بعد</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($groupedMarriages as $marriages)
                @php $husband = $marriages->first()->husband @endphp

                <div class="bg-ground-card rounded-xl border border-white/6 hover:border-white/12 transition-colors overflow-hidden flex">

                    {{-- Husband — single name, vertically centered --}}
                    <div class="flex-shrink-0 flex items-center gap-2.5 px-4 sm:px-5 border-e border-white/8 w-28 sm:w-44 min-w-0">
                        <span class="text-jade-bright text-base leading-none">♂</span>
                        <span class="text-parchment font-medium text-sm truncate">{{ $husband->name_ar }}</span>
                    </div>

                    {{-- Wives — stacked, each row same height as a normal card row --}}
                    <div class="flex-1 divide-y divide-white/6">
                        @foreach ($marriages as $marriage)
                            <div class="flex items-center justify-between px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-parchment text-sm font-medium">{{ $marriage->wife->name_ar }}</span>
                                    <span class="text-pink-400 text-base leading-none">♀</span>
                                </div>
                                <button
                                    wire:click="delete({{ $marriage->id }})"
                                    wire:confirm="حذف هذا الزواج؟"
                                    class="text-muted/30 hover:text-red-400 transition-colors text-xl leading-none ms-4"
                                    title="حذف"
                                >×</button>
                            </div>
                        @endforeach
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</div>
