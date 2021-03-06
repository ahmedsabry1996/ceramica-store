<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillDetails extends Model
{

    protected $guarded = [];

    public function bill()
    {
      return $this->belongsTo('App\Bill');
    }

    public function stock()
    {
      return $this->belongsTo('App\Stock')->with('mark');
    }

}
