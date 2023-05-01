<?php

namespace Tests\Suite\Feature;

use Doctrine\ORM\EntityManager;
use Gvlasov\XhamsterTestTask3\User;
use Gvlasov\XhamsterTestTask3\UserValidator;
use Tests\Helpers\FeatureTest;

class UserValidationSubscriberTest extends FeatureTest
{

    public function testCallsValidatePrePersist()
    {
        $validator = $this->createMock(UserValidator::class);
        $validator->expects($this->atLeastOnce())->method('validate');
        self::$di->set(UserValidator::class, $validator);
        self::$em = self::$di->make(EntityManager::class);
        self::$em->persist(new User);
    }

}

