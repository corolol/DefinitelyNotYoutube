<?php

namespace App\Listeners;

use Symfony\Component\HttpKernel\Event\TerminateEvent;

class OnVideoUploadListener {
    public function onKernelTerminate(TerminateEvent $event) {
        if (!$event->isMainRequest()) {
            return;
        }
    }
}