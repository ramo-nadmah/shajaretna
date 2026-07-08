<div>
    {{-- Search + count header --}}
    <div class="flex items-center gap-4 mb-8">
        <div class="relative flex-1">
            <input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="ابحث بالاسم..."
                class="input-field ps-10"
            >
            <svg class="absolute end-3 top-1/2 -translate-y-1/2 text-muted w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
        </div>
        <span class="text-muted text-sm whitespace-nowrap">{{ $people->count() }} شخص</span>
    </div>

    @if ($people->isEmpty())
        <div class="card text-center py-16">
            <p class="text-muted text-lg mb-2">لا توجد نتائج</p>
            @if ($search !== '')
                <p class="text-muted/60 text-sm">جرّب كلمة بحث مختلفة</p>
            @else
                <a href="{{ route('people.create') }}" class="btn-primary mt-4 inline-block">أضف أول شخص</a>
            @endif
        </div>
    @else
        <div class="space-y-2">
            @foreach ($people as $person)
                <div class="card flex items-center gap-4 p-4 hover:border-white/12 transition-colors">
                    {{-- Gender pip --}}
                    <div class="w-1 self-stretch rounded-full flex-shrink-0 {{ $person->gender->value === 'male' ? 'bg-jade-bright' : 'bg-pink-400' }}"></div>

                    {{-- Name + parents --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-parchment font-medium text-base leading-tight">{{ $person->name_ar }}</p>
                        @php
                            $father = $person->father();
                            $mother = $person->mother();
                        @endphp
                        @if ($father || $mother)
                            <p class="text-muted text-sm mt-0.5">
                                @if ($father) الأب: {{ $father->name_ar }} @endif
                                @if ($father && $mother) · @endif
                                @if ($mother) الأم: {{ $mother->name_ar }} @endif
                            </p>
                        @endif
                    </div>

                    {{-- Gender pill --}}
                    @if ($person->gender->value === 'male')
                        <span class="pill-male">ذكر</span>
                    @else
                        <span class="pill-female">أنثى</span>
                    @endif

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('people.parents', $person) }}" class="btn-ghost text-sm py-2.5">
                            ربط الوالدين
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
