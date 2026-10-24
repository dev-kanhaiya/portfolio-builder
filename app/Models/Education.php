<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $fillable = [
        'portfolio_id', 'institution', 'degree', 'field', 'description', 'start_date', 'end_date',
    ];

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }
}
