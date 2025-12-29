<?php
namespace Binkcn\Seat\SeatPap\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Web\Models\User;

class Fleets extends Model
{
    public $timestamps = true;

    protected $primaryKey = 'fleet_id';

    protected $table = 'seat_pap_fleets';

    protected $fillable = [
        'fleet_id', 'fc_character_id', 'fc_character_name', 'fleet_motd', 'fleet_available',
    ];
}
