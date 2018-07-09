<?php

namespace Net2h\OauthCdiscount;

class ExceptionAutorisation extends \RuntimeException
{
    
    public $body;

    
    public function __construct($message, $body)
    {
        parent::__construct($message, -1);

        $this->body = $body;
    }
}
