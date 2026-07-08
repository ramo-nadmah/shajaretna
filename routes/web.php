<?php

use App\Livewire\FamilyTree;
use App\Livewire\KinshipLookup;
use App\Livewire\Login;
use App\Livewire\MarriageManager;
use App\Livewire\ParentLink;
use App\Livewire\PersonForm;
use App\Livewire\PersonList;
use App\Models\Person;
use App\Services\KinshipCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/',                        PersonList::class)->name('people.index');
    Route::get('/people/create',           PersonForm::class)->name('people.create');
    Route::get('/people/{person}/parents', ParentLink::class)->name('people.parents');
    Route::get('/marriages',               MarriageManager::class)->name('marriages');
    Route::get('/kinship',                 KinshipLookup::class)->name('kinship');
    Route::get('/tree',                    FamilyTree::class)->name('tree');

    Route::get('/kinship/calculate', function (Request $request) {
        $personA = Person::findOrFail($request->integer('a'));
        $personB = Person::findOrFail($request->integer('b'));
        $result  = app(KinshipCalculator::class)->calculate($personA, $personB);
        return response()->json([
            'label'  => $result->arabicLabel,
            'labels' => $result->arabicLabels,
            'found'  => $result->relationshipFound,
        ]);
    })->name('kinship.calculate');
});
