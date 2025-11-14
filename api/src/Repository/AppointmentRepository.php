<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function findTodayAppointments(): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        return $this->createQueryBuilder('a')
            ->where('a.appointmentDate >= :today')
            ->andWhere('a.appointmentDate < :tomorrow')
            ->andWhere('a.status != :cancelled')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('cancelled', 'cancelled')
            ->orderBy('a.appointmentDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithFilters(?string $status = null, ?string $type = null, int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($status) {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $status);
        }

        if ($type) {
            $qb->andWhere('a.type = :type')
               ->setParameter('type', $type);
        }

        return $qb
            ->orderBy('a.appointmentDate', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
