<?php

namespace App\Livewire;

use App\Enums\Gender;
use App\Enums\ParentType;
use App\Models\ParentChild;
use App\Models\Person;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('ربط الوالدين — شجرتنا')]
class ParentLink extends Component
{
    public Person $person;
    public ?int $fatherId = null;
    public ?int $motherId = null;

    public function mount(Person $person): void
    {
        $this->person = $person;
        $this->fatherId = $person->parents()
            ->wherePivot('parent_type', ParentType::Father->value)
            ->value('people.id');
        $this->motherId = $person->parents()
            ->wherePivot('parent_type', ParentType::Mother->value)
            ->value('people.id');
    }

    public function save(): void
    {
        // Replace all existing parent links for this child
        ParentChild::where('child_id', $this->person->id)->delete();

        if ($this->fatherId !== null) {
            ParentChild::create([
                'parent_id'   => $this->fatherId,
                'child_id'    => $this->person->id,
                'parent_type' => ParentType::Father->value,
                'created_by'  => auth()->id(),
            ]);
        }

        if ($this->motherId !== null) {
            ParentChild::create([
                'parent_id'   => $this->motherId,
                'child_id'    => $this->person->id,
                'parent_type' => ParentType::Mother->value,
                'created_by'  => auth()->id(),
            ]);
        }

        $this->redirect(route('people.index'), navigate: true);
    }

    public function render()
    {
        $fathers = Person::where('gender', Gender::Male->value)
            ->where('id', '!=', $this->person->id)
            ->orderBy('name_ar')
            ->get();

        $mothers = Person::where('gender', Gender::Female->value)
            ->where('id', '!=', $this->person->id)
            ->orderBy('name_ar')
            ->get();

        return view('livewire.parent-link', [
            'fathers' => $fathers,
            'mothers' => $mothers,
        ]);
    }
}
