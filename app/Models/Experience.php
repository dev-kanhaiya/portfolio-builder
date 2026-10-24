<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $fillable = [
        'portfolio_id', 'company', 'role', 'description', 'start_date', 'end_date', 'current_job',
    ];

    protected function casts(): array
    {
        return ['current_job' => 'boolean'];
    }

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }
}
