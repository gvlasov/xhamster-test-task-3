<?php

use DI\Container;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;
use Gvlasov\XhamsterTestTask3\DefaultUserValidator;
use Gvlasov\XhamsterTestTask3\ProhibitedWords;
use Gvlasov\XhamsterTestTask3\TrustedDomains;
use Gvlasov\XhamsterTestTask3\User;
use Gvlasov\XhamsterTestTask3\UserModificationLog;
use Gvlasov\XhamsterTestTask3\UserValidator;
use Tests\Helpers\DomainGoogleComIsProhibited;
use Tests\Helpers\NullUserModificationLog;
use Tests\Helpers\WordBollocksIsProhibited;

$container = new Container();
$container->set(ProhibitedWords::class, new WordBollocksIsProhibited());
$container->set(TrustedDomains::class, new DomainGoogleComIsProhibited());
$container->set(UserModificationLog::class, \DI\autowire(NullUserModificationLog::class));
$container->set(UserValidator::class, \DI\autowire(DefaultUserValidator::class));
$container->set(EntityManager::class, function (Container $di) {
    $env = parse_ini_file('.env');
    $config = ORMSetup::createAttributeMetadataConfiguration(
        paths: array(__DIR__ . "/src"),
        isDevMode: true,
    );
    $config->addFilter('soft-deletable', SoftDeleteableFilter::class);
    $connection = DriverManager::getConnection(
        [
            'dbname' => $env['DB_NAME'],
            'user' => $env['DB_USER'],
            'password' => $env['DB_PASSWORD'],
            'host' => $env['DB_HOST'],
            'driver' => 'pdo_mysql',
        ],
        $config
    );
    $em = new EntityManager($connection, $config);
    $validator = $di->get(UserValidator::class);
    User::registerSubscribers(
        $em,
        $validator,
        $di->get(UserModificationLog::class)
    );
    return $em;
});
return $container;
