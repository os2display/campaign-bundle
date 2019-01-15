<?php
/**
 * @file
 * This file is a part of the Os2DisplayCoreBundle.
 *
 * Contains the middleware communication service.
 */

namespace Itk\CampaignBundle\Service;

use Os2Display\CoreBundle\Entity\Channel;
use Os2Display\CoreBundle\Entity\SharedChannel;
use Itk\CampaignBundle\Entity\Campaign;
use Os2Display\CoreBundle\Events\PostPushChannelsEvent;
use Os2Display\CoreBundle\Events\PrePushChannelEvent;
use Os2Display\CoreBundle\Events\PrePushChannelsEvent;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class CampaignService
 */
class CampaignService
{
    protected $doctrine;
    protected $container;
    protected $campaignChanges;

    /**
     * Constructor.
     *
     * @param Container $container
     *   The service container.
     * @throws \Exception
     */
    public function __construct(
        Container $container
    ) {
        $this->doctrine = $container->get('doctrine');
        $this->container = $container;
    }

    /**
     * Subscribed events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            PrePushChannelsEvent::EVENT_PRE_PUSH_CHANNELS => 'prePushChannels',
            PrePushChannelEvent::EVENT_PRE_PUSH_CHANNEL => 'prePushChannel',
            PostPushChannelsEvent::EVENT_POST_PUSH_CHANNELS => 'postPushChannels',
        ];
    }

    /**
     * Handle PrePushChannelsEvent events.
     *
     * Calculate campaign changes.
     *
     * @param \Os2Display\CoreBundle\Events\PrePushChannelsEvent $event
     */
    public function prePushChannels(PrePushChannelsEvent $event)
    {
        $this->campaignChanges = $this->calculateCampaignChanges();

        $this->container->get('logger')->info("Calculated campaign changes: " . json_encode($this->campaignChanges));
    }

    /**
     * Handle PrePushChannelEvent events.
     *
     * Change channel according to calculated campaign changes.
     *
     * @param \Os2Display\CoreBundle\Events\PrePushChannelEvent $event
     */
    public function prePushChannel(PrePushChannelEvent $event)
    {
        $data = $event->getData();

        $this->applyCampaignToChannelData($event->getEntity()->getId(), $data);

        $event->setData($data);
    }

    /**
     * Handle PostPushChannelsEvent events.
     *
     * Cleanup.
     *
     * @param \Os2Display\CoreBundle\Events\PostPushChannelsEvent $event
     */
    public function postPushChannels(PostPushChannelsEvent $event)
    {
        $this->campaignChanges = null;
    }

    /**
     * Get the screens the campaign affects.
     *
     * @param $campaign
     * @return array
     */
    private function getCampaignScreenIds($campaign)
    {
        $screenIds = [];

        foreach ($campaign->getScreens() as $screen) {
            $screenIds[] = $screen->getId();
        }
        foreach ($campaign->getScreenGroups() as $group) {
            $groupings = $group->getGrouping();

            foreach ($groupings as $grouping) {
                $screenIds[] = $grouping->getEntityId();
            }
        }

        return array_unique($screenIds);
    }

    /**
     * Get the campaign channel ids.
     *
     * @param $campaign
     * @return array
     */
    private function getCampaignChannelIds($campaign)
    {
        $campaignChannels = $campaign->getChannels();

        $campaignChannelIds = [];
        foreach ($campaignChannels as $campaignChannel) {
            $campaignChannelIds[] = $campaignChannel->getId();
        }

        return $campaignChannelIds;
    }

    private function applyCampaignToChannelData($id, &$data)
    {
        // If campaign changes are set, apply them to channel.
        if (!is_null(
                $this->campaignChanges
            ) && isset($this->campaignChanges[$id])) {
            $dataArray = json_decode($data);

            $dataArray->regions =
                $this->campaignChanges[$id]['regions'];

            $dataArray->screens = [];

            foreach ($dataArray->regions as $region) {
                $dataArray->screens = array_merge(
                    $dataArray->screens,
                    [$region->screen]
                );
            }

            $data = json_encode($dataArray);
        }
    }

    /**
     * Calculates the changes to channel data [.screens and .regions fields].
     *
     * @return array
     */
    private function calculateCampaignChanges()
    {
        $results = [];

        $now = new \DateTime();

        $queryBuilder = $this->doctrine
            ->getManager()
            ->createQueryBuilder();

        $campaigns = $queryBuilder->select('campaign')
            ->from(Campaign::class, 'campaign')
            ->where(
                ':now between campaign.scheduleFrom and campaign.scheduleTo'
            )
            ->setParameter('now', $now)
            ->getQuery()->getResult();

        $channelScreenRegions = $this->doctrine->getRepository(
            'Os2DisplayCoreBundle:ChannelScreenRegion'
        )->findAll();

        // Create results array from all ChannelScreenRegions.
        foreach ($channelScreenRegions as $csr) {
            if (!is_null($csr->getChannel())) {
                $channelId = $csr->getChannel()->getId();
            } elseif (!is_null($csr->getSharedChannel())) {
                $channelId = $csr->getSharedChannel()->getUniqueId();
            } else {
                continue;
            }

            $region = $csr->getRegion();
            $screenId = $csr->getScreen()->getId();

            if (!isset($results[$channelId])) {
                $results[$channelId] = [
                    'screens' => [],
                    'regions' => [],
                ];
            }

            $results[$channelId]['screens'] = array_unique(
                array_merge($results[$channelId]['screens'], [$screenId])
            );
            $results[$channelId]['regions'][] = (object)[
                'screen' => $screenId,
                'region' => $region,
            ];
        }

        // Modify results array based on active campaigns.
        foreach ($campaigns as $campaign) {
            $campaignChannelIds = $this->getCampaignChannelIds($campaign);
            $campaignScreenIds = $this->getCampaignScreenIds($campaign);

            // Remove all regions (with id 1) that are affected by the campaigns.
            foreach ($results as $channelId => &$result) {
                foreach ($result['regions'] as $key => $region) {
                    if ($region->region === 1 &&
                        !(isset($region->added_by_campaign) && $region->added_by_campaign == true)) {
                        if (in_array($region->screen, $campaignScreenIds)) {
                            unset($result['regions'][$key]);
                        }
                    }
                }

                // Reset array keys
                $result['regions'] = array_values(
                    $result['regions']
                );

                // Get screen ids from regions.
                $screenIds = array_reduce($results[$channelId]['regions'], function ($carry, $item) {
                    return array_unique(array_merge($carry, [$item->screen]));
                }, []);

                $results[$channelId]['screens'] = $screenIds;
            }

            // Add all regions and screens that come from campaigns.
            foreach ($campaignChannelIds as $campaignChannelId) {
                foreach ($campaignScreenIds as $campaignScreenId) {
                    if (!isset($results[$campaignChannelId])) {
                        $results[$campaignChannelId] = [
                            'screens' => [],
                            'regions' => [],
                        ];
                    }

                    $results[$campaignChannelId]['screens'] = array_unique(
                        array_merge(
                            $results[$campaignChannelId]['screens'],
                            [$campaignScreenId]
                        )
                    );

                    $results[$campaignChannelId]['regions'][] = (object)[
                        'screen' => $campaignScreenId,
                        'region' => 1,
                        'added_by_campaign' => true,
                    ];
                }
            }
        }

        return $results;
    }
}
