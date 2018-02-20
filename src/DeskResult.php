<?php

namespace Desk;

use stdClass;
use GuzzleHttp\Client;

/**
 * Extracts elements from a JSON document.
 */
class DeskResult
{
    /** @var Client The Guzzle client */
    private $client;
    /** @var array The JSON document being visited */
    private $data = [];
    /**
     * @param \GuzzleHttp\Client $client
     * @param string $href
     *
     * @return DeskLink
     */
    public function __construct( Client $client, $data ) {
      $this->client = $client;
      $this->data = $data;
    }

    public function __get($name) {
      if($name === 'links') {
        return new DeskLinks($this->client, $this->data['_links']);
      }
      if($name === 'embedded') {
        $data = new stdClass();
        foreach($this->data['_embedded'] as $key => $value)
        {
          if($key === 'entries') {
            $data->$key = [];
            foreach($value as $entry) {
              array_push($data->$key, new DeskResult($this->client, $entry));
            }
          } else {
            $data->$key = new DeskResult($this->client, $value);
          }
        }
        return $data;
      }
      if(isset($this->data[$name])) {
        if(is_array($this->data[$name])) {
          return (object) $this->data[$name];
        }
        return $this->data[$name];
      }
    }
}
