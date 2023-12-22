<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    //
    protected $guarded = ['id'];
}

{
    $this->app->bind('path.public', function()
    {
        return base_path('public_html');
    });
}
