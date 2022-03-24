<?php

namespace App\Doctrine;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class CreatedAtListener implements EventSubscriberInterface
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function setCreatedAt($object)
    {
        $object->setCreatedAt(new \DateTimeImmutable());
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!\method_exists($entity, 'setCreatedAt')) {
            return;
        }

        $this->setCreatedAt($entity);
    }
}
