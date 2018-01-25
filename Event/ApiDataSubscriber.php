<?php

namespace Itk\CampaignBundle\Event;

use Doctrine\ORM\EntityManagerInterface;
use Itk\CampaignBundle\Entity\Campaign;
use Os2Display\CoreBundle\Entity\Screen;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Os2Display\CoreBundle\Events\ApiDataEvent;

class ApiDataSubscriber implements EventSubscriberInterface
{
    /** @var \Doctrine\ORM\EntityManagerInterface */
    protected $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public static function getSubscribedEvents()
    {
        return [
            ApiDataEvent::API_DATA_ADD => 'addApiData',
        ];
    }

    public function addApiData(ApiDataEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Screen) {
            $queryBuilder = $this->manager->createQueryBuilder();
            $activeCampaigns = $queryBuilder->select('c')
              ->from(Campaign::class, 'c')
              ->where(':screen member of c.screens')
              ->andWhere(':now between c.scheduleFrom and c.scheduleTo')
              ->setParameter('screen', $entity)
              ->setParameter('now', new \DateTime())
              ->getQuery()->getResult();

            $entity->setApiData(['active_campaigns' => $activeCampaigns]);
        }
    }
}
