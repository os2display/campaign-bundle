<?php
/**
 * @file
 * Contains the Campaign model.
 */

namespace Itk\CampaignBundle\Entity;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
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
 * @ORM\AttributeOverrides(
 *   @ORM\AttributeOverride(name="createdBy",
 *     column=@ORM\Column(name="user")
 *   )
 * )
 */
class Campaign extends ApiEntity implements GroupableEntity
{
    use BlameableEntity;
    use TimestampableEntity;
    use Groupable;

    /**
     * Id.
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api", "api-bulk"})
     */
    private $id;

    /**
     * Title.
     *
     * @ORM\Column(name="title", type="string")
     * @Groups({"api", "api-bulk"})
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * Description.
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Groups({"api", "api-bulk"})
     */
    private $description;

    /**
     * @ORM\Column(name="schedule_from", type="datetime")
     * @Groups({"api", "api-bulk"})
     * @Assert\DateTime()
     */
    private $scheduleFrom;

    /**
     * @ORM\Column(name="schedule_to", type="datetime")
     * @Groups({"api", "api-bulk"})
     * @Assert\DateTime()
     */
    private $scheduleTo;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="Os2Display\CoreBundle\Entity\Channel")
     * @ORM\JoinTable(name="ik_campaign_channel")
     */
    private $channels;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="Os2Display\CoreBundle\Entity\Screen")
     * @ORM\JoinTable(name="ik_campaign_screen")
     */
    private $screens;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="Os2Display\CoreBundle\Entity\Group")
     * @ORM\JoinTable(name="ik_campaign_group")
     */
    private $groups;

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
}
