<?php

namespace Os2Display\CampaignBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Os2Display\CampaignBundle\Entity\Campaign;
use Os2Display\CoreBundle\Entity\Channel;
use Os2Display\CoreBundle\Entity\Screen;
use Os2Display\CoreBundle\Exception\DuplicateEntityException;
use Os2Display\CoreBundle\Services\EntityManagerService;
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
        'screen_groups',
        'groups',
    ];

    protected $entityService;
    protected $securityMananager;
    protected $groupManager;
    protected $entityManager;
    protected $entityManagerService;

    public function __construct(
        EntityService $entityService,
        SecurityManager $securityManager,
        GroupManager $groupManager,
        EntityManagerInterface $entityManager,
        EntityManagerService $entityManagerService
    ) {
        $this->entityService = $entityService;
        $this->securityMananager = $securityManager;
        $this->groupManager = $groupManager;
        $this->entityManager = $entityManager;
        $this->entityManagerService = $entityManagerService;
    }

    public function createCampaign($data)
    {
        $campaign = new Campaign();
        $campaign->setCreatedAt(new \DateTime());

        return $this->persistCampaign($campaign, $data);
    }

    public function updateCampaign(Campaign $campaign, $data)
    {
        return $this->persistCampaign($campaign, $data);
    }

    private function persistCampaign(Campaign $campaign, $data)
    {
        $data = $this->normalizeData($data);
        if (isset($data['groups'])) {
            $this->groupManager->setGroups($data['groups'], $campaign);
            unset($data['groups']);
        }
        if (isset($data['channels'])) {
            $data['channels'] = $this->entityManagerService->loadEntities(
                $data['channels'],
                Channel::class
            );
        }
        if (isset($data['screens'])) {
            $data['screens'] = $this->entityManagerService->loadEntities(
                $data['screens'],
                Screen::class
            );
        }
        if (isset($data['screen_groups'])) {
            $groups = $this->groupManager->loadGroups(
                $data['screen_groups'],
                $campaign->getScreenGroups()
            );
            $campaign->setScreenGroups($groups);
            unset($data['screen_groups']);
        }

        $this->entityService->setValues(
            $campaign,
            $data,
            self::$editableProperties
        );
        $this->entityService->validateEntity($campaign);

        $repository = $this->entityManager->getRepository(Campaign::class);
        $anotherCampaign = $repository->findOneBy(
            ['title' => $campaign->getTitle()]
        );

        if ($anotherCampaign &&
            $anotherCampaign->getId() !== $campaign->getId()) {
            throw new DuplicateEntityException(
                'Campaign already exists.',
                $data
            );
        }

        // Trick to make sure that entity is persisted.
        $campaign->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($campaign);
        $this->entityManager->flush();

        return $campaign;
    }

    private function normalizeData($data)
    {
        if (isset($data['title'])) {
            $data['title'] = trim($data['title']);
        }
        if (isset($data['schedule_from'])) {
            $data['schedule_from'] = $this->getDatetime($data['schedule_from']);
        }
        if (isset($data['schedule_to'])) {
            $data['schedule_to'] = $this->getDatetime($data['schedule_to']);
        }

        return $data;
    }

    private function getDatetime($value)
    {
        $date = null;
        if ($value instanceof \DateTime) {
            $date = $value;
        } elseif (is_string($value)) {
            $date = new \DateTime($value);
        } elseif (is_integer($value)) {
            $date = new \DateTime();
            $date->setTimestamp($value);
        }

        if ($date !== null) {
            $date->setTimezone(new \DateTimeZone('UTC'));
        }

        return $date;
    }
}
