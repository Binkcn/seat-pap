<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSrpTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_pap_fleets', function (Blueprint $table) {
            $table->bigInteger('fleet_id')->nullable()->unique();
            $table->string('fleet_name')->nullable();
            $table->bigInteger('fc_character_id')->default(0);
            $table->string('fc_character_name')->nullable();
            $table->text('fleet_motd')->nullable();
            $table->boolean('fleet_available')->default(false);

            $table->timestamps();

            $table->index(['fleet_id', 'fc_character_id']);
        });

        Schema::create('seat_pap_fleet_records', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('fleet_id');
            $table->json('fleet_members')->nullable();
            $table->integer('fleet_members_count')->default(0);

            $table->integer('point')->default(0);

            $table->timestamps();

            $table->index('fleet_id');
        });

        Schema::create('seat_pap_character_records', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('fleet_id');
            $table->bigInteger('character_id');
            $table->string('character_name')->nullable();
            
            $table->integer('ship_type_id')->default(0);
            $table->integer('solar_system_id')->default(0);
            $table->integer('station_id')->default(0);
            $table->string('join_time')->nullable();

            $table->integer('point')->default(0);

            $table->timestamps();

            $table->index(['fleet_id', 'character_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seat_pap_fleets');
        Schema::dropIfExists('seat_pap_fleet_records');
        Schema::dropIfExists('seat_pap_character_records');
    }
}
