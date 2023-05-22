<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Acl\Adapter\Memory;
use handler\Token;

require './vendor/autoload.php';
$loader = new Loader();

$loader->registerNamespaces(
    [
        'MyApp\Models' => __DIR__ . '/models/',
        'handler' => __DIR__ . '/handler/'
    ]
);
$loader->register();

$container = new FactoryDefault();
$container->set(
    'mongo',
    function () {
        $mongo = new MongoDB\Client('mongodb+srv://myAtlasDBUser:myatlas-001@myatlas' .
            'clusteredu.aocinmp.mongodb.net/?retryWrites=true&w=majority');
        return $mongo->products->product;
    },
    true
);
// Create a events manager
$eventsManager = new EventsManager();

$eventsManager->attach(
    'micro:beforeExecuteRoute',
    function (Event $event, $app) {
        $acl = new Memory();

        /**
         * Add the roles
         */
        $acl->addRole('user');
        $acl->addRole('admin');
        $acl->addComponent(
            'products',
            []
        );
        $acl->allow('admin', 'products', '*');
        $obj = new Token();
        $token = $obj->getToken($app->request->get('role'));
        $token = $obj->decodeToken($token);
        if (!$acl->isAllowed($token, 'products', '*')) {
            echo "You are not authorised to view this.";
            die;
        }
    }
);
$app = new Micro($container);

// Bind the events manager to the app
$app->setEventsManager($eventsManager);


// Searches for product with $name in their name
$app->get(
    '/products',
    function () use ($app) {
        if (array_key_exists('per_page', $this->request->get())) {
            $per_page = $this->request->get('per_page');
        } else {
            $per_page = 2;
        }
        if (array_key_exists('page', $this->request->get())) {
            $page = $this->request->get('page');
        } else {
            $page = 0;
        }
        $product = $this->mongo->find([], ["limit" => (int)$per_page, "skip" => (int)$per_page * $page]);
        foreach ($product as $value) {
            $result[] = [
                'id'   =>  $value->_id,
                'name' =>  $value->name,
                'price' => $value->price,
                'color' => $value->color,
            ];
        }
        echo json_encode($result);
    }
);
$app->get(
    '/products/search/{name}',
    function ($name) use ($app) {
        $product = $this->mongo->find();
        $data = array();
        $data = explode("%20", $name);
        foreach ($product as $products) {
            foreach ($data as $value) {
                $pattern = "/$value/i";
                if (preg_match_all($pattern, $products->name)) {
                    $result[] = [
                        'id'   =>  $products->_id,
                        'name' =>  $products->name,
                        'price' => $products->price,
                        'color' => $products->color,
                    ];
                }
            }
        }
        if (empty($result)) {
            echo "data not matched";
        } else {
            echo json_encode($result);
        }
    }
);

$app->notFound(
    function () use ($app) {
        $app->response->setStatusCode(404, 'Not Found');
        $app->response->sendHeaders();

        $message = 'Nothing to see here. Move along....';
        $app->response->setContent($message);
        $app->response->send();
    }
);

$app->handle(
    $_SERVER["REQUEST_URI"]
);
