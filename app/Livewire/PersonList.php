<?php

namespace App\Livewire;

use App\Models\Person;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('الأشخاص — شجرتنا')]
class PersonList extends Component
{
    #[Url]
    public string $search = '';

    public function render()
    {
        $people = Person::query()
            ->when($this->search !== '', fn ($query) => $query->where('name_ar', 'like', '%' . $this->search . '%'))
            ->orderBy('name_ar')
            ->get();

        return view('livewire.person-list', ['people' => $people]);
    }
}
