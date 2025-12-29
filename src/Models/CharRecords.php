<?php
namespace Binkcn\Seat\SeatPap\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Web\Models\User;

class CharRecords extends Model
{
    public $timestamps = true;

    protected $primaryKey = 'fleet_id';

    protected $table = 'seat_pap_character_records';

    protected $fillable = [
        'fleet_id', 'character_id', 'character_name', 'ship_type_id', 'solar_system_id', 'station_id', 'join_time', 'point',
    ];

}
