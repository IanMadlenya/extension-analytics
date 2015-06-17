<?php

namespace Pagekit\Analytics\Controller;

use Pagekit\Application as App;

/**
 * @Access(admin=true)
 * @Route("analytics", name="analytics")
 */
class AnalyticsController
{
    const API = 'https://www.googleapis.com/analytics/v3';
    const REDIRECT_URI = 'urn:ietf:wg:oauth:2.0:oob';
    const CACHE_TIME = 3600;
    const REALTIME_CACHE_TIME = 20;

    protected $extension, $oauth;

    /**
     * @Route("/auth", methods="GET")
     */
    public function authRedirectAction()
    {
        $config = App::module('analytics')->config();

        $service = App::get('analytics/oauth')->create('google', $config['credentials'], false, self::REDIRECT_URI, array('ANALYTICS'));

        $service->setAccessType('offline');
        $authorizationUri = $service->getAuthorizationUri(array('approval_prompt' => 'force'));

        return App::response()->redirect($authorizationUri);
    }

    /**
     * @Route("/api", methods="POST")
     * @Request({"metrics": "string", "dimensions":"string", "startDate":"string", "invalidCache": "boolean", "maxResults": "int"})
     */
    public function apiAction($metrics, $dimensions, $startDate, $invalidCache = false, $maxResults = false)
    {
        $config = App::module('analytics')->config();

        if (!isset($config['profile'])) {
            return $this['response']->json(array('message' => 'Not configured'), 400);
        }

        $data = array('metrics' => $metrics,
            'dimensions' => $dimensions,
            'start-date' => $startDate,
            'ids' => 'ga:' . $config['profile'],
            'end-date' => 'today',
            'output' => 'dataTable');

        if ($maxResults) {
            $data['max-results'] = $maxResults;
        }

        $url = self::API . '/data/ga?' . http_build_query($data);

        if ($invalidCache || !$result = App::get('cache')->fetch(md5($url))) {
            try {
                $result = $this->request($url);
                App::get('cache')->save(md5($url), $result, self::CACHE_TIME);
            } catch (\Exception $e) {
                return App::response()->json(array('message' => $e->getMessage()), 400);
            }
        }

        return App::response()->json($result);
    }

    /**
     * @Route("/realtime", methods="POST")
     * @Request({"metrics": "string", "dimensions":"string", "invalidCache": "boolean"})
     */
    public function realtimeAction($metrics, $dimensions, $invalidCache = false)
    {
        $config = App::module('analytics')->config();

        if (!isset($config['profile'])) {
            return $this['response']->json(array('message' => 'Not configured'), 400);
        }

        $data = array('metrics' => $metrics,
            'dimensions' => $dimensions,
            'ids' => 'ga:' . $config['profile'],
            'output' => 'dataTable');

        $url = self::API . '/data/realtime?' . http_build_query($data);

        if ($invalidCache || !$result = App::get('cache')->fetch(md5($url))) {
            try {
                $result = $this->request($url);
                App::get('cache')->save(md5($url), $result, self::REALTIME_CACHE_TIME);
            } catch (\Exception $e) {
                return App::response()->json(array('message' => $e->getMessage()), 400);
            }
        }

        return App::response()->json($result);
    }


    /**
     * @Route("/profile", methods="GET")
     */
    public function profileAction()
    {
        return App::response()->json($this->request(self::API . '/management/accounts/~all/webproperties/~all/profiles'));
    }

    /**
     * @Route("/code", methods="POST")
     * @Request({"code": "string"})
     */
    public function authCodeAction($code)
    {
        $oauth = App::get('analytics/oauth');
        $config = App::module('analytics')->config();

        $token = $oauth->requestToken('google', $code, $config['credentials'], self::REDIRECT_URI);

        App::config('analytics')->set('token', $oauth->tokenToArray($token));

        return App::response()->json(['success' => true]);
    }

    /**
     * @Route("/profile", methods="POST")
     * @Request({"profile": "string"})
     */
    public function saveProfileAction($profile)
    {
        App::config('analytics')->set('profile', $profile);

        return App::response()->json(array());
    }

    /**
     * @Route("/disconnect", methods="DELETE")
     */
    public function disconnectAction()
    {
        unset(App::config('analytics')['profile']);

        return App::response()->json(array());
    }

    protected function request($url)
    {
        $config = App::module('analytics')->config();
        $service = App::get('analytics/oauth')->create('google', $config['credentials'], $config['token']);
        $result = json_decode($service->request($url), true);

        $return = [];
        foreach (['columnHeaders', 'totalsForAllResults', 'dataTable', 'items'] as $key) {
            if (isset($result[$key])) {
                $return[$key] = $result[$key];
            }
        }

        $return['time'] = time();

        return $return;
    }
}
