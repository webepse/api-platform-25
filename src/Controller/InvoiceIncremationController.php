<?php
namespace App\Controller;

use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class InvoiceIncremationController{

    public function __construct(private EntityManagerInterface $manager)
    {}

    public function __invoke(Invoice $data)
    {
        $data->setChrono($data->getChrono()+1);
        $this->manager->persist($data);
        $this->manager->flush();
        // retourner les données modifiée en JSON
        return new JsonResponse([
            'id' => $data->getId(),
            'chrono' => $data->getChrono(),
            'message' => 'Invoice chrnono incremented successfully'
        ]);
    }

}