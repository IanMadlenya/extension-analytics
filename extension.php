<?php

use Pagekit\Analytics\OAuthHelper;

return [

    'name' => 'analytics',

    'main' => function ($app) {
        $app->set('analytics/oauth', function () {
            return new OAuthHelper();
        });

        $app->on('request', function () use ($app) {
            $presetList = array();
            $groupList = array();

            foreach (json_decode(file_get_contents(__DIR__ . '/presets.json'), true) as $group) {

                if (!$group) {
                    continue;
                }

                $groupList[] = array(
                    'id' => $group['id'],
                    'label' => $group['label']
                );

                $groupPresets = array_map(function ($preset) use ($group) {
                    $preset['groupID'] = $group['id'];

                    return $preset;
                }, $group['presets']);

                $presetList = array_merge($presetList, $groupPresets);
            }

            $app['scripts']->register('analytics-config', sprintf('var $analytics = %s;', json_encode(
                array(
                    'groups' => $groupList,
                    'presets' => $presetList,
                    'connected' => $this->config('token'),
                    'profile' => $this->config('profile', false),
                    'geo' => array(
                        'world' => $app['intl']->territory()->getName('001'),
                        'continents' => $app['intl']->territory()->getContinents(),
                        'subcontinents' => $app['intl']->territory()->getList('S'),
                        'countries' => $app['intl']->territory()->getCountries()
                    )
                )
            )), array(), 'string');

            $app['scripts']->register('google', '//www.google.com/jsapi');
            $app['scripts']->register('widget-analytics', 'analytics:app/bundle/analytics.js', array('~dashboard', 'google', 'analytics-config'));
        });

        $app->on('uninstall.analytics', function () use ($app) {
            // remove the config
            $app['config']->remove($this->name);
        });
    },

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
