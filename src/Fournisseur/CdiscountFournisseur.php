<?php


namespace Net2h\OauthCdiscount\Fournisseur;

use GuzzleHttp\ClientInterface;
use Net2h\OauthCdiscount\InterfaceToken;
use Net2h\OauthCdiscount\InterfaceFournisseur;
use Net2h\OauthCdiscount\Utilisateur;


class CdiscountFournisseur extends FournisseurAbstrait implements InterfaceFournisseur
{

    protected $scopeSeparator = ' ';


    protected $scopes = [
        'user_profile',
        'user_profile.phone',
        'user_profile.phoneNumber',
        'user_profile.address',
        'user_profile.birthdate',
        'user_profile.HasCdav'
    ];


    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://auth.cdiscount.com/v1/authorize', $state);
    }


    protected function getTokenUrl()
    {
        return 'https://auth.cdiscount.com/v1/token';
    }


    public function getAccessToken($code)
    {
        $postKey = (1 === version_compare(ClientInterface::VERSION, '6')) ? 'form_params' : 'body';

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            $postKey => $this->getTokenFields($code),
        ]);

        return $this->parseAccessToken($response->getBody());
    }


    protected function getTokenFields($code)
    {
        return parent::getTokenFields($code) + ['grant_type' => 'authorization_code'];
    }


    protected function getUserByToken(InterfaceToken $token)
    {
        $response = $this->getHttpClient()->get('https://orchestration.cdiscount.com/CConnect/GetCustomer', [
            'query' => [
                'prettyPrint' => 'false',
            ],
            'headers' => [
                'X-CDS-APPVERSION' => '3.2.0',
                'X-CDS-SITEID' => '100',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token->getToken(),
            ],
        ]);

        return json_decode($response->getBody(), true);
    }


    protected function mapUserToObject(array $user): Utilisateur
    {

        return new Utilisateur([
            'id' => $this->arrayItem($user, 'CustomerGuid'),
            'civility' => $this->arrayItem($user, 'Civility'),
            'firstname' => $this->arrayItem($user, 'FirstName'),
            'lastname' => $this->arrayItem($user, 'LastName'),
            'email' => $this->arrayItem($user, 'Email'),
            'hasCdav' => $this->arrayItem($user, 'HasCdav'),
            'addressLine1' => $this->arrayItem($user['Address'], 'AddressLine1'),
            'addressLine2' => $this->arrayItem($user['Address'], 'AddressLine2'),
            'zipCode' => $this->arrayItem($user['Address'], 'ZipCode'),
            'city' => $this->arrayItem($user['Address'], 'City'),
            'country' => $this->arrayItem($user['Address'], 'Country'),
            'birthdate' => $this->arrayItem($user, 'Birthdate'),
            'phoneNumber' => $this->arrayItem($user, 'PhoneNumber'),
            'phone' => $this->arrayItem($user, 'MobilePhoneNumber'),
        ]);
    }
}
