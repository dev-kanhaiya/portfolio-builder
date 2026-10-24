<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'portfolio_id', 'full_name', 'headline', 'bio', 'email', 'phone', 'location',
        'github_url', 'linkedin_url', 'website_url', 'avatar_path', 'cover_path',
    ];

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }
}
