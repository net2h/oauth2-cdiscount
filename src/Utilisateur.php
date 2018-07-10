<?php

namespace Net2h\OauthCdiscount;

use ArrayAccess;
use JsonSerializable;

/**
 * Class Utilisateur.
 */
class Utilisateur implements ArrayAccess, InterfaceUtilisateur, JsonSerializable
{
    use AttributsTrait;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Obtenir l'identifiant unique de l'utilisateur.
     *
     * @return string
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /**
     * Obtenir le nom d'utilisateur de l'utilisateur.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getAttribute('username', $this->getId());
    }

    /**
     * Obtenir le surnom / nom d'utilisateur pour l'utilisateur.
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->getAttribute('nickname');
    }

    /**
     * Obtenir le nom complet de l'utilisateur.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getAttribute('lastname');
    }


    public function getCivility()
    {
        return $this->getAttribute('civility');
    }


    public function getFirstName()
    {
        return $this->getAttribute('firstname');
    }

    public function getAddressLine1()
    {
        return $this->getAttribute('addressLine1');
    }

    public function getAddressLine2()
    {
        return $this->getAttribute('addressLine2');
    }

    public function getZipCode()
    {
        return $this->getAttribute('zipCode');
    }

    public function getCity()
    {
        return $this->getAttribute('city');
    }

    public function getCountry()
    {
        return $this->getAttribute('country');
    }
    public function getBirthdate()
    {
        return $this->getAttribute('birthdate');
    }

    public function getPhoneNumber()
    {
        return $this->getAttribute('phoneNumber');
    }

    /**
     * Obtenir l'adresse e-mail de l'utilisateur.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getAttribute('email');
    }

    /**
     * Obtenir l'URL de l'avatar / image pour l'utilisateur.
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->getAttribute('avatar');
    }

    /**
     * Définir le jeton sur l'utilisateur.
     *
     *
     * @param InterfaceToken $token
     * @return $this
     */
    public function setToken(InterfaceToken $token): Utilisateur
    {
        $this->setAttribute('token', $token);

        return $this;
    }

    /**
     * @param string $provider
     *
     * @return $this
     */
    public function setProviderName($provider): Utilisateur
    {
        $this->setAttribute('provider', $provider);

        return $this;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->getAttribute('provider');
    }

    /**
     * Obtenez le jeton autorisé.
     *
     */
    public function getToken()
    {
        return $this->getAttribute('token');
    }

    /**
     * Alias de getToken().
     *
     */
    public function getAccessToken()
    {
        return $this->getToken();
    }

    /**
     * Obtenir les attributs originaux.
     *
     */
    public function getOriginal()
    {
        return $this->getAttribute('original');
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return array_merge($this->attributes, ['token' => $this->token ? $this->token->getAttributes() : null]);
    }
}
