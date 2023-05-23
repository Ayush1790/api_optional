<?php

namespace MyApp\Controller;

use Phalcon\Mvc\Controller;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;

class IndexController extends Controller
{
    public function indexAction()
    {
        $this->response->redirect('signup');
    }

    public function getTokenAction($role)
    {
        $signer  = new Hmac();

        // Builder object
        $builder = new Builder($signer);

        $now        = new \DateTimeImmutable();
        $issued     = $now->getTimestamp();
        $notBefore  = $now->modify('-1 minute')->getTimestamp();
        $expires    = $now->modify('+1 day')->getTimestamp();
        $passphrase = 'QcMpZ&b&mo3TPsPk668J6QH8JA$&U&m2';
        // $role = $this->request->getPost('role');
        // Setup
        $builder
            ->setAudience('https://target.phalcon.io')  // aud
            ->setContentType('application/json')        // cty - header
            ->setExpirationTime($expires)               // exp
            ->setId('abcd123456789')                    // JTI id
            ->setIssuedAt($issued)                      // iat
            ->setIssuer('https://phalcon.io')           // iss
            ->setNotBefore($notBefore)                  // nbf
            ->setSubject($role)                         // sub
            ->setPassphrase($passphrase)                // password
        ;

        // Phalcon\Security\JWT\Token\Token object
        $tokenObject = $builder->getToken();

        // The token
        return $tokenObject->getToken();
    }

    public function decodeTokenAction()
    {
        $parser = new Parser();
        $token = $this->request->getPost('token');
        $tokenObject = $parser->parse($token);
        $now = new \DateTimeImmutable();
        $expirs = $now->getTimestamp();
        $validator = new Validator($tokenObject, 100);
        $validator->validateExpiration($expirs);
        $claims = $tokenObject->getClaims()->getPayload();
        return $claims['sub'];
    }
}
