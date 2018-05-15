<?php
/**
 * @file
 * Contains the Campaign model.
 */

namespace Itk\CampaignBundle\Entity;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use Os2Display\CoreBundle\Entity\ApiEntity;
use Os2Display\CoreBundle\Entity\GroupableEntity;
use Os2Display\CoreBundle\Traits\Groupable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Campaign entity.
 *
 * @ AccessorOrder("custom", custom = {"id", "title" ,"orientation", "created_at", "slides"})
 *
 * @ORM\Table(name="ik_campaign")
 * @ORM\Entity
 */
class Campaign extends ApiEntity implements GroupableEntity
{
    use TimestampableEntity;
    use Groupable;

    /**
     * Id.
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"campaign", "api-bulk", "screen"})
     */
    private $id;

    /**
     * Title.
     *
     * @ORM\Column(name="title", type="string")
     * @Groups({"campaign", "screen"})
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * User that created the channel.
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Os2Display\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user")
     */
    private $user;

    /**
     * Description.
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Groups({"campaign", "screen"})
     */
    private $description;

    /**
     * @ORM\Column(name="schedule_from", type="datetime")
     * @Groups({"campaign", "screen"})
     * @Assert\DateTime()
     */
    private $scheduleFrom;

    /**
     * @ORM\Column(name="schedule_to", type="datetime")
     * @Groups({"campaign", "screen"})
     * @Assert\DateTime()
     */
    private $scheduleTo;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Groups({"campaign"})
     * @ORM\ManyToMany(targetEntity="Os2Display\CoreBundle\Entity\Channel")
     * @ORM\JoinTable(name="ik_campaign_channel")
     */
    private $channels;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Groups({"campaign"})
     * @ORM\ManyToMany(targetEntity="Os2Display\CoreBundle\Entity\Screen")
     * @ORM\JoinTable(name="ik_campaign_screen")
     */
    private $screens;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Groups({"campaign"})
     * @ORM\ManyToMany(targetEntity="Os2Display\CoreBundle\Entity\Group")
     * @ORM\JoinTable(name="ik_campaign_group")
     */
    private $screenGroups;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Campaign
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getScheduleFrom()
    {
        return $this->scheduleFrom;
    }

    /**
     * @param mixed $scheduleFrom
     *
     * @return Campaign
     */
    public function setScheduleFrom($scheduleFrom)
    {
        $this->scheduleFrom = $scheduleFrom;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getScheduleTo()
    {
        return $this->scheduleTo;
    }

    /**
     * @param mixed $scheduleTo
     *
     * @return Campaign
     */
    public function setScheduleTo($scheduleTo)
    {
        $this->scheduleTo = $scheduleTo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $channels
     */
    public function setChannels($channels)
    {
        $this->channels = $channels;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getScreens()
    {
        return $this->screens;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $screens
     */
    public function setScreens($screens)
    {
        $this->screens = $screens;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getScreenGroups()
    {
        return $this->screenGroups;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $screenGroups
     */
    public function setScreenGroups($screenGroups)
    {
        $this->screenGroups = $screenGroups;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}
