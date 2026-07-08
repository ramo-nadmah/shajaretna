<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-parchment">حاسبة القرابة</h1>
        <p class="text-muted text-sm mt-1">اختر شخصَين لمعرفة صلة القرابة بينهما</p>
    </div>

    <div class="card space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Person A --}}
            <div>
                <label class="block text-sm text-muted mb-2">
                    <span class="inline-block w-5 h-5 rounded-full bg-jade text-jade-bright text-xs flex items-center justify-center me-1">أ</span>
                    الشخص الأول
                </label>
                <select wire:model="personAId" class="select-field">
                    <option value="">اختر شخصاً...</option>
                    @foreach ($people as $person)
                        <option value="{{ $person->id }}">{{ $person->name_ar }}</option>
                    @endforeach
                </select>
                @error('personAId') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Person B --}}
            <div>
                <label class="block text-sm text-muted mb-2">
                    <span class="inline-block w-5 h-5 rounded-full bg-amber-900/60 text-amber-400 text-xs flex items-center justify-center me-1">ب</span>
                    الشخص الثاني
                </label>
                <select wire:model="personBId" class="select-field">
                    <option value="">اختر شخصاً...</option>
                    @foreach ($people as $person)
                        <option value="{{ $person->id }}">{{ $person->name_ar }}</option>
                    @endforeach
                </select>
                @error('personBId') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <button wire:click="calculate" class="btn-primary w-full sm:w-auto">
                <span wire:loading.remove wire:target="calculate">احسب القرابة</span>
                <span wire:loading wire:target="calculate">جاري الحساب...</span>
            </button>
        </div>
    </div>

    {{-- Result --}}
    @if ($calculated)
        <div class="card mt-6 border-gold/20 text-center py-10">
            @php
                $nameA = $people->firstWhere('id', $personAId)?->name_ar;
                $nameB = $people->firstWhere('id', $personBId)?->name_ar;
            @endphp

            <p class="text-muted text-sm mb-4 tracking-wide">
                {{ $nameA }} <span class="text-gold mx-2">←</span> {{ $nameB }}
            </p>

            @if ($resultLabel === 'نفس الشخص')
                <p class="text-gold text-4xl mb-3 kinship-arabic-font">نفس الشخص</p>
                <p class="text-muted text-sm">اخترت نفس الشخص مرتين</p>
            @elseif ($resultFound)
                <p class="text-gold leading-none mb-2 kinship-result-primary" dir="rtl">
                    {{ $resultLabels[0] ?? $resultLabel }}
                </p>
                <p class="text-muted text-sm mb-4">
                    <strong class="text-parchment">{{ $nameB }}</strong> بالنسبة لـ<strong class="text-parchment">{{ $nameA }}</strong>
                </p>

                @if (count($resultLabels) > 1)
                    <div class="border-t border-white/8 pt-4 mt-2 space-y-3">
                        @foreach (array_slice($resultLabels, 1) as $extraLabel)
                            <div>
                                <p class="text-gold/80 text-2xl kinship-arabic-font" dir="rtl">
                                    {{ $extraLabel }}
                                </p>
                                <p class="text-muted text-sm mt-0.5">
                                    <strong class="text-parchment">{{ $nameA }}</strong> بالنسبة لـ<strong class="text-parchment">{{ $nameB }}</strong>
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <p class="text-muted text-2xl mb-3">—</p>
                <p class="text-muted text-sm">لم يُعثر على صلة قرابة (يحتاج إلى ربط الوالدين أولاً)</p>
            @endif
        </div>
    @endif
</div>

{{-- Arabic display font and fluid sizing for kinship result labels --}}
<style>
.kinship-arabic-font {
    font-family: 'Segoe UI', 'Noto Naskh Arabic', 'Arial Unicode MS', sans-serif;
}

.kinship-result-primary {
    font-family: 'Segoe UI', 'Noto Naskh Arabic', 'Arial Unicode MS', sans-serif;
    font-size: clamp(3.25rem, 10vw, 5rem);
}
</style>
