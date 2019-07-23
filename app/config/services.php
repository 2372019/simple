<?php

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Mvc\Dispatcher;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    
    return include APP_PATH . "/config/config.php";
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {

    $config = $this->getConfig();

    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Setting up the view component
 */
$di->setShared('view', function () {

    $config = $this->getConfig();

    $view = new View();
    $view->setDI($this);
    $view->setViewsDir($config->application->viewsDir);
    return $view;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {

    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    $connection = new $class($params);

    return $connection;
});


/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {

    return new MetaDataAdapter();
});

$di->set('dispatcher', function () {

    $dispatcher = new Dispatcher();
    return $dispatcher;
});

/**
 * Register the session flash service with the Twitter Bootstrap classes
 */
$di->set('flash', function () {

    return new Flash([
        'error'   => 'alert alert-error',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-notice',
        'warning' => 'alert alert-warning'
    ]);
});

$di->set('flashSession', function () {

    return new FlashSession([
        'error'   => 'alert alert-error',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-notice',
        'warning' => 'alert alert-warning'
    ]);
});


/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function () {

    $session = new SessionAdapter();
    $session->start();

    return $session;
});

$di->setShared('elements', function () {

	return new Elements();
});

/**
 * Trả về 1 mảng của file privateResources.php
 */
$di->setShared('aclResource', function() {

    $pr = [];

    if (is_readable(APP_PATH . '/config/privateResources.php')) {
        $pr = include APP_PATH . '/config/privateResources.php';
    }
    return $pr;
});

/**
 * Đăng ký Acl và dùng để phân quyền cho user
 */
$di->setShared('acl', function () {
    
    $acl = new Acl();
    $pr = $this->getShared('aclResource')->privateResources->toArray();
    $acl->addPrivateResources($pr);
    return $acl;
});