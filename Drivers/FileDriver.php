<?php

namespace Ady\Bundle\MaintenanceBundle\Drivers;

class FileDriver extends AbstractDriver
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * Constructor.
     *
     * @param array $options Options driver
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (!isset($options['file_path'])) {
            throw new \InvalidArgumentException('The configuration `file_path` must be defined if FileDriver is used');
        }

        $this->filePath = $options['file_path'];
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    protected function createLock(): bool
    {
        return (bool) fopen($this->filePath, 'w+');
    }

    /**
     * {@inheritDoc}
     */
    protected function createUnlock(): bool
    {
        return @unlink($this->filePath);
    }

    /**
     * {@inheritDoc}
     */
    public function isExists(): bool
    {
        if (file_exists($this->filePath)) {
            if (isset($this->options['ttl']) && is_numeric($this->options['ttl'])) {
                $this->isEndTime($this->options['ttl']);
            }

            return true;
        }

        return false;
    }

    /**
     * Test if time to life is expired.
     *
     * @param int $timeTtl The ttl value
     *
     * @throws \Exception
     */
    public function isEndTime(int $timeTtl): bool
    {
        $now = new \DateTime('now');
        $fileTime = filemtime($this->filePath) ?: 0;
        $accessTime = new \DateTime();
        $accessTime->setTimestamp($fileTime);
        $accessTime->modify(sprintf('+%1$d seconds', $timeTtl));

        if ($accessTime < $now) {
            return $this->createUnlock();
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getMessageLock(bool $resultTest): string
    {
        $key = $resultTest ? 'ady_maintenance.success_lock_file' : 'ady_maintenance.not_success_lock';

        return $this->translator->trans($key, [], 'maintenance');
    }

    /**
     * {@inheritDoc}
     */
    public function getMessageUnlock(bool $resultTest): string
    {
        $key = $resultTest ? 'ady_maintenance.success_unlock' : 'ady_maintenance.not_success_unlock';

        return $this->translator->trans($key, [], 'maintenance');
    }
}
