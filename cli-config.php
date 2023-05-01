<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
require_once 'vendor/autoload.php';

// replace with mechanism to retrieve EntityManager in your app
$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: array(__DIR__ . "/src"),
    isDevMode: true,
);
$connection = DriverManager::getConnection(
    [
        'dbname' => 'xhamster',
        'user' => 'xhamster',
        'password' => '1',
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
    ],
    $config
);
$em = new EntityManager($connection, $config);

return ConsoleRunner::createHelperSet($em);
