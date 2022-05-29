<?php

namespace Ady\Bundle\MaintenanceBundle\Drivers;

/**
 * Interface DriverTtlInterface.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
interface DriverTtlInterface
{
    /**
     * Set time to life for overwrite basic configuration.
     *
     * @param ?int $value ttl value
     */
    public function setTtl(?int $value): void;

    /**
     * Return time to life.
     *
     * @return ?int
     */
    public function getTtl(): ?int;

    /**
     * Has ttl.
     */
    public function hasTtl(): bool;
}
