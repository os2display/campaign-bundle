<?php

namespace Itk\CampaignBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Itk\CampaignBundle\Entity\Campaign;
use Os2Display\CoreBundle\Exception\DuplicateEntityException;
use Os2Display\CoreBundle\Services\EntityService;
use Os2Display\CoreBundle\Services\SecurityManager;

class CampaignManager
{
    protected static $editableProperties = [
      'title',
      'description',
      'schedule_from',
      'schedule_to',
    ];

    protected $entityService;
    protected $securityMananager;
    protected $entityManager;

    public function __construct(EntityService $entityService, SecurityManager $securityManager, EntityManagerInterface $entityManager) {
        $this->entityService = $entityService;
        $this->securityMananager = $securityManager;
        $this->entityManager = $entityManager;
    }

    public function createCampaign($data) {
        if (isset($data['schedule_from']) && is_scalar($data['schedule_from'])) {
            $data['schedule_from'] = new \DateTime($data['schedule_from']);
        }
        if (isset($data['schedule_to']) && is_scalar($data['schedule_to'])) {
            $data['schedule_to'] = new \DateTime($data['schedule_to']);
        }

        $campaign = new Campaign();
        $this->entityService->setValues($campaign, $data, self::$editableProperties);
        $this->entityService->validateEntity($campaign);

        $repository = $this->entityManager->getRepository(Campaign::class);
        if ($repository->findBy(['title' => $campaign->getTitle()])) {
            throw new DuplicateEntityException('Campaign already exists.', $data);
        }

        $this->entityManager->persist($campaign);
        $this->entityManager->flush();

        return $campaign;
    }

    public function updateCampaign(Campaign $campaign, $data) {
        $this->entityService->setValues($campaign, $data, self::$editableProperties);
        $this->entityService->validateEntity($campaign);

        $repository = $this->entityManager->getRepository(Campaign::class);
        $anotherCampaign = $repository->findOneBy(['title' => $campaign->getTitle()]);
        if ($anotherCampaign && $anotherCampaign->getId() !== $campaign->getId()) {
            throw new DuplicateEntityException('Campaign already exists.', $data);
        }

        $this->entityManager->persist($campaign);
        $this->entityManager->flush();

        return $campaign;
    }

}
