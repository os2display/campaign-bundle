<?php

namespace Itk\CampaignBundle\Event;

use Itk\CampaignBundle\Service\ApiDataService;
use Os2Display\CoreBundle\Events\ApiDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApiDataSubscriber implements EventSubscriberInterface
{
    protected $apiDataService;

    public function __construct(ApiDataService $apiDataService)
    {
        $this->apiDataService = $apiDataService;
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
        $this->apiDataService->setApiData($entity);
    }
}
