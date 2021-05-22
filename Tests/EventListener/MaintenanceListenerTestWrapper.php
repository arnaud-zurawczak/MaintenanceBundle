<?php

namespace Ady\Bundle\MaintenanceBundle\Tests\EventListener;

use Ady\Bundle\MaintenanceBundle\Exception\ServiceUnavailableException;
use Ady\Bundle\MaintenanceBundle\Listener\MaintenanceListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class MaintenanceListenerTestWrapper extends MaintenanceListener
{
    /**
     * {@inheritdoc}
     */
    public function onKernelRequest(RequestEvent $event)
    {
        try {
            parent::onKernelRequest($event);
        } catch (ServiceUnavailableException $e) {
            return false;
        }

        return true;
    }
}
