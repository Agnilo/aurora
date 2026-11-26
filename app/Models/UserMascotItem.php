<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMascotItem extends Model
{
    protected $fillable = ['user_id', 'mascot_item_id', 'price_paid'];

    protected $casts = ['price_paid' => 'integer'];

    public function item()
    {
        return $this->belongsTo(MascotItem::class, 'mascot_item_id');
    }
}
