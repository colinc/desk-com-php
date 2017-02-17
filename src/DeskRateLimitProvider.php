<?php

namespace ColinC\Desk;

use Cache;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Concat\Http\Middleware\RateLimitProvider;

/**
 * An object which manages rate data for a rate limiter, which uses the data to
 * determine wait duration. Keeps track of:
 *
 *  - Time at which the last request was made
 *  - The allowed interval between the last and next request
 */
class DeskRateLimitProvider implements RateLimitProvider
{
    /**
     * Returns when the last request was made.
     *
     * @return float|null When the last request was made.
     */
    public function getLastRequestTime()
    {
        // This is just an example, it's up to you to store the time of the
        // most recent request, whether it's in a database or cache driver.
        return $this->cache_get('last_request_time');
    }

    /**
     * Used to set the current time as the last request time to be queried when
     * the next request is attempted.
     */
    public function setLastRequestTime()
    {
        // This is just an example, it's up to you to store the time of the
        // most recent request, whether it's in a database or cache driver.
        return $this->cache_set('last_request_time', microtime(true));
    }

    /**
     * Returns what is considered the time when a given request is being made.
     *
     * @param RequestInterface $request The request being made.
     *
     * @return float Time when the given request is being made.
     */
    public function getRequestTime(RequestInterface $request)
    {
        // The time unit for this value should match the time unit used across
        // this implementation.
        return microtime(true);
    }

    /**
     * Returns the minimum amount of time that is required to have passed since
     * the last request was made. This value is used to determine if the current
     * request should be delayed, based on when the last request was made.
     *
     * Returns the allowed time between the last request and the next, which
     * is used to determine if a request should be delayed and by how much.
     *
     * @param RequestInterface $request The pending request.
     *
     * @return float The minimum amount of time that is required to have passed
     *               since the last request was made (in microseconds).
     */
    public function getRequestAllowance(RequestInterface $request)
    {
        // This is just an example, it's up to you to store the request 
        // allowance, whether it's in a database or cache driver.
        return $this->cache_get('request_allowance');
    }

    /**
     * Used to set the minimum amount of time that is required to pass between
     * this request and the next (in microseconds).
     *
     * @param ResponseInterface $response The resolved response.
     */
    public function setRequestAllowance(ResponseInterface $response)
    {
        // Let's also assume that the response contains two headers:
        //     - ratelimit-remaining
        //     - ratelimit-window
        //
        // The first header tells us how many requests we have left in the 
        // current window, the second tells us how many seconds are left in the
        // window before it expires.
        $requests = $response->getHeader('X-Rate-Limit-Remaining');
        $seconds  = $response->getHeader('X-Rate-Limit-Reset');

        if(isset($seconds[0]) && isset($requests[0])) {
          // The allowance is therefore how much time is remaining in our window
          // divided by the number of requests we can still make. This is the 
          // value we need to store to determine if a future request should be 
          // delayed or not.
          $allowance = (float) $seconds[0] / $requests[0];

          // This is just an example, it's up to you to store the request 
          // allowance, whether it's in a database or cache driver.
          $this->cache_set('request_allowance', $allowance);
        }
    }

    private function cache_get($var, $default = null) {
      if(file_exists('.cache/'.$var)) {
        return file_get_contents('.cache/'.$var);
      }
      return $default;
    }

    private function cache_set($var, $value) {
      file_put_contents('.cache/'.$var, $value, LOCK_EX);
    }
}
