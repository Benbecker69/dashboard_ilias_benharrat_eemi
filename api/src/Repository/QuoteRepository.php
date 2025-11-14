<?php

namespace App\Repository;

use App\Entity\Quote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quote>
 */
class QuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quote::class);
    }

    public function findByStatusWithPagination(string $status, int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('q');

        if ($status && $status !== 'all') {
            $qb->where('q.status = :status')
               ->setParameter('status', $status);
        }

        return $qb
            ->orderBy('q.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(?string $status = null): int
    {
        $qb = $this->createQueryBuilder('q')
            ->select('COUNT(q.id)');

        if ($status && $status !== 'all') {
            $qb->where('q.status = :status')
               ->setParameter('status', $status);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
