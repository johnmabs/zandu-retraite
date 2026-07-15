<?php

namespace App\Repository;

use App\Entity\ContractTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ContractTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContractTemplate::class);
    }

    public function findActive(): ?ContractTemplate
    {
        return $this->findOneBy(['isActive' => true]);
    }
}
