<?php

namespace Os2Display\CampaignBundle\Event;

use Os2Display\CampaignBundle\Security\Roles;
use Os2Display\CoreBundle\Events\RolesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RolesSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            RolesEvent::ADD_ROLE_NAMES => 'addRoleNames',
        ];
    }

    public function addRoleNames(RolesEvent $event)
    {
        $event->addRoleNames(Roles::getRoleNames());
    }
}
