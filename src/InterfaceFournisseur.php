<?php

namespace Net2h\OauthCdiscount;

interface InterfaceFournisseur
{
    /**
     * Rediriger l'utilisateur vers la page d'authentification du fournisseur.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect();

    /**
     * Obtenir l'instance utilisateur pour l'utilisateur authentifié.
     * @param InterfaceToken|null $token
     * @return Utilisateur
     */
    public function user(InterfaceToken $token = null);
}
