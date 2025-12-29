<?php

return [
    'pap' => [
        'name' => 'PAP',
        'icon' => 'fas fa-space-shuttle',
        'route_segment' => 'pap',
        'entries' => [
            [
                'name' => 'Points',
                'icon' => 'fas fa-award',
                'route' => 'pap.query',
            ],
            [
                'name' => 'Rank',
                'icon' => 'fas fa-trophy',
                'route' => 'papadmin.rank',
                'permission' => 'pap.fc',
            ],
            [
                'name' => 'Fleet Commander',
                'icon' => 'fas fa-user-tie',
                'route' => 'papadmin.fleets',
                'permission' => 'pap.fc',
            ],
        ],
    ],
];
