<?php

namespace Ady\Bundle\MaintenanceBundle\Tests\EventListener;

use Ady\Bundle\MaintenanceBundle\Drivers\DatabaseDriver;
use Ady\Bundle\MaintenanceBundle\Drivers\DriverFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Test for the maintenance listener.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class MaintenanceListenerTest extends TestCase
{
    protected $container;
    protected $factory;

    /**
     * Create request and test the listener
     * for scenarios with permissive firewall
     * and restrictive with no arguments.
     */
    public function testBaseRequest()
    {
        $driverOptions = ['class' => DriverFactory::DATABASE_DRIVER, 'options' => null];

        $request = Request::create('http://test.com/foo?bar=baz');
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(false), $this->getTranslator(), $driverOptions);
        $this->container->set('ady_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory);
        $this->assertTrue($listener->runOnKernelRequestMethod($event), 'Permissive factory should approve without args');

        $listener = new MaintenanceListenerTestWrapper($this->factory, 'path', 'host', ['ip'], ['query'], ['cookie'], 'route');
        $this->assertTrue($listener->runOnKernelRequestMethod($event), 'Permissive factory should approve with args');

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('ady_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny without args');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, [], [], [], null);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny without args');
    }

    /**
     * Create request and test the listener
     * for scenarios with permissive firewall
     * and path filters.
     */
    public function testPathFilter()
    {
        $driverOptions = ['class' => DriverFactory::DATABASE_DRIVER, 'options' => null];

        $request = Request::create('http://test.com/foo?bar=baz');
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('ady_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory, null);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny without path');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '');
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny with empty path');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '/bar');
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny on non matching path');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '/foo');
        $this->assertTrue($listener->runOnKernelRequestMethod($event), 'Restrictive factory should allow on matching path');
    }

    /**
     * Create request and test the listener
     * for scenarios with permissive firewall
     * and path filters.
     */
    public function testHostFilter()
    {
        $driverOptions = ['class' => DriverFactory::DATABASE_DRIVER, 'options' => null];

        $request = Request::create('http://test.com/foo?bar=baz');
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('ady_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny without host');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, '');
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny with empty host');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, 'www.google.com');
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny on non matching host');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, 'test.com');
        $this->assertTrue($listener->runOnKernelRequestMethod($event), 'Restrictive factory should allow on matching host');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '/barfoo', 'test.com');
        $this->assertTrue($listener->runOnKernelRequestMethod($event), 'Restrictive factory should allow on non-matching path and matching host');
    }

    /**
     * Create request and test the listener
     * for scenarios with permissive firewall
     * and ip filters.
     */
    public function testIPFilter()
    {
        $driverOptions = ['class' => DriverFactory::DATABASE_DRIVER, 'options' => null];

        $request = Request::create('http://test.com/foo?bar=baz');
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('ady_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny without ips');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, []);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny with empty ips');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, ['8.8.4.4']);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny on non matching ips');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, ['127.0.0.1']);
        $this->assertTrue($listener->runOnKernelRequestMethod($event), 'Restrictive factory should allow on matching ips');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '/barfoo', 'google.com', ['127.0.0.1']);
        $this->assertTrue($listener->runOnKernelRequestMethod($event), 'Restrictive factory should allow on non-matching path and host and matching ips');
    }

    /**
     * @dataProvider routeProviderWithDebugContext
     */
    public function testRouteFilter($debug, $route, $expected)
    {
        $driverOptions = ['class' => DriverFactory::DATABASE_DRIVER, 'options' => null];

        $request = Request::create('');
        $request->attributes->set('_route', $route);

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('ady_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, [], [], $debug);

        $info = sprintf(
            'Should be %s route %s with when we are %s debug env',
            true === $expected ? 'allow' : 'deny',
            $route,
            true === $debug ? 'in' : 'not in'
        );

        $this->assertTrue($listener->runOnKernelRequestMethod($event) === $expected, $info);
    }

    public function routeProviderWithDebugContext()
    {
        $debug = [true, false];
        $routes = ['route_1', '_route_started_with_underscore'];

        $data = [];

        foreach ($debug as $isDebug) {
            foreach ($routes as $route) {
                $data[] = [$isDebug, $route, (true === $isDebug && '_' === $route[0]) ? false : true];
            }
        }

        return $data;
    }

    /**
     * Create request and test the listener
     * for scenarios with permissive firewall
     * and query filters.
     */
    public function testQueryFilter()
    {
        $driverOptions = ['class' => DriverFactory::DATABASE_DRIVER, 'options' => null];

        $request = Request::create('http://test.com/foo?bar=baz');
        $postRequest = Request::create('http://test.com/foo?bar=baz', 'POST');
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
        $postEvent = new RequestEvent($kernel, $postRequest, HttpKernelInterface::MAIN_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('ady_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, null);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny without query');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, []);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny with empty query');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, ['some' => 'attribute']);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny on non matching query');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, ['attribute']);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny on non matching query');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, ['bar' => 'baz']);
        $this->assertTrue($listener->runOnKernelRequestMethod($event), 'Restrictive factory should allow on matching get query');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, ['bar' => 'baz']);
        $this->assertTrue($listener->runOnKernelRequestMethod($postEvent), 'Restrictive factory should allow on matching post query');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '/barfoo', 'google.com', ['8.8.1.1'], ['bar' => 'baz']);
        $this->assertTrue($listener->runOnKernelRequestMethod($event), 'Restrictive factory should allow on non-matching path, host and ip and matching query');
    }

    /**
     * Create request and test the listener
     * for scenarios with permissive firewall
     * and cookie filters.
     */
    public function testCookieFilter()
    {
        $driverOptions = ['class' => DriverFactory::DATABASE_DRIVER, 'options' => null];

        $request = Request::create('http://test.com/foo', 'GET', [], ['bar' => 'baz']);
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(true), $this->getTranslator(), $driverOptions);
        $this->container->set('ady_maintenance.driver.factory', $this->factory);

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, null, null);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny without cookies');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, null, []);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny with empty cookies');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, null, ['some' => 'attribute']);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny on non matching cookie');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, null, ['attribute']);
        $this->assertFalse($listener->runOnKernelRequestMethod($event), 'Restrictive factory should deny on non matching cookie');

        $listener = new MaintenanceListenerTestWrapper($this->factory, null, null, null, null, ['bar' => 'baz']);
        $this->assertTrue($listener->runOnKernelRequestMethod($event), 'Restrictive factory should allow on matching cookie');

        $listener = new MaintenanceListenerTestWrapper($this->factory, '/barfoo', 'google.com', ['8.8.1.1'], ['bar' => 'baz'], ['bar' => 'baz']);
        $this->assertTrue($listener->runOnKernelRequestMethod($event), 'Restrictive factory should allow on non-matching path, host, ip, query and matching cookie');
    }

    public function tearDown(): void
    {
        $this->container = null;
        $this->factory = null;
    }

    /**
     * Init a container.
     *
     * @return Container
     */
    protected function initContainer()
    {
        return new ContainerBuilder(new ParameterBag([
            'kernel.debug' => false,
            'kernel.bundles' => ['MaintenanceBundle' => 'Ady\Bundle\MaintenanceBundle'],
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.environment' => 'dev',
            'kernel.project_dir' => __DIR__.'/../../../../', // src dir
            'kernel.default_locale' => 'fr',
        ]));
    }

    /**
     * Get a mock DatabaseDriver.
     *
     * @param bool $lock
     *
     * @return MockObject
     */
    protected function getDatabaseDriver($lock = false)
    {
        $db = $this->getMockbuilder(DatabaseDriver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $db->expects($this->any())
            ->method('isExists')
            ->will($this->returnValue($lock));

        $db->expects($this->any())
            ->method('decide')
            ->will($this->returnValue($lock));

        return $db;
    }

    public function getTranslator()
    {
        return $this->createMock(TranslatorInterface::class);
    }
}
