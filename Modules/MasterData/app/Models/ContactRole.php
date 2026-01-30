<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ContactRole extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'description'];
}
