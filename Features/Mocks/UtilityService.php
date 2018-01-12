<?php

namespace Itk\CampaignBundle\Features\Mocks;

use Os2Display\CoreBundle\Services\AuthenticationService;

class UtilityService extends \Os2Display\CoreBundle\Services\UtilityService
{
    private $requests = [];

    /**
     * Constructor.
     *
     * @param AuthenticationService $authenticationService
     *   The authentication service.
     */
    public function __construct(AuthenticationService $authenticationService)
    {
    }

    public function getLastRequest()
    {
        return end($this->requests);
    }

    public function getAllRequests($prefix = null)
    {
        if (is_null($prefix)) {
            return $this->requests;
        }

        $res = [];

        foreach ($this->requests as $request) {
            if ($request['prefix'] == $prefix) {
                $res[] = $request;
            }
        }

        return $res;
    }

    /**
     * @param $url
     * @param string $method
     * @param $data
     * @param $prefix
     */
    public function curl($url, $method, $data, $prefix)
    {
        $this->requests[] = [
            'url' => $url,
            'method' => $method,
            'data' => $data,
            'prefix' => $prefix,
        ];

        return [
            'status' => 200,
            'content' => ["mock"],
        ];
    }
}
