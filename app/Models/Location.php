<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'region', 'description', 'capacity', 'latitude', 'longitude'])]
class Location extends Model
{
    //
}
