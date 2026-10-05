<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DocumentTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'file_path',
        'type', // proposal, seminar, defence, other
        'version',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
