<?php

namespace Ady\Bundle\MaintenanceBundle\Drivers;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Abstract class for drivers.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
abstract class AbstractDriver
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Constructor.
     *
     * @param array $options Array of options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Test if object exists.
     */
    abstract public function isExists(): bool;

    /**
     * Result of creation of lock.
     */
    abstract protected function createLock(): bool;

    /**
     * Result of create unlock.
     */
    abstract protected function createUnlock(): bool;

    /**
     * The feedback message.
     *
     * @param bool $resultTest The result of lock
     */
    abstract public function getMessageLock(bool $resultTest): string;

    /**
     * The feedback message.
     *
     * @param bool $resultTest The result of unlock
     */
    abstract public function getMessageUnlock(bool $resultTest): string;

    /**
     * The response of lock.
     */
    public function lock(): bool
    {
        if (!$this->isExists()) {
            return $this->createLock();
        } else {
            return false;
        }
    }

    /**
     * The response of unlock.
     */
    public function unlock(): bool
    {
        if ($this->isExists()) {
            return $this->createUnlock();
        } else {
            return false;
        }
    }

    /**
     * the choice of the driver to less pass or not the user.
     */
    public function decide(): bool
    {
        return $this->isExists();
    }

    /**
     * Options of driver.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set translatorlator.
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
