<?php
/**
 * @file
 * This file is a part of the Os2DisplayCoreBundle.
 *
 * Contains the middleware communication service.
 */

namespace Itk\CampaignBundle\Service;

use JMS\Serializer\SerializationContext;
use Os2Display\CoreBundle\Entity\Channel;
use Os2Display\CoreBundle\Entity\ChannelScreenRegion;
use Os2Display\CoreBundle\Entity\SharedChannel;
use Itk\CampaignBundle\Entity\Campaign;
use Symfony\Component\DependencyInjection\Container;
use Os2Display\CoreBundle\Services\TemplateService;
use Os2Display\CoreBundle\Services\UtilityService;

use Os2Display\CoreBundle\Services\MiddlewareCommunication as BaseService;

/**
 * Class MiddlewareCommunication
 *
 * @package Os2Display\CoreBundle\Services
 */
class MiddlewareCommunication extends BaseService
{
    // @TODO: Move these to constructor dependecy injection, instead of using container.
    protected $middlewarePath;
    protected $doctrine;
    protected $serializer;
    protected $entityManager;

    /**
     * Constructor.
     *
     * @param Container $container
     *   The service container.
     * @param TemplateService $templateService
     *   The template service.
     * @param UtilityService $utilityService
     *   The utility service.
     */
    public function __construct(
        Container $container,
        TemplateService $templateService,
        UtilityService $utilityService
    ) {
        parent::__construct($container, $templateService, $utilityService);

        $this->middlewarePath =
            $this->container->getParameter('middleware_host').
            $this->container->getParameter('middleware_path');
        $this->doctrine = $this->container->get('doctrine');
        $this->serializer = $this->container->get('jms_serializer');
        $this->entityManager = $this->doctrine->getManager();
    }

    /**
     * Find Id's of the screen using a channel.
     *
     * @param Channel|SharedChannel $channel
     *   The Channel or SharedChannel to push.
     *
     * @return array
     *   Id's of the screens that uses the channel.
     */
    private function getScreenIdsOnChannel($channel)
    {
        // Get screen ids.
        $regions = $channel->getChannelScreenRegions();
        $screenIds = array();
        foreach ($regions as $region) {
            if (!in_array($region->getScreen()->getId(), $screenIds)) {
                $screenIds[] = $region->getScreen()->getId();
            }
        }

        return $screenIds;
    }

    private function getScreenIdsFromData($data)
    {
        $decoded = json_decode($data);

        return $decoded->screens;
    }

    /**
     * Push a Channel or a SharedChannel to the middleware.
     *
     * @param Channel|SharedChannel $channel
     *   The Channel or SharedChannel to push.
     * @param mixed $data
     *   The Data that should be pushed for $channel encoded as json.
     * @param string $id
     *   The id of the channel (internal id for Channel, unique_id for SharedChannel)
     * @param boolean $force
     *   Should the push be forced through?
     */
    public function pushChannel($channel, $data, $id, $force)
    {
        // Calculate hash of content, used to avoid unnecessary push.
        $sha1 = sha1($data);

        // Get screen ids.
        $screenIds = $this->getScreenIdsFromData($data);
        $lastPushScreens = json_decode($channel->getLastPushScreens());

        // Check if the channel should be pushed.
        if ($force ||
            $sha1 != $channel->getLastPushHash() ||
            $screenIds != $lastPushScreens) {
            // Only push channel if it's attached to a least one screen. If no screen
            // is attached then channel will be deleted from the middleware and
            // $lastPushTime will be reset later on in this function.
            if (count($screenIds) > 0) {
                $curlResult = $this->utilityService->curl(
                    $this->middlewarePath.'/channel/'.$id,
                    'POST',
                    $data,
                    'middleware'
                );

                // If the result was delivered, update the last hash.
                if ($curlResult['status'] === 200) {
                    $lastPushScreens = $channel->getLastPushScreens();

                    // Push deletes to the middleware if a channel has been on a screen previously,
                    // but now has been removed.
                    $updatedScreensFailed = false;

                    $lastPushScreensArray = array();
                    if (!empty($lastPushScreens)) {
                        $lastPushScreensArray = json_decode($lastPushScreens);
                    }

                    foreach ($lastPushScreensArray as $lastPushScreenId) {
                        if (!in_array($lastPushScreenId, $screenIds)) {
                            $curlResult = $this->utilityService->curl(
                                $this->middlewarePath.'/channel/'.$id.'/screen/'.$lastPushScreenId,
                                'DELETE',
                                json_encode(array()),
                                'middleware'
                            );

                            if ($curlResult['status'] !== 200) {
                                $updatedScreensFailed = true;
                            }
                        }
                    }

                    // If the delete process was successful, update last push information.
                    // else set values to NULL to ensure new push.
                    if (!$updatedScreensFailed) {
                        $channel->setLastPushScreens(json_encode($screenIds));
                        $channel->setLastPushHash($sha1);
                    } else {
                        // Removing channel from some screens have failed, hence mark the
                        // channel for re-push.
                        $channel->setLastPushHash(null);
                    }
                } else {
                    // Channel push failed for this channel mark it for re-push.
                    $channel->setLastPushHash(null);
                }
            } else {
                if (!is_null($channel->getLastPushHash())) {
                    // Channel don't have any screens, so delete from the middleware. This
                    // will automatically remove it from any screen connected to the
                    // middleware that displays is currently.
                    $curlResult = $this->utilityService->curl(
                        $this->middlewarePath.'/channel/'.$id,
                        'DELETE',
                        json_encode(array()),
                        'middleware'
                    );

                    if ($curlResult['status'] !== 200) {
                        // Delete did't not work, so mark the channel for
                        // re-push of DELETE by removing last push hash.
                        $channel->setLastPushHash(null);
                    } else {
                        // Channel delete success, so empty last pushed screens.
                        $channel->setLastPushScreens(json_encode([]));
                    }
                }
            }

            // Save changes to database.
            $this->entityManager->flush();
        }
    }

    private function campaignsApply($channel)
    {
        $now = new \DateTime();

        $queryBuilder = $this->doctrine
            ->getManager()
            ->createQueryBuilder();

        $campaigns = $queryBuilder->select('campaign')
            ->from(Campaign::class, 'campaign')
            ->where(
                ':now between campaign.scheduleFrom and campaign.scheduleTo'
            )
            ->andWhere(
                ':channel member of campaign.channels'
            )
            ->setParameter('channel', $channel)
            ->setParameter('now', $now)
            ->getQuery()->getResult();

        return count($campaigns) > 0;
    }

    /**
     * Should the channel be pushed?
     *
     * @param $channel
     * @return bool
     */
    private function channelShouldBePushed($channel)
    {
        if (count($this->getScreenIdsOnChannel($channel)) === 0) {
            // If no campaigns apply and it has not been pushed before.
            if (!$this->campaignsApply($channel) &&
                (
                    is_null($channel->getLastPushHash()) ||
                    empty($channel->getLastPushScreens()))
            ) {
                return false;
            }
        }

        return true;
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

        $before = $results;

        // Modify results array based on active campaigns.
        foreach ($campaigns as $campaign) {
            $campaignChannelIds = $this->getCampaignChannelIds($campaign);
            $campaignScreenIds = $this->getCampaignScreenIds($campaign);

            // Remove all regions that are affected by the campaigns.
            foreach ($results as $channelId => &$result) {
                foreach ($result['regions'] as $key => $region) {
                    if ($region->region === 1 &&
                        !(isset($region->added_by_campaign) && $region->added_by_campaign == true)) {
                        if (in_array($region->screen, $campaignScreenIds)) {
                            unset($result['regions'][$key]);
                            $result['regions'] = array_values(
                                $result['regions']
                            );
                        }
                    }
                }
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

    /**
     * Pushes the channels for each screen to the middleware.
     *
     * Campaigns only apply to region 1 of screens.
     *
     * @param boolean $force
     *   Should the push to screen be forced, even though the content has previously been pushed to the middleware?
     */
    public function pushToScreens($force = false)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder();

        // @TODO: Optimize which channels should be examined.
        // Get channels that are currently pushed to screens,
        // or should be pushed to screens.
        $activeChannels =
            $queryBuilder->select('c')
                ->from(Channel::class, 'c')
                ->getQuery()->getResult();

        $campaignChanges = $this->calculateCampaignChanges();

        foreach ($activeChannels as $channel) {
            if (!$this->channelShouldBePushed($channel)) {
                continue;
            }

            $data = $this->serializer->serialize(
                $channel,
                'json',
                SerializationContext::create()
                    ->setGroups(array('middleware'))
            );

            // If campaign changes are set, apply them to channel.
            if (isset($campaignChanges[$channel->getId()])) {
                $dataArray = json_decode($data);

                $dataArray->regions =
                    $campaignChanges[$channel->getId()]['regions'];

                $dataArray->screens = [];

                foreach ($dataArray->regions as $region) {
                    $dataArray->screens = array_merge(
                        $dataArray->screens,
                        [$region->screen]
                    );

                    $dataArray->screens = array_unique($dataArray->screens);
                }

                $data = json_encode($dataArray);
            }

            $this->pushChannel($channel, $data, $channel->getId(), $force);
        }

        // Push shared channels
        $sharedChannels = $this->doctrine->getRepository(
            'Os2DisplayCoreBundle:SharedChannel'
        )->findAll();

        foreach ($sharedChannels as $sharedChannel) {
            $data = $this->serializer->serialize(
                $sharedChannel,
                'json',
                SerializationContext::create()
                    ->setGroups(array('middleware'))
            );

            // Hack to get slides encoded correctly
            //   Issue with how the slides array is encoded in jms_serializer.
            $d = json_decode($data);
            $d->data->slides = json_decode($d->data->slides);
            $data = json_encode($d);

            if ($data === null) {
                continue;
            }

            // If campaign changes are set, apply them to channel.
            if (isset($campaignChanges[$sharedChannel->getUniqueId()])) {
                $dataArray = json_decode($data);

                $dataArray->regions =
                    $campaignChanges[$sharedChannel->getUniqueId()]['regions'];

                $dataArray->screens = [];

                foreach ($dataArray->regions as $region) {
                    $dataArray->screens = array_merge(
                        $dataArray->screens,
                        [$region->screen]
                    );
                }

                $data = json_encode($dataArray);
            }

            $this->pushChannel(
                $sharedChannel,
                $data,
                $sharedChannel->getUniqueId(),
                $force
            );
        }
    }
}
