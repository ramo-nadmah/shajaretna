<div class="min-h-[80vh] flex items-center justify-center">
    <div class="w-full max-w-sm">

        <div class="text-center mb-10">
            <p class="text-gold text-4xl mb-2 login-logo">شجرتنا</p>
        </div>

        {{-- ── Step 1: Mobile ── --}}
        @if ($step === 'mobile')
            <form wire:submit="checkMobile" class="card space-y-5">
                <div>
                    <label class="block text-sm text-muted mb-2">رقم الجوال</label>
                    <input
                        wire:model="mobile"
                        type="tel"
                        placeholder="07XXXXXXXX"
                        class="input-field text-center tracking-widest"
                        dir="ltr"
                        autocomplete="tel"
                        autofocus
                    >
                    @error('mobile') <p class="text-red-400 text-sm mt-1.5 text-center">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary w-full">
                    <span wire:loading.remove wire:target="checkMobile">متابعة</span>
                    <span wire:loading wire:target="checkMobile">جاري التحقق...</span>
                </button>
            </form>

        {{-- ── Step 2: Register (new mobile) ── --}}
        @else
            <form wire:submit="register" class="card space-y-5">

                {{-- Mobile display (read-only) --}}
                <div class="flex items-center justify-between bg-ground-raised rounded-lg px-4 py-3">
                    <span class="text-parchment text-sm tracking-widest" dir="ltr">{{ $mobile }}</span>
                    <button type="button" wire:click="backToMobile" class="text-gold text-sm px-2 py-1 hover:underline">
                        تغيير
                    </button>
                </div>

                <p class="text-muted text-sm text-center -mt-2">
                    رقم جديد — أدخل اسمك الرباعي لإنشاء حسابك
                </p>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm text-muted mb-1.5">الاسم الأول</label>
                        <input wire:model="firstName" type="text" placeholder="أحمد" class="input-field" autocomplete="given-name">
                        @error('firstName') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-muted mb-1.5">اسم الأب</label>
                        <input wire:model="secondName" type="text" placeholder="محمد" class="input-field" autocomplete="off">
                        @error('secondName') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-muted mb-1.5">اسم الجد</label>
                        <input wire:model="thirdName" type="text" placeholder="علي" class="input-field" autocomplete="off">
                        @error('thirdName') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-muted mb-1.5">اسم العائلة</label>
                        <input wire:model="fourthName" type="text" placeholder="الحمدان" class="input-field" autocomplete="family-name">
                        @error('fourthName') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full">
                    <span wire:loading.remove wire:target="register">إنشاء حساب</span>
                    <span wire:loading wire:target="register">جاري الإنشاء...</span>
                </button>

            </form>
        @endif

    </div>
</div>

{{-- Arabic display font for the app logo --}}
<style>
.login-logo {
    font-family: 'Segoe UI', 'Noto Naskh Arabic', sans-serif;
}
</style>
