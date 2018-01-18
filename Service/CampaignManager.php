<?php

namespace Itk\CampaignBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Itk\CampaignBundle\Entity\Campaign;
use Os2Display\CoreBundle\Exception\DuplicateEntityException;
use Os2Display\CoreBundle\Services\EntityService;
use Os2Display\CoreBundle\Services\GroupManager;
use Os2Display\CoreBundle\Services\SecurityManager;

class CampaignManager
{
    protected static $editableProperties = [
      'title',
      'description',
      'schedule_from',
      'schedule_to',
      'channels',
      'screens',
      'groups',
    ];

    protected $entityService;
    protected $securityMananager;
    protected $groupManager;
    protected $entityManager;

    public function __construct(EntityService $entityService, SecurityManager $securityManager, GroupManager $groupManager, EntityManagerInterface $entityManager) {
        $this->entityService = $entityService;
        $this->securityMananager = $securityManager;
        $this->groupManager = $groupManager;
        $this->entityManager = $entityManager;
    }

    public function createCampaign($data) {
        $campaign = new Campaign();

        return $this->persistCampaign($campaign, $data);
    }

    public function updateCampaign(Campaign $campaign, $data) {
        return $this->persistCampaign($campaign, $data);
    }

    private function persistCampaign(Campaign $campaign, $data) {
        $data = $this->normalizeData($data);
        if (isset($data['groups'])) {
            $this->groupManager->replaceGroups($data['groups'], $campaign);
            unset($data['groups']);
        }
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

    private function normalizeData($data) {
        if (isset($data['schedule_from']) && is_scalar($data['schedule_from'])) {
            $data['schedule_from'] = new \DateTime($data['schedule_from']);
        }
        if (isset($data['schedule_to']) && is_scalar($data['schedule_to'])) {
            $data['schedule_to'] = new \DateTime($data['schedule_to']);
        }

        return $data;
    }
}
