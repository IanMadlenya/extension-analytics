<?php

namespace Pagekit\Analytics;

use Pagekit\Application as App;


use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\Memory;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\ServiceFactory;



class OAuthHelper
{
    protected $serviceFactory, $services;

    public function __construct()
    {
        $this->serviceFactory = new ServiceFactory();
    }

    /**
     * Open OAuth session and get new token
     *
     * @param  string $provider
     * @param  array $scope
     * @param $credentials
     * @param bool $token
     * @param string $redirectUri
     * @return false|Service
     * @throws \Exception
     */
    public function create($provider, $scope, $credentials, $token = false, $redirectUri = '')
    {
        $provider = ucfirst(strtolower($provider));
        $storage =  new Memory;

        if (!isset($credentials['client_id']) || !isset($credentials['client_secret'])) {
            throw new \Exception('Credentials not valid');
        }

        $credentials = new Credentials(
            $credentials['client_id'],
            $credentials['client_secret'],
            $redirectUri
        );

        if (!$service = $this->serviceFactory->createService($provider, $credentials, $storage, $scope)) {
            throw new \Exception('Could not create Service');
        }

        if ($token && $token = $this->loadToken($token)) {
            $storage->storeAccessToken($provider, $token);

            if ($token->getEndOfLife() < time()) {
                if ($token->getRefreshToken()) {
                    try {
                        $service->refreshAccessToken($token);
                    } catch (\Exception $e) {
                        throw new \Exception('Could not refresh Token');
                    }
                } else {
                    throw new \Exception('Token expired');
                }
            }
        }

        return $service;
    }

    /**
     * Get token from storage
     *
     * @param $data
     * @return Token
     * @internal param string $provider
     * @internal param int $key
     */
    public function loadToken($data)
    {
        if ($data &&
            array_key_exists('accessToken', $data) &&
            array_key_exists('accessTokenSecret', $data) &&
            array_key_exists('requestToken', $data) &&
            array_key_exists('requestTokenSecret', $data) &&
            array_key_exists('endOfLife', $data) &&
            array_key_exists('extraParams', $data)
        ) {
            $token = new StdOAuth1Token($data['accessToken']);
            $token->setAccessTokenSecret($data['accessTokenSecret']);
            $token->setRequestToken($data['requestToken']);
            $token->setRequestTokenSecret($data['requestTokenSecret']);
            $token->setEndOfLife($data['endOfLife']);
            $token->setExtraParams($data['extraParams']);
        } elseif ($data &&
            array_key_exists('accessToken', $data) &&
            array_key_exists('refreshToken', $data) &&
            array_key_exists('endOfLife', $data) &&
            array_key_exists('extraParams', $data)
        ) {
            $token = new StdOAuth2Token($data['accessToken'], $data['refreshToken'], null, $data['extraParams']);
            $token->setEndOfLife($data['endOfLife']);
        }

        if (!isset($token) || !$token) {
            return null;
        }

        return $token;
    }


    /**
     * Save token to storage
     *
     * @param  Token $token
     * @return array
     */
    public function parseToken($token)
    {
        $data = array();

        if (get_class($token) === 'OAuth\OAuth1\Token\StdOAuth1Token') {
            $data['accessToken'] = $token->getAccessToken();
            $data['accessTokenSecret'] = $token->getAccessTokenSecret();
            $data['requestToken'] = $token->getRequestToken();
            $data['requestTokenSecret'] = $token->getRequestTokenSecret();
            $data['endOfLife'] = $token->getEndOfLife();
            $data['extraParams'] = $token->getExtraParams();
        } else {
            $data['accessToken'] = $token->getAccessToken();
            $data['refreshToken'] = $token->getRefreshToken();
            $data['endOfLife'] = $token->getEndOfLife();
            $data['extraParams'] = $token->getExtraParams();
        }

        return $data;
    }


    /**
     * Get redirect url
     *
     * @return string
     */
    public function getRedirectUrl($provider)
    {
        return 'urn:ietf:wg:oauth:2.0:oob';
        // $provider = strtolower($provider);
        // return $this['url']->route('connect', array('provider' => $provider), UrlGenerator::ABSOLUTE_URL);
    }
}
