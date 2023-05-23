<?php

namespace MyApp\Controller;

use Phalcon\Mvc\Controller;

class UserTokenController extends Controller
{
    public function indexAction()
    {
        session_start();
        echo "Your Token is <h1>$_SESSION[token]</h1>";
    }
}
