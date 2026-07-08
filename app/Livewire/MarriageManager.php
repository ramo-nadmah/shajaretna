<?php

namespace App\Livewire;

use App\Enums\Gender;
use App\Models\Marriage;
use App\Models\Person;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('الزواجات — شجرتنا')]
class MarriageManager extends Component
{
    public ?int $husbandId = null;
    public ?int $wifeId    = null;

    public function add(): void
    {
        $this->validate([
            'husbandId' => 'required|exists:people,id',
            'wifeId'    => 'required|exists:people,id',
        ], [
            'husbandId.required' => 'اختر الزوج',
            'wifeId.required'    => 'اختر الزوجة',
        ]);

        $alreadyExists = Marriage::where('husband_id', $this->husbandId)
            ->where('wife_id', $this->wifeId)
            ->exists();

        if ($alreadyExists) {
            $this->addError('wifeId', 'هذا الزواج مسجّل مسبقاً');
            return;
        }

        Marriage::create([
            'husband_id' => $this->husbandId,
            'wife_id'    => $this->wifeId,
            'created_by' => auth()->id(),
        ]);

        $this->husbandId = null;
        $this->wifeId    = null;
    }

    public function delete(int $marriageId): void
    {
        Marriage::findOrFail($marriageId)->delete();
    }

    public function render()
    {
        $groupedMarriages = Marriage::with(['husband', 'wife'])
            ->get()
            ->groupBy('husband_id')
            ->sortBy(fn ($marriages) => $marriages->first()->husband->name_ar);

        return view('livewire.marriage-manager', [
            'groupedMarriages' => $groupedMarriages,
            'husbands'         => Person::where('gender', Gender::Male->value)->orderBy('name_ar')->get(),
            'wives'            => Person::where('gender', Gender::Female->value)
                ->whereNotIn('id', Marriage::pluck('wife_id'))
                ->orderBy('name_ar')
                ->get(),
        ]);
    }
}
