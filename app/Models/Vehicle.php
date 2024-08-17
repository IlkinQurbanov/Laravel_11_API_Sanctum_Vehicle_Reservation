<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Driver;
use App\Models\Reservation;


class Vehicle extends Model
{
    use HasFactory;
    protected $fillable = ['model', 'category_id', 'driver_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
