<?php

namespace App\Livewire;

use App\Models\Person;
use App\Services\KinshipCalculator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('القرابة — شجرتنا')]
class KinshipLookup extends Component
{
    public ?int $personAId = null;
    public ?int $personBId = null;
    public ?string $resultLabel  = null;
    public array   $resultLabels = [];
    public bool    $resultFound  = false;
    public bool    $calculated   = false;

    public function calculate(): void
    {
        $this->validate([
            'personAId' => 'required|exists:people,id',
            'personBId' => 'required|exists:people,id',
        ], [
            'personAId.required' => 'اختر الشخص الأول',
            'personBId.required' => 'اختر الشخص الثاني',
        ]);

        $personA = Person::find($this->personAId);
        $personB = Person::find($this->personBId);

        $result = app(KinshipCalculator::class)->calculate($personA, $personB);
        $this->resultLabel  = $result->arabicLabel;
        $this->resultLabels = $result->arabicLabels;
        $this->resultFound  = $result->relationshipFound;
        $this->calculated   = true;
    }

    public function updatedPersonAId(): void
    {
        $this->resultLabel  = null;
        $this->resultLabels = [];
        $this->resultFound  = false;
        $this->calculated   = false;
    }

    public function updatedPersonBId(): void
    {
        $this->resultLabel  = null;
        $this->resultLabels = [];
        $this->resultFound  = false;
        $this->calculated   = false;
    }

    public function render()
    {
        $people = Person::orderBy('name_ar')->get();

        return view('livewire.kinship-lookup', ['people' => $people]);
    }
}
