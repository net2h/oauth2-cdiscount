<?php


namespace Net2h\OauthCdiscount;

/**
 * Interface InterfaceUtilisateur.
 */
interface InterfaceUtilisateur
{
    /**
     * Obtenir l'identifiant unique de l'utilisateur.
     *
     * @return string
     */
    public function getId();

    /**
     * Obtenir le surnom / nom d'utilisateur pour l'utilisateur.
     *
     * @return string
     */
    public function getNickname();

    /**
     * Obtenir le nom complet de l'utilisateur.
     *
     * @return string
     */
    public function getName();

    /**
     * Obtenir l'adresse e-mail de l'utilisateur.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Obtenir l'URL de l'avatar / image pour l'utilisateur.
     *
     * @return string
     */
    public function getAvatar();
}
