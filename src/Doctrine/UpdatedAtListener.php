<?php

namespace App\Doctrine;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class UpdatedAtListener implements EventSubscriberInterface
{
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
            Events::prePersist,
        ];
    }

    public function setUpdatedAt($object)
    {
        $object->setUpdatedAt(new \DateTimeImmutable());
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!\method_exists($entity, 'setUpdatedAt')) {
            return;
        }

        $this->setUpdatedAt($entity);

        $em = $args->getEntityManager();
        $meta = $em->getClassMetadata(\get_class($entity));
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $entity);
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!\method_exists($entity, 'setUpdatedAt')) {
            return;
        }

        $this->setUpdatedAt($entity);
    }
}
