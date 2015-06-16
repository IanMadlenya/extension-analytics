<?php

return [

    'name' => 'analytics',

    'main' => 'Pagekit\\Analytics\\AnalyticsExtension',

    'autoload' => [

        'Pagekit\\Analytics\\' => 'src'

    ],

    'routes' => [

        '/' => [
            'name' => '@analytics',
            'controller' => [
                'Pagekit\\Analytics\\Controller\\AnalyticsController'
            ]
        ]

    ],

    'resources' => [

        'analytics:' => ''

    ],

    'config' => [

        'credentials' => [
            'client_id' => '845083612678-l0324vjmuc8q3m7fk5r37v9o4reor61j.apps.googleusercontent.com',
            'client_secret' => 'CiYpV-u9AASBXax5y38TbWmG'
        ]

    ]

];
