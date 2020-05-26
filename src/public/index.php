<?php


/*error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
});*/

require './vendor/autoload.php';
require_once 'config.php';

use \DareOne\auth\Authenticator;


$app = new \Slim\App(["settings" => $config]);




$container=$app->getContainer();

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('templates', [
        'cache' => false
    ]);
    return $view;
};

$container["auth"]=function ($container) {
    $auth = new \DareOne\auth\Authenticator($container);
    return $auth;
};




$container["baseurl"]=$config["baseurl"];

$container['view']->twigTemplateDirs = array(
    './templates/viewer'
);

// Comment this area for slim error
/*
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['view']->render($response->withStatus(404), 'viewer/404.twig', [
            "message" => "The requested URL was not found on this server"
        ]);
    };
};

$container['errorHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['view']->render($response->withStatus(500), 'viewer/500.twig', [
            "message" => "An internal error has occurred. "
        ]);
    };
};

$container['phpErrorHandler'] = function ($container) {
    return $container['errorHandler'];
};
*/
// Service factory for the ORM, move into bootstrap file
$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();


/**
 * PRESENTATION ROUTES
 */


$app->get('/', '\DareOne\controllers\viewer\BibController:showBibliography');




/**
 * MANAGER
 */
$app->group('/manager', function() use ($app){
    $app->get('', '\DareOne\controllers\manager\ManagerController:showManager');
    $app->group('/bibliography', function() use ($app){
        $app->get('', '\DareOne\controllers\manager\BibController:showBibliography');
        $app->post('', '\DareOne\controllers\manager\BibController:filterBibliography');
        $app->post('/new', '\DareOne\controllers\manager\BibController:addEntry');
        $app->get('/{id}', '\DareOne\controllers\manager\BibController:showEntry');
        $app->put('/{id}', '\DareOne\controllers\manager\BibController:updateEntry');
        $app->post('/{id}/category', '\DareOne\controllers\manager\BibController:addCategory');
        $app->delete('/{id}/category/{cat_id}', '\DareOne\controllers\manager\BibController:deleteCategory');
        $app->post('/{id}/person', '\DareOne\controllers\manager\BibController:addPerson');
        $app->post('/{bid}/person/{id}', '\DareOne\controllers\manager\BibController:updatePerson');
        $app->delete('/{id}/person/{person_id}', '\DareOne\controllers\manager\BibController:deletePerson');
        $app->put('/{id}/book', '\DareOne\controllers\manager\BibController:updateBook');
        $app->put('/{id}/booksection', '\DareOne\controllers\manager\BibController:updateBooksection');
        $app->put('/{id}/article', '\DareOne\controllers\manager\BibController:updateArticle');
    });
    $app->group('/persons', function () use ($app){
        $app->get('', '\DareOne\controllers\manager\PersonController:showPersons');
        $app->post('', '\DareOne\controllers\manager\PersonController:filterPersons');
        $app->post('/new', '\DareOne\controllers\manager\PersonController:addPerson');
        $app->get('/{id}', '\DareOne\controllers\manager\PersonController:showPerson');
        $app->put('/{id}', '\DareOne\controllers\manager\PersonController:updatePerson');
    });
    $app->group('/categories', function () use ($app){
        $app->get('', '\DareOne\controllers\manager\CategoryController:showCategories');
        $app->post('', '\DareOne\controllers\manager\CategoryController:filterCategories');
        $app->post('/new', '\DareOne\controllers\manager\CategoryController:addCategory');
        $app->get('/{id}', '\DareOne\controllers\manager\CategoryController:showCategory');
        $app->put('/{id}', '\DareOne\controllers\manager\CategoryController:updateCategory');
    });


})->add(Authenticator::class . ':authenticate');


/**
 * API
 */
$app->group('/api', function () use ($app){
    $app->group('/db', function () use ($app){
        $app->get('/bib', '\DareOne\controllers\ApiController:getBib');
        $app->get('/bib/{id}', '\DareOne\controllers\ApiController:getBibById');
    });
    $app->group('/index', function () use ($app){
        $app->get('/bib', '\DareOne\controllers\ApiController:getBibIndex');
        $app->get('/bib/{id}', '\DareOne\controllers\ApiController:getBibIndexDocumentById');
    });


});


/**
 * AUTH
 */
$app->get('/login', '\DareOne\controllers\AuthController:getLogin');
$app->post('/login', '\DareOne\controllers\AuthController:login');
$app->post('/logout', Authenticator::class . ':logout');

//$app->post('/admin/createUser', '\DareOne\controllers\AuthController:createUser');
/**
 * USER
 */
$app->group('/user', function() use ($app){
    $app->get('/myprofile', '\DareOne\controllers\UserController:showProfile');
    $app->put('/myprofile', '\DareOne\controllers\UserController:updateProfile');
})->add(Authenticator::class . ':authenticate');

$app->group('/admin', function() use ($app){
    $app->get('', '\DareOne\controllers\AdminController:showOverview');
    $app->get('/user', '\DareOne\controllers\AdminController:showUser');
    $app->post('/user', '\DareOne\controllers\AdminController:createUser');
    $app->put('/user/{id}', '\DareOne\controllers\AdminController:updateUser');
    $app->get('/logs', '\DareOne\controllers\AdminController:showLogs');
    $app->get('/tests', '\DareOne\controllers\AdminController:showTests');
    $app->get('/indices', '\DareOne\controllers\AdminController:showIndices');
    $app->put('/indices/bib', '\DareOne\controllers\AdminController:resetBibIndex');

})->add(Authenticator::class . ':authenticate');

$app->run();


