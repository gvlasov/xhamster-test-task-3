<?php

namespace Tests\Helpers;

use DI\Container;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

abstract class FeatureTest extends TestCase
{

    protected static Container $di;

    protected static EntityManager $em;

    public function setUp(): void
    {
        parent::setUp();
        self::$di = self::getDiContainer();
        self::$em = self::$di->get(EntityManager::class);
    }

    protected static function getDiContainer(): Container
    {
        return include __DIR__ . '/../di.php';
    }

}
