<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ExternalExaminerProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'program_id',
        'institution',
        'expertise',
        'phone',
        'office_address',
        'active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
