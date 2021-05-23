<?php

namespace Ady\Bundle\MaintenanceBundle\Tests\Maintenance;

use Ady\Bundle\MaintenanceBundle\Drivers\DatabaseDriver;
use Ady\Bundle\MaintenanceBundle\Drivers\DriverFactory;
use Ady\Bundle\MaintenanceBundle\Drivers\FileDriver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Test driver factory.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DriverFactoryTest extends \PHPUnit\Framework\TestCase
{
    protected $factory;
    protected $container;

    public function setUp(): void
    {
        $driverOptions = [
            'class' => FileDriver::class,
            'options' => ['file_path' => sys_get_temp_dir().'/lock'], ];

        $this->container = $this->initContainer();

        $this->factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), $driverOptions);
        $this->container->set('ady_maintenance.driver.factory', $this->factory);
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
        new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), []);
    }

    public function testWithDatabaseChoice()
    {
        $driverOptions = ['class' => DriverFactory::DATABASE_DRIVER, 'options' => null];

        $factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), $driverOptions);

        $this->container->set('ady_maintenance.driver.factory', $factory);

        $this->assertInstanceOf(DatabaseDriver::class, $factory->getDriver());
    }

    public function testExceptionGetDriver()
    {
        $driverOptions = ['class' => '\Unknown', 'options' => null];

        $factory = new DriverFactory($this->getDatabaseDriver(), $this->getTranslator(), $driverOptions);
        $this->container->set('ady_maintenance.driver.factory', $factory);

        $this->expectException(\ErrorException::class);
        $factory->getDriver();
    }

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
