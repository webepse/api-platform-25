<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Customer;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\InvoiceRepository;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use App\Controller\InvoiceIncremationController;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ApiResource(
    paginationEnabled: false,
    paginationItemsPerPage: 10,
    order: ['amount'=>'asc'],
    normalizationContext: [
        'groups' => ['invoices_read']
    ],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Post(
            controller: InvoiceIncremationController::class,
            uriTemplate: '/invoices/{id}/increment',
            name: 'Increment',
            openapi: new Model\Operation(
                summary: 'Incrémente une facture',
                description: "Incrémente le chrono d'une facture donnée"
            )
        ),
        new Put(),
        new Patch(),
        new Delete()
    ],
    denormalizationContext: [
        "disable_type_enforcement" => true
    ]
)]
#[ApiResource(
    uriTemplate: '/customers/{id}/invoices',
    uriVariables: [
        'id' => new Link(fromClass: Customer::class, fromProperty: 'invoices')
    ],
    operations: [ new GetCollection() ],
    normalizationContext: [
        'groups' => ['invoices_subresource']
    ]
)]
#[ApiFilter(OrderFilter::class, properties:["amount","sentAt"])]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read','invoices_subresource'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read','invoices_subresource'])]
    #[Assert\NotBlank(message: "Le montant de la facture est obligatoire")]
    #[Assert\Type(type:"numeric", message:"Le montant de la facture doit être au format numérique")]
    private $amount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: "La date de la facture est obligatoire")]
    #[Assert\Type(type:"datetime",message:"La date doit être au format YYYY-MM-DD")]
    private $sentAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['invoices_read', 'customers_read','invoices_subresource'])]
    #[Assert\NotBlank(message: "Le statut de la facture est obligatoire")]
    #[Assert\Choice(choices:["SENT","PAID","CANCELLED"], message:"Le statut doit être soit SENT, PAID ou CANCELLED")]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invoices_read'])]
    #[Assert\NotBlank(message: "Le client de la facture est obligatoire")]
    private ?Customer $customer = null;

    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read','invoices_subresource'])]
    #[Assert\NotBlank(message: "Le chrono de la facture est obligatoire")]
    #[Assert\Type(type:"integer", message:"Le chrono de la facture doit être au format numérique")]
    private $chrono = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Permet de récupérer le user à qui appartient finalement la facture
     *
     * @return User
     */
    #[Groups(['invoices_read'])]
    public function getUser(): User{
        return $this->customer->getUser();
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount($amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt($sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getChrono(): ?int
    {
        return $this->chrono;
    }

    public function setChrono($chrono): static
    {
        $this->chrono = $chrono;

        return $this;
    }
}
