<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\Maintenance;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Lexik\Bundle\MaintenanceBundle\Drivers\MemCacheDriver;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * Test mem cache
 *
 * @package LexikMaintenanceBundle
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
        return new ContainerBuilder(new ParameterBag(array(
            'kernel.debug'          => false,
            'kernel.bundles'        => ['MaintenanceBundle' => 'Lexik\Bundle\MaintenanceBundle'],
            'kernel.cache_dir'      => sys_get_temp_dir(),
            'kernel.environment'    => 'dev',
            'kernel.root_dir'       => __DIR__.'/../../../../', // src dir
            'kernel.default_locale' => 'fr',
        )));
    }
}
