<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AppointmentService extends Pivot
{
    protected $table = 'appointment_services';

    public $incrementing = true;
}
