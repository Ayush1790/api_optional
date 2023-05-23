<?php

namespace MyApp\Controller;

use Phalcon\Mvc\Controller;

class OrderController extends Controller
{
    public function indexAction()
    {
        session_start();
        $ch = curl_init();
        $url = 'http://172.21.0.2/allOrders?role='.$_SESSION['role'];
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        // execute!
        $response = json_decode(curl_exec($ch), true);
        $this->view->data = $response;
    }
}
