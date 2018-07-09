<?php


namespace Net2h\OauthCdiscount;

/**
 * Interface InterfaceToken.
 */
interface InterfaceToken
{
    /**
     * Retourne le token d'accès
     *
     * @return string
     */
    public function getToken(): ?string ;
}
