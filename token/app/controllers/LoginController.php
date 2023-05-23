<?php

namespace MyApp\Controller;

use Phalcon\Mvc\Controller;

class LoginController extends Controller
{
    public function indexAction()
    {
        //redirect to view
    }

    public function loginAction()
    {
        $email = $this->request->getPost('email');
        $pswd = $this->request->getPost('pswd');
        // set post fields
        $ch = curl_init();
        $url = 'http://172.21.0.2/findUser?email=' . $email . '&pswd=' . $pswd;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        // execute!
        $response = json_decode(curl_exec($ch));
        if ($response->role == 'user') {
            $this->response->redirect('userToken');
        } elseif ($response->role == 'admin') {
            $this->response->redirect('order');
        } else {
            echo "Wrong email or password";
            echo $this->tag->linkTo('login/index', ' Go Back');
        }
    }
}
