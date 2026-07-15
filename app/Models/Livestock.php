<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'category', 'name', 'price', 'desc', 'weight', 'age', 'fit', 'image'])]
class Livestock extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'livestock';
}
