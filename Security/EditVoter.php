<?php

namespace Os2Display\CampaignBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Os2Display\CoreBundle\Entity\Group;
use Os2Display\CoreBundle\Entity\GroupableEntity;
use Os2Display\CoreBundle\Entity\User;
use Os2Display\CoreBundle\Entity\UserGroup;
use Os2Display\CoreBundle\Security\Roles;
use Os2Display\CoreBundle\Services\SecurityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EditVoter extends Voter
{
    protected $manager;
    protected $decisionManager;
    protected $securityManager;

    const CREATE = 'CREATE';
    const READ = 'READ';
    const UPDATE = 'UPDATE';
    const DELETE = 'DELETE';
    const READ_LIST = 'LIST';

    public function __construct(EntityManagerInterface $manager, AccessDecisionManagerInterface $decisionManager, SecurityManager $securityManager)
    {
        $this->manager = $manager;
        $this->decisionManager = $decisionManager;
        $this->securityManager = $securityManager;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::CREATE, self::READ, self::UPDATE, self::DELETE, self::READ_LIST]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, [Roles::ROLE_ADMIN])) {
            return true;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                        header('Content-type: text/plain');
                echo var_export(['attribute' => $attribute], true);
                die(__FILE__.':'.__LINE__.':'.__METHOD__);

                return false;

            case self::READ_LIST:
                return $this->decisionManager->decide($token, ['ROLE_CAMPAIGN_ADMIN']);

                // case self::READ: case self::UPDATE: case self::DELETE:
                // Handled by Os2Display\CoreBundle\Security\EditVoter
                // @FIXME: Can't we do this in a better way? Maybe inherit?
        }

        return false;
    }
}
