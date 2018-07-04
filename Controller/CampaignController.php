<?php
/**
 * @file
 * Contains campaign controller.
 */

namespace Os2Display\CampaignBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Os2Display\CampaignBundle\Entity\Campaign;
use Os2Display\CoreBundle\Controller\ApiController;
use Os2Display\CoreBundle\Exception\DuplicateEntityException;
use Os2Display\CoreBundle\Exception\HttpDataException;
use Os2Display\CoreBundle\Exception\ValidationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api/campaign")
 * @Rest\View(serializerGroups={"campaign"})
 */
class CampaignController extends ApiController
{
    /**
     * Lists all campaign entities.
     *
     * @Rest\Get("", name="api_campaign_index")
     *
     * @Security("is_granted('LIST', 'campaign')")
     *
     */
    public function indexAction()
    {
        $campaigns = $this->findAll(Campaign::class);

        return $this->setApiData($campaigns);
    }

    /**
     * Creates a new campaign entity.
     *
     * @Rest\Post("", name="api_campaign_new")
     *
     * @ FIXME: Why does "is_granted" not work? (Apparently the Voter is not invoked).
     * @ Security("is_granted('CREATE', 'campaign')")
     * @Security("has_role('ROLE_CAMPAIGN_ADMIN')")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Campaign
     */
    public function newAction(Request $request)
    {
        // Get post content.
        $data = $this->getData($request);

        // Create campaign.
        try {
            $campaign = $this->get('os2display.campaign_manager')->createCampaign($data);
        } catch (ValidationException $e) {
            throw new HttpDataException(Response::HTTP_BAD_REQUEST, $data, 'Invalid data', $e);
        } catch (DuplicateEntityException $e) {
            throw new HttpDataException(Response::HTTP_CONFLICT, $data, 'Duplicate campaign', $e);
        }

        // Send response.
        return $this->createCreatedResponse($this->setApiData($campaign));
    }

    /**
     * Finds and displays a campaign entity.
     *
     * @Rest\Get("/{id}", name="api_campaign_show")
     *
     * @Security("is_granted('READ', campaign)")
     *
     * @return \Os2Display\CoreBundle\Entity\Campaign
     */
    public function showAction(Campaign $campaign)
    {
        return $this->setApiData($campaign);
    }

    /**
     * @Rest\Put("/{id}", name="api_campaign_edit")
     *
     * @Security("is_granted('UPDATE', campaign)")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Os2Display\CoreBundle\Entity\Campaign $campaign
     * @return Campaign
     */
    public function editAction(Request $request, Campaign $campaign)
    {
        $data = $this->getData($request);

        try {
            $campaign = $this->get('os2display.campaign_manager')->updateCampaign($campaign, $data);
        } catch (ValidationException $e) {
            throw new HttpDataException(Response::HTTP_BAD_REQUEST, $data, 'Invalid data', $e);
        } catch (DuplicateEntityException $e) {
            throw new HttpDataException(Response::HTTP_CONFLICT, $data, 'Duplicate campaign', $e);
        }

        return $this->setApiData($campaign);
    }

    /**
     * Deletes a campaign entity.
     *
     * @Rest\Delete("/{id}", name="api_campaign_delete")
     *
     * @Security("is_granted('DELETE', campaign)")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Os2Display\CoreBundle\Entity\Campaign $campaign
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, Campaign $campaign)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($campaign);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    // @FIXME: Hook into ApiController's method.
    protected function setApiData($object)
    {
        if (is_array($object)) {
            foreach ($object as $item) {
                $this->setApiData($item, true);
            }
        } elseif ($object instanceof Campaign) {
        }

        return $object;
    }
}
