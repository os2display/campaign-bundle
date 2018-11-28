<?php

namespace Itk\CampaignBundle\Event;

use Doctrine\ORM\EntityManagerInterface;
use Itk\CampaignBundle\Entity\Campaign;
use Os2Display\CoreBundle\Entity\Channel;
use Os2Display\CoreBundle\Events\CleanupEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CleanupSubscriber
 * @package Itk\CampaignBundle\Event
 */
class CleanupSubscriber implements EventSubscriberInterface
{
    protected $entityManager;

    /**
     * CleanupSubscriber constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Subscribed events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CleanupEvent::EVENT_CLEANUP_CHANNELS => 'ignoreCampaignChannel',
        ];
    }

    /**
     * Make sure campaign channels are not removed.
     *
     * @param \Os2Display\CoreBundle\Events\CleanupEvent $event
     * @return \Os2Display\CoreBundle\Events\CleanupEvent
     */
    public function ignoreCampaignChannel(CleanupEvent $event)
    {
        $entities = $event->getEntities();

        foreach ($entities as $key => $channel) {
            $qb = $this->entityManager->createQueryBuilder();
            $query = $qb->select('entity')
                ->from(Campaign::class, 'entity')
                ->where(':channel member of entity.channels')
                ->setParameter('channel', $channel);

            if (count($query->getQuery()->getResult()) > 0) {
                unset($entities[$key]);
            }
        }

        $event->setEntities($entities);

        return $event;
    }
}
