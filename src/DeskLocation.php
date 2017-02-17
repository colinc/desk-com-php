<?php

namespace ColinC\Desk;

use GuzzleHttp\Command\Guzzle\ResponseLocation\AbstractLocation;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Command\Result;
use GuzzleHttp\Command\ResultInterface;
use Psr\Http\Message\ResponseInterface;
/**
 * Extracts elements from a JSON document.
 */
class DeskLocation extends AbstractLocation
{
    /**
     * Set the name of the location
     *
     * @param string $locationName
     */
    public function __construct($locationName = 'desk')
    {
        parent::__construct($locationName);
    }
    /**
     * @param \GuzzleHttp\Command\ResultInterface  $result
     * @param \Psr\Http\Message\ResponseInterface  $response
     * @param \GuzzleHttp\Command\Guzzle\Parameter $model
     *
     * @return \GuzzleHttp\Command\ResultInterface
     */
    public function after(
        ResultInterface $result,
        ResponseInterface $response,
        Parameter $model
    ) {
        $client = $model->getData('client');
        $body = (string) $response->getBody();
        $body = $body ?: "{}";
        $results = \GuzzleHttp\json_decode($body,true);
        return new DeskResult($client, $results);
    }
}
