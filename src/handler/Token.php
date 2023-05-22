<?php

namespace handler;

use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;

session_start();
class Token
{
    public function getToken($role)
    {
        // Defaults to 'sha512'
        $signer  = new Hmac();
        // Builder object
        $builder = new Builder($signer);
        $now        = new \DateTimeImmutable();
        $issued     = $now->getTimestamp();
        $notBefore  = $now->modify('-1 minute')->getTimestamp();
        $expires    = $now->modify('+1 day')->getTimestamp();
        $passphrase = 'QcMpZ&b&mo3TPsPk668J6QH8JA$&U&m2';
        // Setup
        $builder
            ->setContentType('application/json')        // cty - header
            ->setExpirationTime($expires)               // exp
            ->setId('abcd123456789')                    // JTI id
            ->setIssuedAt($issued)                      // iat
            ->setIssuer('https://phalcon.io')           // iss
            ->setNotBefore($notBefore)                  // nbf
            ->setSubject($role)                // sub
            ->setPassphrase($passphrase);               // password
        $tokenObject = $builder->getToken();
        //token
        $token= $tokenObject->getToken();
    }
}
