<?php

namespace App\Doctrine;

use App\Entity\User;
use App\Entity\Invoice;
use App\Entity\Customer;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Metadata\Operation;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security, private AuthorizationCheckerInterface $auth)
    {}

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass)
    {
        // obtenir l'utilisateur 
        $user = $this->security->getUser();

        // si on demande des invoices ou des customers, alors agir sur la requête pour qu'elle tienne compte de l'utilisateur connecté
        if(($resourceClass === Customer::class || $resourceClass === Invoice::class) && !$this->auth->isGranted("ROLE_ADMIN") && $user instanceof User)
        {
          $rootAlias = $queryBuilder->getRootAliases()[0]; // permet de récup le root alias de la queryBuilder; on recup un tab donc [0]
          if($resourceClass === Customer::class)
          {
            $queryBuilder->andWhere($rootAlias.".user = :user");
          }elseif($resourceClass === Invoice::class)
            {
             $queryBuilder->join($rootAlias.".customer","c")
                        ->andWhere("c.user = :user");   
            }
        
            $queryBuilder->setParameter("user",$user);
        }
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }
}

?>