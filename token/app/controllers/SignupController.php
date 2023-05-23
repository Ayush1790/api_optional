<?php

namespace MyApp\Controller;

use Phalcon\Mvc\Controller;
use Phalcon\Escaper;
use MyApp\Controller\IndexController;

class SignupController extends Controller
{
    public function indexAction()
    {
        //redirect to view
    }

    public function registerAction()
    {
        $escaper = new Escaper();
        $data = array(
            'name' =>  $escaper->escapeHtml($this->request->getPost('name')),
            'email' => $escaper->escapeHtml($this->request->getPost('email')),
            'pswd' =>  $escaper->escapeHtml($this->request->getPost('pswd')),
            'pincode' => $escaper->escapeHtml($this->request->getPost('pincode')),
            'role' => 'user'
        );
        // set post fields
        $ch = curl_init();
        $url = 'http://172.21.0.2/adduser';
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // execute!
        curl_exec($ch);
        $index=new IndexController();
        $token=$index->getTokenAction('user');
        session_start();
        $_SESSION['token']=$token;
        $this->response->redirect('login');
    }
}
