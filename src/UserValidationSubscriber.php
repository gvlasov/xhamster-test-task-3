<?php

namespace Gvlasov\XhamsterTestTask3;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Gedmo\Mapping\MappedEventSubscriber;

class UserValidationSubscriber extends MappedEventSubscriber
{

    public function __construct(
        protected UserValidator $validator
    )
    {
        parent::__construct();
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(PrePersistEventArgs $event)
    {
        $this->validator->validate($event->getObject());
    }


    protected function getNamespace(): string
    {
        return __NAMESPACE__;
    }

}