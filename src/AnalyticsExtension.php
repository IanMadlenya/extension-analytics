<?php

namespace Pagekit\Analytics;

use Pagekit\Application as App;
use Pagekit\System\Extension;

class AnalyticsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function main(App $app)
    {
        $app->set('analytics/oauth', function () {
            return new OAuthHelper();
        });

        $app->on('request', function () use ($app) {
            $config = App::module('analytics')->config();

            $presetList = array();
            $groupList = array();

            foreach (json_decode(file_get_contents(__DIR__ . '/../presets.json'), true) as $group) {

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
                    'connected' => isset($config['token']),
                    'profile' => isset($config['profile']) ? $config['profile'] : false
                )
            )), array(), 'string');

            $app['scripts']->register('google', '//www.google.com/jsapi');
            $app['scripts']->register('widget-analytics', 'analytics:app/bundle/analytics.js', array('~dashboard', 'google', 'analytics-config'));
        });
    }

    public function uninstall()
    {
        // remove the config
        App::config()->remove($this->name);
    }
}
