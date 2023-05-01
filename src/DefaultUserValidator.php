<?php

namespace Gvlasov\XhamsterTestTask3;

class DefaultUserValidator implements UserValidator
{

    public function __construct(
        protected ProhibitedWords $prohibitedWords,
        protected TrustedDomains  $trustedDomains
    )
    {
    }

    public function validate(User $user)
    {
        if (!$user->hasName()) {
            throw new ValidationException('Name is required');
        }
        if (!$user->hasEmail()) {
            throw new ValidationException('Email is required');
        }
        if (strlen($user->getName()) < 8) {
            throw new ValidationException('Name must be at least 8 characters long');
        }
        if (!preg_match('/^[a-z0-9]+$/', $user->getName())) {
            throw new ValidationException('Name must be lowercase alphanumeric');
        }
        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Email must be a valid email');
        }
        if ($this->prohibitedWords->hasProhibitedWords($user->getName())) {
            throw new ValidationException('Name must not contain prohibited words');
        }
        if (!$this->trustedDomains->isDomainTrusted($user->getEmailDomain())) {
            throw new ValidationException('Email must be on trusted domain');
        }
        if (
            $user->getDeleted() !== null
            && $user->getDeleted()->getTimestamp() < $user->getCreated()->getTimestamp()
        ) {
            throw new ValidationException('Deleted time must be >= created time');
        }
    }

}