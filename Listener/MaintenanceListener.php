<?php

namespace Lexik\Bundle\MaintenanceBundle\Listener;

use Lexik\Bundle\MaintenanceBundle\Drivers\DriverFactory;
use Lexik\Bundle\MaintenanceBundle\Exception\ServiceUnavailableException;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Listener to decide if user can access to the site.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class MaintenanceListener
{
    /**
     * Service driver factory.
     *
     * @var DriverFactory
     */
    protected $driverFactory;

    /**
     * Authorized data.
     *
     * @var array
     */
    protected $authorizedIps;

    /**
     * @var string|null
     */
    protected $path;

    /**
     * @var string|null
     */
    protected $host;

    /**
     * @var array|null
     */
    protected $ips;

    /**
     * @var array
     */
    protected $query;

    /**
     * @var array
     */
    protected $cookie;

    /**
     * @var string|null
     */
    protected $route;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var int|null
     */
    protected $http_code;

    /**
     * @var string|null
     */
    protected $http_status;

    /**
     * @var string|null
     */
    protected $http_exception_message;

    /**
     * @var bool
     */
    protected $handleResponse = false;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * Constructor Listener.
     *
     * Accepts a driver factory, and several arguments to be compared against the
     * incoming request.
     * When the maintenance mode is enabled, the request will be allowed to bypass
     * it if at least one of the provided arguments is not empty and matches the
     *  incoming request.
     *
     * @param DriverFactory $driverFactory          The driver factory
     * @param string        $path                   A regex for the path
     * @param string        $host                   A regex for the host
     * @param array         $ips                    The list of IP addresses
     * @param array         $query                  Query arguments
     * @param array         $cookie                 Cookies
     * @param string        $route                  Route name
     * @param array         $attributes             Attributes
     * @param int           $http_code              http status code for response
     * @param string        $http_status            http status message for response
     * @param null          $http_exception_message http response page exception message
     * @param bool          $debug
     */
    public function __construct(
        DriverFactory $driverFactory,
        $path = null,
        $host = null,
        $ips = null,
        $query = [],
        $cookie = [],
        $route = null,
        $attributes = [],
        $http_code = null,
        $http_status = null,
        $http_exception_message = null,
        $debug = false
    ) {
        $this->driverFactory = $driverFactory;
        $this->path = $path;
        $this->host = $host;
        $this->ips = $ips;
        $this->query = $query;
        $this->cookie = $cookie;
        $this->route = $route;
        $this->attributes = $attributes;
        $this->http_code = $http_code;
        $this->http_status = $http_status;
        $this->http_exception_message = $http_exception_message;
        $this->debug = $debug;
    }

    /**
     * @return void
     *
     * @throws ServiceUnavailableException
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (is_array($this->query)) {
            foreach ($this->query as $key => $pattern) {
                if (!empty($pattern) && preg_match('{'.$pattern.'}', $request->get($key))) {
                    return;
                }
            }
        }

        if (is_array($this->cookie)) {
            foreach ($this->cookie as $key => $pattern) {
                if (!empty($pattern) && preg_match('{'.$pattern.'}', $request->cookies->get($key))) {
                    return;
                }
            }
        }

        if (is_array($this->attributes)) {
            foreach ($this->attributes as $key => $pattern) {
                if (!empty($pattern) && preg_match('{'.$pattern.'}', $request->attributes->get($key))) {
                    return;
                }
            }
        }

        if (null !== $this->path && !empty($this->path) && preg_match('{'.$this->path.'}', rawurldecode($request->getPathInfo()))) {
            return;
        }

        if (null !== $this->host && !empty($this->host) && preg_match('{'.$this->host.'}i', $request->getHost())) {
            return;
        }

        if (0 !== count((array) $this->ips) && $this->checkIps($request->getClientIp(), $this->ips)) {
            return;
        }

        $route = $request->get('_route');
        if (null !== $this->route && preg_match('{'.$this->route.'}', $route) || (true === $this->debug && '_' === $route[0])) {
            return;
        }

        // Get driver class defined in your configuration
        $driver = $this->driverFactory->getDriver();

        if ($driver->decide() && HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $this->handleResponse = true;
            throw new ServiceUnavailableException($this->http_exception_message);
        }
    }

    /**
     * Rewrites the http code of the response.
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        if ($this->handleResponse && null !== $this->http_code) {
            $response = $event->getResponse();
            $response->setStatusCode($this->http_code, $this->http_status);
        }
    }

    /**
     * Checks if the requested ip is valid.
     *
     * @param string       $requestedIp
     * @param string|array $ips
     *
     * @return bool
     */
    protected function checkIps($requestedIp, $ips)
    {
        $ips = (array) $ips;

        $valid = false;
        $i = 0;

        while ($i < count($ips) && !$valid) {
            $valid = IpUtils::checkIp($requestedIp, $ips[$i]);
            ++$i;
        }

        return $valid;
    }
}
