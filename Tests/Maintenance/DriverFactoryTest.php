<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\Maintenance;

use Lexik\Bundle\MaintenanceBundle\Drivers\DatabaseDriver;
use Lexik\Bundle\MaintenanceBundle\Drivers\DriverFactory;
use Lexik\Bundle\MaintenanceBundle\Drivers\FileDriver;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Test driver factory
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DriverFactoryTest extends \PHPUnit\Framework\TestCase
{
    protected $factory;
    protected $container;

    public function setUp(): void
    {
        $driverOptions = array(
            'class' => FileDriver::class,
            'options' => array('file_path' => sys_get_temp_dir().'/lock'));

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $this->factory);
    }

    protected function tearDown(): void
    {
        $this->factory = null;
    }

    public function testDriver()
    {
        $driver = $this->factory->getDriver();
        $this->assertInstanceOf(FileDriver::class, $driver);
    }

    public function testExceptionConstructor()
    {
        $this->expectException(\ErrorException::class);
        new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), array());
    }

    public function testWithDatabaseChoice()
    {
        $driverOptions = array('class' => DriverFactory::DATABASE_DRIVER, 'options' => null);

        $factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), $driverOptions);

        $this->container->set('lexik_maintenance.driver.factory', $factory);

        $this->assertInstanceOf(DatabaseDriver::class, $factory->getDriver());
    }

    public function testExceptionGetDriver()
    {
        $driverOptions = array('class' => '\Unknown', 'options' => null);

        $factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), $driverOptions);
        $this->container->set('lexik_maintenance.driver.factory', $factory);

        $this->expectException(\ErrorException::class);
        $factory->getDriver();
    }

    protected function initContainer()
    {
        return new ContainerBuilder(new ParameterBag(array(
            'kernel.debug'          => false,
            'kernel.bundles'        => ['MaintenanceBundle' => 'Lexik\Bundle\MaintenanceBundle'],
            'kernel.cache_dir'      => sys_get_temp_dir(),
            'kernel.environment'    => 'dev',
            'kernel.root_dir'       => __DIR__.'/../../../../', // src dir
            'kernel.default_locale' => 'fr',
        )));
    }

    protected function getDatabaseDriver()
    {
        return $this->getMockbuilder(DatabaseDriver::class)
                ->disableOriginalConstructor()
                ->getMock();
    }

    public function getTranslator()
    {
        return $this->createMock(TranslatorInterface::class);
    }
}
