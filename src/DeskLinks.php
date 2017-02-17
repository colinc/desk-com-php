<?php

namespace ColinC\Desk;

use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Command\ResultInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
/**
 * Extracts elements from a JSON document.
 */
class DeskLinks
{
    /** @var Client The Guzzle client */
    private $client;
    /** @var string The Desk callback links */
    private $links;
    /**
     * @param \GuzzleHttp\Client $client
     * @param string $href
     *
     * @return DeskLink
     */
    public function __construct( Client $client, $links) {
      $this->client = $client;
      $this->links = $links;
    }

    public function __get($name) {
      if(isset($this->links[$name])) {
        $response = $this->client->get($this->links[$name]['href']);
        $body = (string) $response->getBody();
        $body = $body ?: "{}";
        $results = \GuzzleHttp\json_decode($body,true);
        return new DeskResult($this->client, $results);
      }
    }
}
