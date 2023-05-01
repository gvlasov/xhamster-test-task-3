<?php

namespace Gvlasov\XhamsterTestTask3;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Gedmo\Mapping\MappedEventSubscriber;

class UserModificationLogSubscriber extends MappedEventSubscriber
{

    public function __construct(
        protected UserModificationLog $log
    )
    {
        parent::__construct();
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
        ];
    }

    public function prePersist(PrePersistEventArgs $event)
    {
        $this->log->logCreation($event->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $this->log->logUpdate($event->getObject());
    }

    public function preRemove(PreRemoveEventArgs $event)
    {
        $this->log->logDeletion($event->getObject());
    }

    protected function getNamespace()
    {
        return __NAMESPACE__;
    }

}