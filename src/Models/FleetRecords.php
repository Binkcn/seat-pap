<?php
namespace Binkcn\Seat\SeatPap\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Web\Models\User;

class FleetRecords extends Model
{
    public $timestamps = true;

    protected $primaryKey = 'id';

    protected $table = 'seat_pap_fleet_records';

    protected $fillable = [
        'id', 'fleet_id', 'fleet_members', 'fleet_members_count', 'point',
    ];
}
