<?php


namespace Net2h\OauthCdiscount;

use Closure;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;


class OauthCdiscount implements InterfaceUsine
{
    
    protected $config;

    
    protected $request;

    
    protected $initialDrivers = [
        'cdiscount' => 'Cdiscount',
    ];

    
    protected $drivers = [];

    
    public function __construct(array $config, Request $request = null)
    {
        $this->config = new Configuration($config);

        if ($this->config->has('guzzle')) {
            Fournisseur\FournisseurAbstrait::setGuzzleOptions($this->config->get('guzzle'));
        }

        if ($request) {
            $this->setRequest($request);
        }
    }

    
    public function config(Configuration $config): OauthCdiscount
    {
        $this->config = $config;

        return $this;
    }


    /**
     * @param $driver
     * @return InterfaceFournisseur
     */
    public function driver($driver): InterfaceFournisseur
    {
        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    
    public function setRequest(Request $request): OauthCdiscount
    {
        $this->request = $request;

        return $this;
    }

    
    public function getRequest(): Request
    {
        return $this->request ?? $this->createDefaultRequest();
    }

    
    protected function createDriver($driver): InterfaceFournisseur
    {
        if (isset($this->initialDrivers[$driver])) {
            $provider = $this->initialDrivers[$driver];
            $provider = __NAMESPACE__.'\\Fournisseur\\'.$provider.'Fournisseur';

            return $this->buildProvider($provider, $this->formatConfig($this->config->get($driver)));
        }

        throw new InvalidArgumentException("Pilote [$driver] non supporté.");
    }


    
    protected function createDefaultRequest(): Request
    {
        $request = Request::createFromGlobals();
        $session = new Session();

        $request->setSession($session);

        return $request;
    }


    
    public function getDrivers(): array
    {
        return $this->drivers;
    }

    
    public function buildProvider($provider, $config): InterfaceFournisseur
    {
        return new $provider(
            $this->getRequest(),
            $config['client_id'],
            $config['client_secret'],
            $config['redirect']
        );
    }

    
    public function formatConfig(?array $config): array
    {
        return array_merge([
            'identifier' => $config['client_id'],
            'secret' => $config['client_secret'],
            'callback_uri' => $config['redirect'],
        ], $config);
    }

    public static function cdiscountOauth(){
        $oauthCdiscount = new OauthCdiscount(Configuration::loadConfig());
        $response = $oauthCdiscount->driver('cdiscount')->redirect();
        $response->setStatusCode(Response::HTTP_TEMPORARY_REDIRECT);
        $response->headers->set('Content-Type', 'text/html');
        $response->send();
    }

}
