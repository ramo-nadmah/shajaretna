<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('دخول — شجرتنا')]
class Login extends Component
{
    public string $step   = 'mobile'; // 'mobile' | 'register'
    public string $mobile = '';

    public string $firstName  = '';
    public string $secondName = '';
    public string $thirdName  = '';
    public string $fourthName = '';

    public function checkMobile(): void
    {
        $this->validate(['mobile' => 'required|string|min:9|max:15']);

        $existingUser = User::where('mobile', \trim($this->mobile))->first();

        if ($existingUser !== null) {
            Auth::login($existingUser);
            $this->redirect(route('people.index'), navigate: true);
        } else {
            $this->step = 'register';
        }
    }

    public function register(): void
    {
        $this->validate([
            'firstName'  => 'required|string|max:100',
            'secondName' => 'required|string|max:100',
            'thirdName'  => 'required|string|max:100',
            'fourthName' => 'required|string|max:100',
        ]);

        $newUser = User::create([
            'first_name'  => \trim($this->firstName),
            'second_name' => \trim($this->secondName),
            'third_name'  => \trim($this->thirdName),
            'fourth_name' => \trim($this->fourthName),
            'mobile'      => \trim($this->mobile),
            'created_by'  => null,
        ]);

        Auth::login($newUser);
        $this->redirect(route('people.index'), navigate: true);
    }

    public function backToMobile(): void
    {
        $this->step      = 'mobile';
        $this->firstName = $this->secondName = $this->thirdName = $this->fourthName = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.login');
    }
}
