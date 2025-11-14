<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use App\Repository\ClientRepository;
use App\Repository\QuoteRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/statistics', name: 'api_statistics_')]
class StatisticsController extends AbstractController
{
    public function __construct(
        private ClientRepository $clientRepository,
        private AppointmentRepository $appointmentRepository,
        private QuoteRepository $quoteRepository
    ) {
    }

    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    #[OA\Get(
        path: '/api/statistics/dashboard',
        summary: 'Get dashboard statistics',
        tags: ['Statistics']
    )]
    #[OA\Response(
        response: 200,
        description: 'Dashboard statistics data',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(
                            property: 'appointmentsThisMonth',
                            properties: [
                                new OA\Property(property: 'value', type: 'integer', example: 25),
                                new OA\Property(property: 'change', type: 'string', example: '+4.5%'),
                                new OA\Property(property: 'changeType', type: 'string', example: 'positive')
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'activeClients',
                            properties: [
                                new OA\Property(property: 'value', type: 'integer', example: 150),
                                new OA\Property(property: 'change', type: 'string', example: '+2.1%'),
                                new OA\Property(property: 'changeType', type: 'string', example: 'positive')
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'quotesInProgress',
                            properties: [
                                new OA\Property(property: 'value', type: 'integer', example: 12),
                                new OA\Property(property: 'change', type: 'string', example: '-1.2%'),
                                new OA\Property(property: 'changeType', type: 'string', example: 'negative')
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'revenue',
                            properties: [
                                new OA\Property(property: 'value', type: 'string', example: '45 000€'),
                                new OA\Property(property: 'change', type: 'string', example: '+18%'),
                                new OA\Property(property: 'changeType', type: 'string', example: 'positive')
                            ],
                            type: 'object'
                        )
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function dashboard(): JsonResponse
    {
        $now = new \DateTime();
        $startOfMonth = (clone $now)->modify('first day of this month')->setTime(0, 0, 0);
        $endOfMonth = (clone $now)->modify('last day of this month')->setTime(23, 59, 59);
        $startOfLastMonth = (clone $startOfMonth)->modify('-1 month');
        $endOfLastMonth = (clone $startOfMonth)->modify('-1 second');

        $clientsActive = $this->clientRepository->countByStatus('active');
        $quotesInProgress = $this->quoteRepository->countByStatus('sent');

        // Rendez-vous ce mois (tout le mois, pas seulement jusqu'à maintenant)
        $appointmentsThisMonth = $this->appointmentRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.appointmentDate >= :start')
            ->andWhere('a.appointmentDate <= :end')
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->getQuery()
            ->getSingleScalarResult();

        // Rendez-vous mois dernier (pour calculer le changement)
        $appointmentsLastMonth = $this->appointmentRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.appointmentDate >= :start')
            ->andWhere('a.appointmentDate <= :end')
            ->setParameter('start', $startOfLastMonth)
            ->setParameter('end', $endOfLastMonth)
            ->getQuery()
            ->getSingleScalarResult();

        // Calculer le changement des rendez-vous
        $appointmentsChange = $this->calculatePercentageChange($appointmentsLastMonth, $appointmentsThisMonth);

        // Clients actifs mois dernier (pour calculer le changement)
        $clientsLastMonth = $this->clientRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->andWhere('c.createdAt <= :end')
            ->setParameter('status', 'active')
            ->setParameter('end', $endOfLastMonth)
            ->getQuery()
            ->getSingleScalarResult();

        $clientsChange = $this->calculatePercentageChange($clientsLastMonth, $clientsActive);

        // Devis en cours mois dernier
        $quotesLastMonth = $this->quoteRepository->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->where('q.status = :status')
            ->andWhere('q.createdAt <= :end')
            ->setParameter('status', 'sent')
            ->setParameter('end', $endOfLastMonth)
            ->getQuery()
            ->getSingleScalarResult();

        $quotesChange = $this->calculatePercentageChange($quotesLastMonth, $quotesInProgress);

        // CA ce mois
        $quotesThisMonth = $this->quoteRepository->createQueryBuilder('q')
            ->select('SUM(q.amount)')
            ->where('q.createdAt >= :start')
            ->andWhere('q.status = :status')
            ->setParameter('start', $startOfMonth)
            ->setParameter('status', 'signed')
            ->getQuery()
            ->getSingleScalarResult();

        $revenue = $quotesThisMonth ?: 0;

        // CA mois dernier
        $quotesLastMonthRevenue = $this->quoteRepository->createQueryBuilder('q')
            ->select('SUM(q.amount)')
            ->where('q.createdAt >= :start')
            ->andWhere('q.createdAt <= :end')
            ->andWhere('q.status = :status')
            ->setParameter('start', $startOfLastMonth)
            ->setParameter('end', $endOfLastMonth)
            ->setParameter('status', 'signed')
            ->getQuery()
            ->getSingleScalarResult();

        $revenueLastMonth = $quotesLastMonthRevenue ?: 0;
        $revenueChange = $this->calculatePercentageChange($revenueLastMonth, $revenue);

        return $this->json([
            'status' => 200,
            'data' => [
                'appointmentsThisMonth' => [
                    'value' => (int) $appointmentsThisMonth,
                    'change' => $appointmentsChange['text'],
                    'changeType' => $appointmentsChange['type']
                ],
                'activeClients' => [
                    'value' => $clientsActive,
                    'change' => $clientsChange['text'],
                    'changeType' => $clientsChange['type']
                ],
                'quotesInProgress' => [
                    'value' => $quotesInProgress,
                    'change' => $quotesChange['text'],
                    'changeType' => $quotesChange['type']
                ],
                'revenue' => [
                    'value' => number_format((float) $revenue, 0, ',', ' ') . '€',
                    'change' => $revenueChange['text'],
                    'changeType' => $revenueChange['type']
                ]
            ]
        ]);
    }

    private function calculatePercentageChange($oldValue, $newValue): array
    {
        if ($oldValue == 0) {
            if ($newValue > 0) {
                return ['text' => '+100%', 'type' => 'positive'];
            }
            return ['text' => '0%', 'type' => 'neutral'];
        }

        $change = (($newValue - $oldValue) / $oldValue) * 100;
        $changeRounded = round($change, 1);

        return [
            'text' => ($changeRounded >= 0 ? '+' : '') . $changeRounded . '%',
            'type' => $changeRounded > 0 ? 'positive' : ($changeRounded < 0 ? 'negative' : 'neutral')
        ];
    }

    #[Route('/performance', name: 'performance', methods: ['GET'])]
    #[OA\Get(
        path: '/api/statistics/performance',
        summary: 'Get performance statistics',
        tags: ['Statistics']
    )]
    #[OA\Response(
        response: 200,
        description: 'Performance statistics data',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(
                            property: 'visitsCompleted',
                            properties: [
                                new OA\Property(property: 'current', type: 'integer', example: 18),
                                new OA\Property(property: 'total', type: 'integer', example: 25),
                                new OA\Property(property: 'percentage', type: 'number', format: 'float', example: 72.0)
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'quotesSigned',
                            properties: [
                                new OA\Property(property: 'current', type: 'integer', example: 5),
                                new OA\Property(property: 'total', type: 'integer', example: 8),
                                new OA\Property(property: 'percentage', type: 'number', format: 'float', example: 62.5)
                            ],
                            type: 'object'
                        ),
                        new OA\Property(property: 'conversionRate', type: 'number', format: 'float', example: 62.5),
                        new OA\Property(property: 'estimatedCommission', type: 'integer', example: 2850)
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function performance(): JsonResponse
    {
        $totalVisits = 25;
        $completedVisits = 18;
        $totalQuotes = 8;
        $signedQuotes = $this->quoteRepository->countByStatus('signed');

        $conversionRate = $totalQuotes > 0 ? round(($signedQuotes / $totalQuotes) * 100, 1) : 0;
        $estimatedCommission = $signedQuotes * 570;

        return $this->json([
            'status' => 200,
            'data' => [
                'visitsCompleted' => [
                    'current' => $completedVisits,
                    'total' => $totalVisits,
                    'percentage' => round(($completedVisits / $totalVisits) * 100, 1)
                ],
                'quotesSigned' => [
                    'current' => $signedQuotes,
                    'total' => $totalQuotes,
                    'percentage' => round(($signedQuotes / $totalQuotes) * 100, 1)
                ],
                'conversionRate' => $conversionRate,
                'estimatedCommission' => $estimatedCommission
            ]
        ]);
    }
}
