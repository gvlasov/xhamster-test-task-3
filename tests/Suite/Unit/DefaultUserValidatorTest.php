<?php

namespace Tests\Suite\Unit;

use Carbon\Carbon;
use Gvlasov\XhamsterTestTask3\DefaultUserValidator;
use Gvlasov\XhamsterTestTask3\User;
use Gvlasov\XhamsterTestTask3\ValidationException;
use PHPUnit\Framework\TestCase;
use Tests\Helpers\DomainGoogleComIsProhibited;
use Tests\Helpers\WordBollocksIsProhibited;

class DefaultUserValidatorTest extends TestCase
{

    public function testNameMustBeAtLeast8CharactersLong()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Name must be at least 8 characters long');
        $user = new User();
        $user->setName('asdfdf');
        $user->setEmail('asdf@asdf.com');
        $user->setCreated(Carbon::now('UTC'));
        $this->getValidator()->validate($user);
    }

    public function testNameIsRequired()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Name is required');
        $user = new User();
        $user->setEmail('asdf@asdf.com');
        $user->setCreated(Carbon::now('UTC'));
        $this->getValidator()->validate($user);
    }

    public function testNameMustBeLowercaseAlphanumeric(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Name must be lowercase alphanumeric');
        $user = new User();
        $user->setName('Mc&Donalds');
        $user->setEmail('frosty@chilly.com');
        $user->setNotes('notes...');
        $user->setCreated(Carbon::now('UTC'));
        $this->getValidator()->validate($user);
    }

    public function testNameMustNotContainProhibitedWords(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Name must not contain prohibited words');
        $user = new User();
        $user->setName('bollocks69');
        $user->setEmail('frosty@chilly.com');
        $user->setNotes('notes...');
        $user->setCreated(Carbon::now('UTC'));
        $this->getValidator()->validate($user);
    }

    public function testEmailIsRequired(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Email is required');
        $user = new User();
        $user->setName('asdfadsf');
        $user->setNotes('notes...');
        $user->setCreated(Carbon::now('UTC'));
        $this->getValidator()->validate($user);
    }

    public function testEmailMustBeValid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Email must be a valid email');
        $user = new User();
        $user->setName('asdfadsf');
        $user->setEmail('email');
        $user->setNotes('notes...');
        $user->setCreated(Carbon::now('UTC'));
        $this->getValidator()->validate($user);
    }

    public function testEmailMustBeOnTrustedDomain(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Email must be on trusted domain');
        $user = new User();
        $user->setName('asdfadsf');
        $user->setEmail('email@google.com');
        $user->setNotes('notes...');
        $user->setCreated(Carbon::now('UTC'));
        $this->getValidator()->validate($user);
    }

    public function testDeletedCantBeLessThanCreated(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Deleted time must be >= created time');
        $user = new User();
        $user->setName('frosty123');
        $user->setEmail('frosty@chilly.com');
        $user->setNotes('notes...');
        $user->setCreated(Carbon::now('UTC'));
        $user->setDeleted(Carbon::now('UTC')->subSeconds(10));
        $this->getValidator()->validate($user);
    }

    protected function getValidator(): DefaultUserValidator
    {
        return (new DefaultUserValidator(
            new WordBollocksIsProhibited(),
            new DomainGoogleComIsProhibited()
        ));
    }
}

