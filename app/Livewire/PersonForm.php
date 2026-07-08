<?php

namespace App\Livewire;

use App\Models\Person;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class PersonForm extends Component
{
    use WithFileUploads;

    public ?int $personId = null;

    #[Validate('required|string|max:255')]
    public string $nameAr = '';

    #[Validate('required|in:male,female')]
    public string $gender = '';

    #[Validate('nullable|image|max:3072')]
    public $newPhoto = null;

    public function mount(?Person $person = null): void
    {
        if ($person !== null) {
            $this->personId = $person->id;
            $this->nameAr   = $person->name_ar;
            $this->gender   = $person->gender->value;
        }
    }

    public function title(): string
    {
        return $this->personId ? 'تعديل شخص — شجرتنا' : 'إضافة شخص — شجرتنا';
    }

    public function save(): void
    {
        $this->validate();

        $photoPath = null;

        if ($this->newPhoto !== null) {
            $existingPerson = $this->personId ? Person::find($this->personId) : null;

            if ($existingPerson?->photo) {
                Storage::disk('public')->delete($existingPerson->photo);
            }

            $photoPath = $this->newPhoto->store('photos', 'public');
        }

        if ($this->personId !== null) {
            $updateData = ['name_ar' => $this->nameAr, 'gender' => $this->gender];

            if ($photoPath !== null) {
                $updateData['photo'] = $photoPath;
            }

            Person::findOrFail($this->personId)->update($updateData);
        } else {
            Person::create([
                'name_ar'    => $this->nameAr,
                'gender'     => $this->gender,
                'photo'      => $photoPath,
                'created_by' => auth()->id(),
            ]);
        }

        $this->redirect(route('people.index'), navigate: true);
    }

    public function render()
    {
        $existingPhoto = $this->personId
            ? Person::find($this->personId)?->photo
            : null;

        return view('livewire.person-form', ['existingPhoto' => $existingPhoto])
            ->title($this->title());
    }
}
