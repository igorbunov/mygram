<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TariffList extends Model
{
    public function tariff() {
        return $this->hasMany('App\Tariff', 'tariff_list_id', 'id');
    }
}
