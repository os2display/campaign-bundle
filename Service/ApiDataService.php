<?php

namespace Itk\CampaignBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Itk\CampaignBundle\Entity\Campaign;
use Os2Display\CoreBundle\Entity\ApiEntity;
use Os2Display\CoreBundle\Entity\Screen;

class ApiDataService
{
    /** @var \Doctrine\ORM\EntityManagerInterface */
    protected $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function setApiData(ApiEntity $entity)
    {
        if ($entity instanceof Screen) {
            $queryBuilder = $this->manager->createQueryBuilder();
            $activeCampaigns = $queryBuilder->select('c')
              ->from(Campaign::class, 'c')
              ->where(':now between c.scheduleFrom and c.scheduleTo')
              ->andWhere($queryBuilder->expr()->orX(
                ':screen member of c.screens',
                ':screen member of c.screenGroups'
              ))
              ->setParameter('screen', $entity)
              ->setParameter('now', new \DateTime())
              ->getQuery()->getResult();

            $entity->setApiData(['active_campaigns' => $activeCampaigns]);
        }
    }
}
