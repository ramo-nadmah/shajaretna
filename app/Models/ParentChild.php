<?php

namespace App\Models;

use App\Enums\ParentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentChild extends Model
{
    protected $table = 'parent_child';

    protected $fillable = [
        'parent_id',
        'child_id',
        'parent_type',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'parent_type' => ParentType::class,
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'parent_id');
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'child_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
