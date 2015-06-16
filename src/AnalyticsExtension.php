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

        $app->on('app.request', function () use ($app) {

            $presetList = array();
            $groupList = array();

            foreach (glob(__DIR__ . '/../presets/*.json') as $file) {
                $file = json_decode(file_get_contents($file), true);

                if (!$file) {
                    continue;
                }

                $groupList[] = array(
                    'id' => $file['id'],
                    'label' => $file['label']
                );

                $group = array_map(function ($preset) use ($file) {
                    $preset['groupID'] = $file['id'];

                    return $preset;
                }, $file['presets']);

                $presetList = array_merge($presetList, $group);
            }

            $app['scripts']->register('analytics-config', sprintf('var $analytics = %s;', json_encode(
                array(
                    'groups' => $groupList,
                    'presets' => $presetList,
                    'configured' =>  true || isset($options['token']) && isset($options['profile'])
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
