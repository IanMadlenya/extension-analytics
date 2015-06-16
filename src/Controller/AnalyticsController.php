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

    protected $extension, $oauth;

    /**
     * @Route("/auth", methods="GET")
     */
    public function authRedirectAction()
    {
        $config = $this['config']->get('analytics');

        $service = App::get('analytics/oauth')->create('google', array('ANALYTICS'), $config['credentials'], false, 'urn:ietf:wg:oauth:2.0:oob');

        $service->setAccessType('offline');
        $authorizationUri = $service->getAuthorizationUri(array('approval_prompt' => 'force'));

        return App::response()->redirect($authorizationUri);
    }

    /**
     * @Route("/dashboard", methods="POST")
     * @Request({"metrics": "string", "dimensions":"string", "startDate":"string", "maxResults": "int"})
     */
    public function dashboardAction($metrics, $dimensions, $startDate, $maxResults = false)
    {

        // $options = $this['option']->get('analytics');

        // Interim Workaround:
        $options = ['profile' => 89499433];

        if (!isset($options['profile'])) {
            return $this['response']->json(array('message' => 'Not configured'), 400);
        }

        $data = array('metrics' => $metrics,
            'dimensions' => $dimensions,
            'start-date' => $startDate,
            'ids' => 'ga:' . $options['profile'],
            'end-date' => 'today',
            'output' => 'dataTable');

        if ($maxResults) {
            $data['max-results'] = $maxResults;
        }

        $url = self::API . '/data/ga?';

        foreach ($data as $key => $value) {
            $url .= $key . '=' . $value . '&';
        }

        return App::response($this->request($url));

//        if (true || !$result = $this['cache']->fetch(md5($url))) {
//            try {
//                $result = json_decode($this->oauth->request($url), true);
//                $this['cache']->save(md5($url), $result, 0);
//            } catch (\Exception $e) {
//                var_dump($e);
//                return false;
//            }
//        }
//
//        return $result;
    }

    /**
     * @Route("/profile", methods="GET")
     */
    public function profileAction()
    {
        return App::response($this->request(self::API . '/management/accounts/~all/webproperties/~all/profiles'));
    }

    /**
     * @Route("/profile", methods="POST")
     * @Request({"profile": "string"})
     */
    public function saveProfileAction($profile)
    {
        $options = $this['option']->get('analytics');

        $options['profile'] = $profile;

        $this['option']->set('analytics', $options);

        return App::response()->json(array());
    }

    /**
     * @Route("/disconnect", methods="DELETE")
     */
    public function disconnectAction()
    {
        $options = $this['option']->get('analytics');

        unset($options['profile']);

        return App::response()->json(array());
    }

    protected function request($url)
    {
        try {
            $config = App::module('analytics')->config();
            // $options = $this['option']->get('analytics');

            // Interim Workaround:
            $options = ['token' => [
                'accessToken' => 'ya29.kwHU-3WSxA0rLUPUfEtR_I0G9ACA_BKVEcVlNSzpyDoP7RY8_P3k-Y4cMzn50xqco2A8O707KHEZRQ',
                'refreshToken' => '1/AnXb0aeFGvMulITB8O0bG8ZvdJWJT6f6X4WAQG47hZpIgOrJDtdun6zK6XiATCKT',
                'endOfLife' => 1434357631,
                'extraParams' =>
                    ['token_type' => 'Bearer']
            ]];

            $service = App::get('analytics/oauth')->create('google', array('ANALYTICS'), $config['credentials'], $options['token']);

            return $service->request($url);

        } catch (\Exception $e) {
            return App::response()->json(array('message' => $e->getMessage()), 400);
        }
    }
}
