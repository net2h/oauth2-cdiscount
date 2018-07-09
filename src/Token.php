<?php


namespace Net2h\OauthCdiscount;

use ArrayAccess;
use InvalidArgumentException;
use JsonSerializable;


class Token implements InterfaceToken, ArrayAccess, JsonSerializable
{
    use AttributsTrait;

    public function __construct(array $attributes)
    {
        if (empty($attributes['access_token'])) {
            throw new InvalidArgumentException('La clef "access_token" ne peut être vide.');
        }

        $this->attributes = $attributes;
    }

    
    public function getToken(): ?string
    {
        return $this->getAttribute('access_token');
    }

    
    public function __toString(): string
    {
        return strval($this->getAttribute('access_token', ''));
    }

    
    public function jsonSerialize(): ?string
    {
        return $this->getToken();
    }
}
