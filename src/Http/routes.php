<?php

Route::group([
    'namespace' => 'Binkcn\Seat\SeatPap\Http\Controllers',
    'prefix' => 'pap',
], function () {
    Route::group([
        'middleware' => ['web', 'auth'],
    ], function () {
        Route::get('/', [
            'as' => 'pap.query',
            'uses' => 'PapController@papGetRequests',
        ]);

        Route::get('/admin', [
            'as' => 'papadmin.fleets',
            'uses' => 'PapAdminController@papGetFleets',
            'middleware' => 'can:pap.fc',
        ]);

        Route::get('/admin/rank', [
            'as' => 'papadmin.rank',
            'uses' => 'PapAdminController@papGetRank',
            'middleware' => 'can:pap.fc',
        ]);

        Route::get('/admin/getfleetinfo', [
            'as' => 'papadmin.getFleetInfo',
            'uses' => 'PapAdminController@papGetFleetInfo',
            'middleware' => 'can:pap.fc',
        ]);

        Route::get('/admin/getfleetrecords/{fleet_id}', [
            'as' => 'papadmin.getFleetRecords',
            'uses' => 'PapAdminController@papGetFleetRecords',
            'middleware' => 'can:pap.fc',
        ]);

        Route::post('/admin/issuepoints/{fleet_id}', [
            'as' => 'papadmin.issuePoints',
            'uses' => 'PapAdminController@papIssuePoints',
            'middleware' => 'can:pap.fc',
        ]);
    });
});
