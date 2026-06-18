<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagedApp extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_name',
        'internet_block',
    ];

    protected $casts = [
        'internet_block' => 'boolean',
    ];
}
