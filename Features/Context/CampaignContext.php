<?php

namespace Itk\CampaignBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behatch\Context\BaseContext;
use Behatch\HttpCall\HttpCallResultPool;
use Behatch\HttpCall\Request;
use Behatch\Json\Json;
use Behatch\Json\JsonInspector;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use Os2Display\CoreBundle\Entity\Channel;
use Os2Display\CoreBundle\Entity\ChannelScreenRegion;
use Os2Display\CoreBundle\Entity\ChannelSlideOrder;
use Os2Display\CoreBundle\Entity\User;
use Os2Display\CoreBundle\Entity\UserGroup;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Os2Display\CoreBundle\Entity\Slide;
use Os2Display\CoreBundle\Entity\Screen;

/**
 * Defines application features from the specific context.
 */
class CampaignContext extends BaseContext implements Context, KernelAwareContext
{
    private $kernel;
    private $container;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $manager;

    /**
     * @var \Behatch\HttpCall\Request
     */
    private $request;

    /**
     * @var \Behatch\Json\JsonInspector
     */
    private $inspector;

    /**
     * @var \Behatch\HttpCall\HttpCallResultPool
     */
    private $httpCallResultPool;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     *
     * @param mixed $evaluationMode
     */
    public function __construct(
        ManagerRegistry $doctrine,
        Request $request,
        HttpCallResultPool $httpCallResultPool,
        $evaluationMode = 'javascript'
    ) {
        $this->doctrine = $doctrine;
        $this->request = $request;
        $this->manager = $doctrine->getManager();
        $this->schemaTool = new SchemaTool($this->manager);
        $this->classes = $this->manager->getMetadataFactory()->getAllMetadata();

        $this->inspector = new JsonInspector($evaluationMode);
        $this->httpCallResultPool = $httpCallResultPool;
    }

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->container = $this->kernel->getContainer();
    }

    private function createChannel(array $data)
    {
        $repository = $this->doctrine->getManager()->getRepository(
            Channel::class
        );

        $channel = $repository->findOneBy(['title' => $data['title']]);
        if (!$channel) {
            $channel = new Channel();
            $channel->setTitle($data['title']);
            $channel->setCreatedAt($data['created_at']);
            $channel->setModifiedAt($data['modified_at']);
            $this->doctrine->getManager()->persist($channel);
            $this->doctrine->getManager()->flush();
        }
    }

    /**
     * @Given the following channels exist:
     */
    public function theFollowingChannelsExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->createChannel($row);
        }
        $this->doctrine->getManager()->clear();
    }

    private function createSlide(array $data)
    {
        $repository = $this->doctrine->getManager()->getRepository(
            Slide::class
        );
        $channelsRepository = $this->doctrine->getManager()->getRepository(
            Channel::class
        );

        $slide = $repository->findOneBy(['title' => $data['title']]);
        if (!$slide) {
            $slide = new Slide();
            $slide->setTitle($data['title']);
        }

        foreach ([$data['channel']] as $channel) {
            $channel = $channelsRepository->findOneBy(['id' => $channel]);

            $channelSlideOrder = new ChannelSlideOrder();
            $channelSlideOrder->setChannel($channel);
            $channelSlideOrder->setSlide($slide);
            $channelSlideOrder->setSortOrder(0);
        }

        $this->doctrine->getManager()->persist($slide);
    }

    /**
     * @Given the following slides exist:
     */
    public function theFollowingSlidesExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $title = $row['title'];
            $channel = $row['channel'];

            $this->createSlide(['title' => $title, 'channel' => $channel]);
        }
        $this->doctrine->getManager()->clear();
    }

    /**
     * @When I call pushToScreens
     */
    public function iCallPushtoscreens()
    {
        $this->container->get('os2display.middleware.communication')
            ->pushToScreens();
    }

    /**
     * @When I get the last utility service curl call
     */
    public function iGetTheLastUtilityServiceCurlCall()
    {
        return $this->container->get('os2display.utility_service')
            ->getLastRequest();
    }

    /**
     * @When I get all the utility service curl calls
     */
    public function iGetAllTheUtilityServiceCurlCalls()
    {
        $this->curlCalls = $this->container->get('os2display.utility_service')
            ->getAllRequests();
    }

    /**
     * @When I get all the utility service curl calls with prefix :prefix
     */
    public function iGetAllTheUtilityServiceCurlCallsWithPrefix($prefix)
    {
        $this->curlCalls = $this->container->get('os2display.utility_service')
            ->getAllRequests($prefix);
    }

    /**
     * @When I print all the utility service curl calls
     */
    public function iPrintAllTheUtilityServiceCurlCalls()
    {
        print_r(
            $this->container->get('os2display.utility_service')->getAllRequests(
            )
        );
    }

    /**
     * @Then curl calls should equal:
     */
    public function curlCallsShouldEqual(TableNode $table)
    {
        $shouldEqual = $table->getHash();

        $this->assertEquals(
            $this->curlCalls,
            $shouldEqual,
            sprintf(
                'The array "%s" should be an equal the input',
                json_encode($this->curlCalls)
            )
        );
    }

    private function createScreen(array $data)
    {
        $repository = $this->doctrine->getManager()->getRepository(
            Screen::class
        );
        $channelRepository = $this->doctrine->getManager()->getRepository(
            Channel::class
        );

        $screen = $repository->findOneBy(['title' => $data['title']]);
        if (!$screen) {
            $screen = new Screen();
            $screen->setTitle($data['title']);
            $screen->setCreatedAt($data['created_at']);
            $screen->setModifiedAt($data['modified_at']);
            $screen->setToken('123');
            $screen->setActivationCode('123');
            $screen->setDescription('123');

            foreach ([$data['channel']] as $channel) {
                $channel = $channelRepository->findOneBy(['id' => $channel]);

                $csr = new ChannelScreenRegion();
                $csr->setChannel($channel);
                $csr->setScreen($screen);
                $csr->setRegion(0);
                $this->doctrine->getManager()->persist($csr);
            }

            $this->doctrine->getManager()->persist($screen);
            $this->doctrine->getManager()->flush();
        }
    }

    /**
     * @Given the following screens exist:
     */
    public function theFollowingScreensExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $this->createScreen($row);
        }
        $this->doctrine->getManager()->clear();
    }

    private function channelPushedToScreen($channel, $screen, $result = true) {
        $requests = $this->container->get('os2display.utility_service')
            ->getAllRequests('middleware');

        $res = false;

        foreach ($requests as $request) {
            if (explode('https://middleware.os2display.vm/api/channel/', $request['url'])[1] == $channel) {
                $data = json_decode($request['data']);
                if (in_array($screen, $data->screens)) {
                    $res = true;
                }
            }
        }

        $this->assertEquals($res, $result, sprintf(
            'The channel "%s" should' . (!$result ? ' not' : '')  . ' be pushed to screen "%s"',
            $channel,
            $screen
        ));
    }

    /**
     * @Then channel :arg1 should be pushed to screen :arg2
     */
    public function channelShouldBePushedToScreen($arg1, $arg2)
    {
        $this->channelPushedToScreen($arg1, $arg2);
    }

    /**
     * @Then channel :arg1 should not be pushed to screen :arg2
     */
    public function channelShouldNotBePushedToScreen($arg1, $arg2)
    {
        $this->channelPushedToScreen($arg1, $arg2, false);
    }
}
