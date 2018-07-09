<?php

namespace Net2h\OauthCdiscount\Fournisseur;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Net2h\OauthCdiscount\Token;
use Net2h\OauthCdiscount\InterfaceToken;
use Net2h\OauthCdiscount\ExceptionAutorisation;
use Net2h\OauthCdiscount\ExceptionEtatInvalide;
use Net2h\OauthCdiscount\InterfaceFournisseur;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class FournisseurAbstrait
 * @package Net2h\OauthCdiscount\Fournisseur
 */
abstract class FournisseurAbstrait implements InterfaceFournisseur
{

    /**
     * @var
     */
    protected $name;

    
    protected $request;

    
    protected $clientId;

    
    protected $clientSecret;


    protected $accessToken;

    
    protected $redirectUrl;

    
    protected $parameters = [];

    
    protected $scopes = [];

    
    protected $scopeSeparator = ',';

    
    protected $encodingType = PHP_QUERY_RFC1738;

    
    protected $stateless = false;

    
    protected static $guzzleOptions = ['http_errors' => false];


    /**
     * FournisseurAbstrait constructor.
     * @param Request $request
     * @param $clientId
     * @param $clientSecret
     * @param null $redirectUrl
     */
    public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl = null)
    {
        $this->request = $request;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
    }

    
    abstract protected function getAuthUrl($state);

    
    abstract protected function getTokenUrl();

    
    abstract protected function getUserByToken(InterfaceToken $token);

    
    abstract protected function mapUserToObject(array $user);

    
    public function redirect($redirectUrl = null)
    {
        $state = null;

        if (!is_null($redirectUrl)) {
            $this->redirectUrl = $redirectUrl;
        }

        if ($this->usesState()) {
            $state = $this->makeState();
        }

        return new RedirectResponse($this->getAuthUrl($state));
    }


    /**
     * @param InterfaceToken|null $token
     * @return mixed
     */
    public function user(InterfaceToken $token = null)
    {
        if (is_null($token) && $this->hasInvalidState()) {
            throw new ExceptionEtatInvalide();
        }

        $token = $token ?? $this->getAccessToken($this->getCode());

        $user = $this->getUserByToken($token);

        $user = $this->mapUserToObject($user)->merge(['original' => $user]);

        return $user->setToken($token)->setProviderName($this->getName());
    }


    /**
     * @param $redirectUrl
     * @return $this
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    
    public function withRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    
    public function setAccessToken(InterfaceToken $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    
    public function getAccessToken($code)
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $postKey = (1 === version_compare(ClientInterface::VERSION, '6')) ? 'form_params' : 'body';

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            $postKey => $this->getTokenFields($code),
        ]);

        return $this->parseAccessToken($response->getBody());
    }

    
    public function scopes(array $scopes)
    {
        $this->scopes = $scopes;

        return $this;
    }

    
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    
    public function getRequest()
    {
        return $this->request;
    }

    
    public function stateless()
    {
        $this->stateless = true;

        return $this;
    }

    
    public function with(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    
    public function getName()
    {
        if (empty($this->name)) {
            $this->name = strstr((new \ReflectionClass(get_class($this)))->getShortName(), 'Provider', true);
        }

        return $this->name;
    }

    
    protected function buildAuthUrlFromBase($url, $state)
    {
        return $url.'?'.http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);
    }

    
    protected function getCodeFields($state = null)
    {
        $fields = array_merge([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'response_type' => 'code',
        ], $this->parameters);

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        return $fields;
    }

    
    protected function formatScopes(array $scopes, $scopeSeparator)
    {
        return implode($scopeSeparator, $scopes);
    }

    
    protected function hasInvalidState()
    {
        if ($this->isStateless()) {
            return false;
        }

        $state = $this->request->getSession()->get('state');

        return !(strlen($state) > 0 && $this->request->get('state') === $state);
    }

    
    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];
    }

    
    protected function parseAccessToken($body)
    {
        if (!is_array($body)) {
            $body = json_decode($body, true);
        }

        if (empty($body['access_token'])) {
            throw new ExceptionAutorisation('Authorize Failed: '.json_encode($body, JSON_UNESCAPED_UNICODE), $body);
        }

        return new Token($body);
    }

    
    protected function getCode()
    {
        return $this->request->get('code');
    }

    
    protected function getHttpClient()
    {
        return new Client(self::$guzzleOptions);
    }

    
    public static function setGuzzleOptions($config = [])
    {
        return self::$guzzleOptions = $config;
    }

    
    protected function usesState()
    {
        return !$this->stateless;
    }

    
    protected function isStateless()
    {
        return $this->stateless;
    }

    
    protected function arrayItem(array $array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    
    protected function makeState()
    {
        $state = sha1(uniqid(mt_rand(1, 1000000), true));
        $session = $this->request->getSession();

        if (is_callable([$session, 'put'])) {
            $session->put('state', $state);
        } elseif (is_callable([$session, 'set'])) {
            $session->set('state', $state);
        } else {
            return false;
        }

        return $state;
    }
}
