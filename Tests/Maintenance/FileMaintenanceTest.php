<?php

namespace Lexik\Bundle\MaintenanceBundle\Tests\Maintenance;

use Lexik\Bundle\MaintenanceBundle\Drivers\FileDriver;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Test driver file.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class FileMaintenanceTest extends TestCase
{
    protected static $tmpDir;
    protected $container;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$tmpDir = sys_get_temp_dir().'/symfony2_finder';
    }

    public function setUp(): void
    {
        $this->container = $this->initContainer();
    }

    public function tearDown(): void
    {
        $this->container = null;
    }

    public function testDecide()
    {
        $options = ['file_path' => self::$tmpDir.'/lock.lock'];

        $fileM = new FileDriver($options);
        $fileM->setTranslator($this->getTranslator());

        $this->assertTrue($fileM->decide());

        $options = ['file_path' => self::$tmpDir.'/clok'];

        $fileM2 = new FileDriver($options);
        $fileM2->setTranslator($this->getTranslator());
        $this->assertFalse($fileM2->decide());
    }

    public function testExceptionInvalidPath()
    {
        $this->expectException(\InvalidArgumentException::class);
        $fileM = new FileDriver([]);
        $fileM->setTranslator($this->getTranslator());
    }

    public function testLock()
    {
        $options = ['file_path' => self::$tmpDir.'/lock.lock'];

        $fileM = new FileDriver($options);
        $fileM->setTranslator($this->getTranslator());
        $fileM->lock();

        $this->assertFileExists($options['file_path']);
    }

    public function testUnlock()
    {
        $options = ['file_path' => self::$tmpDir.'/lock.lock'];

        $fileM = new FileDriver($options);
        $fileM->setTranslator($this->getTranslator());
        $fileM->lock();

        $fileM->unlock();

        if ((float) \PHPUnit\Runner\Version::id() < 9.0) {
            $this->assertFileNotExists($options['file_path']);
        } else {
            $this->assertFileDoesNotExist($options['file_path']);
        }
    }

    public function testIsExists()
    {
        $options = ['file_path' => self::$tmpDir.'/lock.lock', 'ttl' => 3600];

        $fileM = new FileDriver($options);
        $fileM->setTranslator($this->getTranslator());
        $fileM->lock();

        $this->assertTrue($fileM->isEndTime(3600));
    }

    public function testMessages()
    {
        $options = ['file_path' => self::$tmpDir.'/lock.lock', 'ttl' => 3600];

        $fileM = new FileDriver($options);
        $fileM->setTranslator($this->getTranslator());
        $fileM->lock();

        // lock
        $this->assertEquals('lexik_maintenance.success_lock_file', $fileM->getMessageLock(true));
        $this->assertEquals('lexik_maintenance.not_success_lock', $fileM->getMessageLock(false));

        // unlock
        $this->assertEquals('lexik_maintenance.success_unlock', $fileM->getMessageUnlock(true));
        $this->assertEquals('lexik_maintenance.not_success_unlock', $fileM->getMessageUnlock(false));
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    protected function initContainer()
    {
        return new ContainerBuilder(new ParameterBag([
            'kernel.debug' => false,
            'kernel.bundles' => ['MaintenanceBundle' => 'Lexik\Bundle\MaintenanceBundle'],
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.environment' => 'dev',
            'kernel.root_dir' => __DIR__.'/../../../../', // src dir
            'kernel.default_locale' => 'fr',
        ]));
    }

    public function getTranslator()
    {
        /** @var TranslatorInterface|MockObject $identityTranslator */
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        return $translator;
    }
}
