<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\ParentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $fillable = [
        'name_ar',
        'gender',
        'photo',
        'birth_year',
        'death_year',
        'is_alive',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'gender'   => Gender::class,
            'is_alive' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function marriagesAsHusband(): HasMany
    {
        return $this->hasMany(Marriage::class, 'husband_id');
    }

    public function marriagesAsWife(): HasMany
    {
        return $this->hasMany(Marriage::class, 'wife_id');
    }

    public function parentChildAsParent(): HasMany
    {
        return $this->hasMany(ParentChild::class, 'parent_id');
    }

    public function parentChildAsChild(): HasMany
    {
        return $this->hasMany(ParentChild::class, 'child_id');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'parent_child', 'parent_id', 'child_id')
                    ->withPivot('parent_type', 'created_by')
                    ->withTimestamps();
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'parent_child', 'child_id', 'parent_id')
                    ->withPivot('parent_type', 'created_by')
                    ->withTimestamps();
    }

    public function father(): static|null
    {
        return $this->parents()->wherePivot('parent_type', ParentType::Father->value)->first();
    }

    public function mother(): static|null
    {
        return $this->parents()->wherePivot('parent_type', ParentType::Mother->value)->first();
    }
}
