<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CoordinatorProfile extends Model
{
    /** @use HasFactory<\Database\Factories\CoordinatorProfileFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'program_id',
        'level_id',
        'active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}
