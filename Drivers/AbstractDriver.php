<?php

namespace Lexik\Bundle\MaintenanceBundle\Drivers;

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
     * @return bool
     */
    abstract public function isExists();

    /**
     * Result of creation of lock.
     *
     * @return bool
     */
    abstract protected function createLock();

    /**
     * Result of create unlock.
     *
     * @return bool
     */
    abstract protected function createUnlock();

    /**
     * The feedback message.
     *
     * @param bool $resultTest The result of lock
     *
     * @return string
     */
    abstract public function getMessageLock($resultTest);

    /**
     * The feedback message.
     *
     * @param bool $resultTest The result of unlock
     *
     * @return string
     */
    abstract public function getMessageUnlock($resultTest);

    /**
     * The response of lock.
     *
     * @return bool
     */
    public function lock()
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
    public function unlock()
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
    public function decide()
    {
        return $this->isExists();
    }

    /**
     * Options of driver.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set translatorlator.
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}
