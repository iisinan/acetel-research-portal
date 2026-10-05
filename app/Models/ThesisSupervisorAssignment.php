<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ThesisSupervisorAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'supervision_assignments'; // Verify table name from migration

    protected $fillable = [
        'thesis_project_id',
        'supervisor_profile_id',
        'role', // primary, secondary
        'status', // active, inactive
        'assigned_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function thesis()
    {
        return $this->belongsTo(ThesisProject::class, 'thesis_project_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(SupervisorProfile::class, 'supervisor_profile_id');
    }
}
