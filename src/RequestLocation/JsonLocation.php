<?php

namespace Desk\RequestLocation;

use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Command\Guzzle\Operation;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Command\Guzzle\RequestLocation\JsonLocation as GuzzleJsonLocation;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

class JsonLocation extends GuzzleJsonLocation
{
  /**
   * @param CommandInterface $command
   * @param RequestInterface $request
   * @param Parameter        $param
   *
   * @return RequestInterface
   */
  public function visit(
    CommandInterface $command,
    RequestInterface $request,
    Parameter $param
  ) {
    $location = $param->getData('location');
    if(!$location || $location != 'links')
    {
      $this->jsonData[$param->getWireName()] = parent::prepareValue(
        $command[$param->getName()],
        $param
      );
    } else {
      $this->jsonData['_links'][$param->getWireName()] = $this->prepareValue(
        $command[$param->getName()],
        $param
      );
    }

    return $request->withBody(Psr7\stream_for(\GuzzleHttp\json_encode($this->jsonData)));
  }

  /**
   * @param CommandInterface $command
   * @param RequestInterface $request
   * @param Operation        $operation
   *
   * @return MessageInterface
   */
  public function after(
    CommandInterface $command,
    RequestInterface $request,
    Operation $operation
  ) {
    $data = $this->jsonData;
    $this->jsonData = [];

    // Add additional parameters to the JSON document
    $additional = $operation->getAdditionalParameters();
    if ($additional && ($additional->getLocation() === $this->locationName)) {
      $location = $additional->getData('location');
      if(!$location || $location != 'links')
      {
        foreach ($command->toArray() as $key => $value) {
          if (!$operation->hasParam($key)) {
            $data[$key] = parent::prepareValue($value, $additional);
          }
        }
      } else {
        foreach ($command->toArray() as $key => $value) {
          if (!$operation->hasParam($key)) {
            $data['_links'][$key] = $this->prepareValue($value, $additional);
          }
        }
      }
    }

    // Don't overwrite the Content-Type if one is set
    if ($this->jsonContentType && !$request->hasHeader('Content-Type')) {
      $request = $request->withHeader('Content-Type', $this->jsonContentType);
    }

    return $request->withBody(Psr7\stream_for(\GuzzleHttp\json_encode($data)));
  }

  /**
   * Prepare (filter and set desired name for request item) the value for
   * request.
   *
   * @param mixed     $value
   * @param Parameter $param
   *
   * @return array|mixed
   */
  protected function prepareValue($value, Parameter $param)
  {
    return [
      'class' => $param->getData('class'),
      'href' => preg_replace(
        '/{value}/',
        $value,
        $param->getData('href')
      )
    ];
  }
}
