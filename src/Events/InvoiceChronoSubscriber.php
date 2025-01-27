<?php

namespace App\Events;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvoiceChronoSubscriber implements EventSubscriberInterface
{
    public function __construct(private Security $security, private InvoiceRepository $repo)
    {}

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setChronoForInvoice', EventPriorities::PRE_VALIDATE]
        ];        
    }

    public function setChronoForInvoice(ViewEvent $event)
    {
        $invoice = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if($invoice instanceof Invoice && $method="POST")
        {
            $user = $this->security->getUser();
            $nextChrono = $this->repo->findNextChrono($user);
            $invoice->setChrono($nextChrono);
            // TODO déplacer dans une classe dédiée
            if(empty($invoice->getSentAt()))
            {
                $invoice->setSentAt(new \DateTime());
            }
        }
    }
}
