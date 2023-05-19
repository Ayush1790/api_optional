<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Response;

require './vendor/autoload.php';
$loader = new Loader();

$loader->registerNamespaces(
    [
        'MyApp\Models' => __DIR__ . '/models/',
    ]
);
$loader->register();

$container = new FactoryDefault();
$container->set(
    'mongo',
    function () {
        $mongo = new MongoDB\Client('mongodb+srv://myAtlasDBUser:myatlas-001@myatlas' .
            'clusteredu.aocinmp.mongodb.net/?retryWrites=true&w=majority');
        return $mongo->movies->movie;
    },
    true
);
$app = new Micro($container);

$app->get(
    '/api/movie',
    function () use ($app) {
        $movie = $this->mongo->find();

        foreach ($movie as $movies) {
            $data[] = [
                'id'   => $movies->_id,
                'name' => $movies->name,
                'year' =>  $movies->year
            ];
        }

        echo json_encode($data);
    }
);

// Searches for movie with $name in their name
$app->get(
    '/api/movie/search/{name}',
    function ($name) use ($app) {
        $name = str_replace("%20", " ", $name);
        $movie = $this->mongo->findOne(['name' => $name]);
        $data = [];
        $data[] = [
            'id'   => $movie->_id,
            'name' => $movie->name,
            'year' => $movie->year
        ];

        echo json_encode($data);
    }
);
$app->get(
    '/api/movie/{id:[0-9]+}',
    function ($id) use ($app) {
        $movie = $this->mongo->findOne(['id' => $id]);
        $response = new Response();
        if ($movie === false) {
            $response->setJsonContent(
                [
                    'status' => 'NOT-FOUND'
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    'status' => 'FOUND',
                    'data'   => [
                        'id'   => $movie->id,
                        'name' => $movie->name,
                        'year' => $movie->year,
                    ]
                ]
            );
        }
        return $response;
    }
);
$app->post(
    '/api/movie',
    function () use ($app) {
        $movie=json_decode(file_get_contents("php://input"));
        $status = $this->mongo->insertOne(['name' => $movie[0]->name, 'year' => $movie[0]->year, 'id' => $movie[0]->id]);
    }
);

$app->put(
    '/api/movie/{id:[0-9]+}',
    function ($id) use ($app) {
        $movies = $app->request->getJsonRawBody();
        $status = $this->mongo->updateOne(['id' => $id], ['$set' => ['name' => $movies[0]->name, 'year' => $movies[0]->year]]);
 
    }
);

// Deletes movie based on primary key
$app->delete(
    '/api/movie/{id:[0-9]+}',
    function ($id) use ($app) {
        $status = $this->mongo->deleteOne(['id' => $id]);

    }
);

$app->handle(
    $_SERVER["REQUEST_URI"]
);
