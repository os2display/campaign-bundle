<?php

namespace Os2Display\CampaignBundle\Security;

/**
 * Class Roles.
 *
 * A helper class to help using role names in code.
 */
class Roles
{
    const ROLE_CAMPAIGN_ADMIN = 'ROLE_CAMPAIGN_ADMIN';

    public static function getRoleNames()
    {
        $class = new \ReflectionClass(static::class);

        return $class->getConstants();
    }
}
