<?php

namespace Ady\Bundle\MaintenanceBundle\Tests\Maintenance;

use Ady\Bundle\MaintenanceBundle\Drivers\MemCacheDriver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Test mem cache.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class MemCacheTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructWithNotKeyName()
    {
        $this->expectException(\InvalidArgumentException::class);
        new MemCacheDriver([]);
    }

    public function testConstructWithNotHost()
    {
        $this->expectException(\InvalidArgumentException::class);
        new MemCacheDriver(['key_name' => 'mnt']);
    }

    public function testConstructWithNotPort()
    {
        $this->expectException(\InvalidArgumentException::class);
        new MemCacheDriver(['key_name' => 'mnt', 'host' => '127.0.0.1']);
    }

    public function testConstructWithNotPortNumber()
    {
        $this->expectException(\InvalidArgumentException::class);
        new MemCacheDriver(['key_name' => 'mnt', 'host' => '127.0.0.1', 'port' => 'roti']);
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
}
