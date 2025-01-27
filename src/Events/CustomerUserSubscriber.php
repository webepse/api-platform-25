<?php
namespace App\Events;

use App\Entity\Customer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CustomerUserSubscriber implements EventSubscriberInterface
{
    public function __construct(private Security $security)
    {}


    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setUserCustomer', EventPriorities::PRE_VALIDATE]
        ];
    }

    public function setUserCustomer(ViewEvent $event)
    {
        $customer = $event->getControllerResult();
        $method= $event->getRequest()->getMethod();

        if($customer instanceof Customer && $method === "POST")
        {
            // récup l'utilisateur connecté (token)
            $user = $this->security->getUser();
            // assigner à customer
            $customer->setUser($user);
        }
    }
}