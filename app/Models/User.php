<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
    protected $fillable = [
        'first_name',
        'second_name',
        'third_name',
        'fourth_name',
        'mobile',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function createdPeople(): HasMany
    {
        return $this->hasMany(Person::class, 'created_by');
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function fullName(): string
    {
        return \implode(' ', [
            $this->first_name,
            $this->second_name,
            $this->third_name,
            $this->fourth_name,
        ]);
    }
}
