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
     *
     * @return ?bool
     */
    abstract public function isExists(): ?bool;

    /**
     * Result of creation of lock.
     *
     * @return bool
     */
    abstract protected function createLock(): bool;

    /**
     * Result of create unlock.
     *
     * @return bool
     */
    abstract protected function createUnlock(): bool;

    /**
     * The feedback message.
     *
     * @param bool $resultTest The result of lock
     *
     * @return string
     */
    abstract public function getMessageLock(bool $resultTest): string;

    /**
     * The feedback message.
     *
     * @param bool $resultTest The result of unlock
     *
     * @return string
     */
    abstract public function getMessageUnlock(bool $resultTest): string;

    /**
     * The response of lock.
     *
     * @return bool
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
     *
     * @return bool
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
     *
     * @return bool
     */
    public function decide(): bool
    {
        return $this->isExists();
    }

    /**
     * Options of driver.
     *
     * @return array
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
