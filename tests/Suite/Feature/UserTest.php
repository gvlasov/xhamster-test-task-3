<?php

namespace Tests\Suite\Feature;

use Carbon\Carbon;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Gvlasov\XhamsterTestTask3\User;
use Gvlasov\XhamsterTestTask3\UserModificationLog;
use Gvlasov\XhamsterTestTask3\ValidationException;
use Tests\Helpers\FeatureTest;

class UserTest extends FeatureTest
{

    public function setUp(): void
    {
        parent::setUp();
        self::$em = self::$di->get(EntityManager::class);
        self::$em->getConnection()->executeQuery('TRUNCATE TABLE users');
    }

    public function testUserCanBeCreatedWithValidData(): void
    {
        $startTime = Carbon::now();
        $user = new User();
        $user->setName('frosty123');
        $user->setEmail('frosty@chilly.com');
        $user->setNotes('notes...');
        $user->setCreated(Carbon::now('UTC'));
        self::$em->persist($user);
        self::$em->flush();
        self::$em->refresh($user);
        $this->assertGreaterThanOrEqual(
            $startTime->format('Y-m-d H:i:s'),
            $user->getCreated()
        );
    }

    public function testUserCanBeLoadedFromDb(): void
    {
        // Load a single user
        self::$em->createNativeQuery('insert into users (name, email, created, deleted, notes) values(\'frosty123\', \'frosty@chilly.com\', NOW(), null, null)', new ResultSetMapping())->execute();
        $user = self::$em->getRepository(User::class)->findAll()[0];
        $oldEmail = $user->getEmail();
        $user->setEmail($oldEmail . '1');
        self::$em->persist($user);
        self::$em->flush($user);
        $this->assertEquals(
            $oldEmail . '1',
            self::$em->find(User::class, $user->getId())->getEmail()
        );
    }

    public function testUserValidationRulesAreApplied(): void
    {
        $this->expectException(ValidationException::class);
        $user = new User();
        self::$em->persist($user);
    }

    public function testUserCanBeUpdatedWithValidData(): void
    {
        {
            $user = new User();
            $user->setName('frosty123');
            $user->setEmail('old@email.com');
            $user->setNotes('notes...');
            $user->setCreated(Carbon::now('UTC'));
            self::$em->persist($user);
            self::$em->flush();
            self::$em->refresh($user);
        }
        $newEmail = 'new@email.com';
        $user->setEmail($newEmail);
        self::$em->persist($user);
        self::$em->flush();

        $this->assertEquals(
            $newEmail,
            self::$em->find(User::class, $user->getId())->getEmail()
        );
    }

    public function testNameMustBeUnique(): void
    {
        $this->expectException(UniqueConstraintViolationException::class);
        {
            $user1 = new User();
            $user1->setName('asdfasdf');
            $user1->setEmail('frosty@chilly.com');
            $user1->setNotes('notes...');
            $user1->setCreated(Carbon::now('UTC'));
            self::$em->persist($user1);
        }
        $user2 = new User();
        $user2->setName('asdfasdf');
        $user2->setEmail('hello@goodbye.com');
        $user2->setNotes('notes...');
        $user2->setCreated(Carbon::now('UTC'));
        self::$em->persist($user2);
        self::$em->flush($user2);
    }

    public function testEmailMustBeUnique(): void
    {
        $this->expectException(UniqueConstraintViolationException::class);
        {
            $user1 = new User();
            $user1->setName('asdfasdf');
            $user1->setEmail('frosty@chilly.com');
            $user1->setNotes('notes...');
            $user1->setCreated(Carbon::now('UTC'));
            self::$em->persist($user1);
        }
        $user2 = new User();
        $user2->setName('asdfasdf2');
        $user2->setEmail('frosty@chilly.com');
        $user2->setNotes('notes...');
        $user2->setCreated(Carbon::now('UTC'));
        self::$em->persist($user2);
        self::$em->flush($user2);
    }

    public function testCanBeSoftDeleted(): void
    {
        {
            $user = new User();
            $user->setName('frosty123');
            $user->setEmail('frosty@chilly.com');
            $user->setNotes('notes...');
            $user->setCreated(Carbon::now('UTC'));
            self::$em->persist($user);
            self::$em->flush();
            self::$em->refresh($user);
        }
        $userId = $user->getId();
        $this->assertNotNull(
            self::$em->find(User::class, $userId)
        );
        self::$em->remove($user);
        self::$em->flush();
        $this->assertCount(
            0,
            self::$em->getRepository(User::class)->findBy([
                'deleted' => null,
                'id' => $userId,
            ])
        );
        $deletedUser = self::$em->find(User::class, $userId);
        $this->assertNotNull($deletedUser);
        $this->assertNotNull($deletedUser->getDeleted());
    }

    public function testDeletedIsNullForNonDeletedActiveUser(): void
    {
        $user = new User();
        $user->setName('frosty123');
        $user->setEmail('frosty@chilly.com');
        $user->setNotes('notes...');
        $user->setCreated(Carbon::now('UTC'));
        self::$em->persist($user);
        self::$em->flush();
        self::$em->refresh($user);
        $this->assertNull($user->getDeleted());
    }

    public function testLogsUserCreation(): void
    {
        $log = $this->createMock(UserModificationLog::class);
        $log->expects($this->once())->method('logCreation');
        $log->expects($this->never())->method('logUpdate');
        $log->expects($this->never())->method('logDeletion');
        self::$di->set(UserModificationLog::class, $log);
        self::$em->close();
        self::$em = self::$di->make(EntityManager::class);
        $user = new User();
        $user->setName('frosty123');
        $user->setEmail('frosty@chilly.com');
        $user->setNotes('notes...');
        $user->setCreated(Carbon::now('UTC'));
        self::$em->persist($user);
        self::$em->flush();
    }

    public function testLogsUserUpdate(): void
    {
        // Load a single user
        self::$em->createNativeQuery('insert into users (name, email, created, deleted, notes) values(\'frosty123\', \'frosty@chilly.com\', NOW(), null, null)', new ResultSetMapping())->execute();
        // Set up UserChangeLog to count its method executions
        $log = $this->createMock(UserModificationLog::class);
        $log->expects($this->never())->method('logCreation');
        $log->expects($this->once())->method('logUpdate');
        $log->expects($this->never())->method('logDeletion');
        self::$di->set(UserModificationLog::class, $log);
        self::$em->close();
        self::$em = self::$di->make(EntityManager::class);

        $user = self::$em->getRepository(User::class)->findAll()[0];
        $user->setName('frosty1239999');
        self::$em->persist($user);
        self::$em->flush();
    }

    public function testLogsUserDeletion(): void
    {
        // Load a single user
        self::$em->createNativeQuery('insert into users (name, email, created, deleted, notes) values(\'frosty123\', \'frosty@chilly.com\', NOW(), null, null)', new ResultSetMapping())->execute();
        // Set up UserChangeLog to count its method executions
        $log = $this->createMock(UserModificationLog::class);
        $log->expects($this->never())->method('logCreation');
        $log->expects($this->never())->method('logUpdate');
        $log->expects($this->once())->method('logDeletion');
        self::$di->set(UserModificationLog::class, $log);

        self::$em->close();
        self::$em = self::$di->make(EntityManager::class);

        $user = self::$em->getRepository(User::class)->findAll()[0];
        self::$em->remove($user);
    }

}
