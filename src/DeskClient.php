<?php

namespace ColinC\Desk;

use Concat\Http\Middleware\RateLimiter;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\Deserializer;

use Noodlehaus\Config;

class DeskClient extends GuzzleClient {

  public function __construct($config = []) {

    $consumer_key = $config['consumer_key'];
    $consumer_secret = $config['consumer_secret'];
    $token = $config['token'];
    $token_secret = $config['token_secret'];
    $site_name = $config['site_name'];
    $api_version = $config['api_version'];

    $stack = HandlerStack::create();
    $stack->push(new Oauth1([
      'consumer_key'    => $consumer_key,
      'consumer_secret' => $consumer_secret,
      'token'           => $token,
      'token_secret'    => $token_secret
    ]));
    $stack->push(new RateLimiter(new DeskRateLimitProvider));

    $client = new Client([
        'base_uri' => 'https://'.$site_name.'.desk.com',
        'handler' => $stack,
        'auth' => 'oauth'
      ]);

    $description = [
        'baseUri' => 'https://nvows.desk.com/api/'.$api_version.'/',
        'models' => [
          'DeskModel' => [
            'type' => 'object',
            'additionalProperties' => [ 'location' => 'desk' ],
            'data' => [ 'client' => $client ],
          ]
        ]
      ];

    $operations = new Config([__DIR__.'/service-description/operations']);
    $description['operations'] = $operations->all();
    /*die(var_dump([
        'baseUri' => 'https://nvows.desk.com/api/'.$api_version.'/',
        'operations' => [
          'case' => [
            'httpMethod' => 'GET',
            'parameters' => [
              'id' => [
                'required' => 'true',
                'location' => 'uri'
              ],
              'embed' => [
                'location' => 'query'
              ],
            ],
            'uri' => 'cases/{id}',
            'responseModel' => 'DeskModel'
          ],
          'cases' => [
            'httpMethod' => 'GET',
            'uri' => 'cases',
            'responseModel' => 'DeskModel',
          ]
        ],
        'models' => [
          'DeskModel' => [
            'type' => 'object',
            'additionalProperties' => [ 'location' => 'desk' ],
            'data' => [ 'client' => $client ],
          ]
        ]
      ]));*/

    $description = new Description( $description );

    $deserializer = new Deserializer($description, true, [
      'desk' => new DeskLocation()
    ]);

    parent::__construct($client, $description, null, $deserializer);
  }
}
