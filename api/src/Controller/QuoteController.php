<?php

namespace App\Controller;

use App\Entity\Quote;
use App\Repository\QuoteRepository;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/quotes', name: 'api_quotes_')]
class QuoteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QuoteRepository $quoteRepository,
        private ClientRepository $clientRepository,
        private UserRepository $userRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/quotes',
        summary: 'Get list of quotes',
        tags: ['Quotes']
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Page number',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Items per page',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 10)
    )]
    #[OA\Parameter(
        name: 'status',
        in: 'query',
        description: 'Filter by status',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['draft', 'sent', 'signed', 'rejected', 'all'])
    )]
    #[OA\Response(
        response: 200,
        description: 'List of quotes with pagination',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'reference', type: 'string', example: 'QUOTE-2025-001'),
                            new OA\Property(property: 'amount', type: 'number', format: 'float', example: 15000.00),
                            new OA\Property(property: 'powerKwc', type: 'number', format: 'float', example: 7.5),
                            new OA\Property(property: 'status', type: 'string', example: 'draft'),
                            new OA\Property(property: 'description', type: 'string', example: 'Installation of solar panels'),
                            new OA\Property(property: 'validUntil', type: 'string', format: 'date', example: '2025-12-31'),
                            new OA\Property(property: 'signedAt', type: 'string', format: 'date', example: null, nullable: true),
                            new OA\Property(
                                property: 'client',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'fullName', type: 'string', example: 'Jean Dupont')
                                ],
                                type: 'object'
                            ),
                            new OA\Property(
                                property: 'user',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'fullName', type: 'string', example: 'Marie Martin')
                                ],
                                type: 'object'
                            ),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-11-01T10:00:00+00:00')
                        ],
                        type: 'object'
                    )
                ),
                new OA\Property(
                    property: 'pagination',
                    properties: [
                        new OA\Property(property: 'page', type: 'integer', example: 1),
                        new OA\Property(property: 'limit', type: 'integer', example: 10),
                        new OA\Property(property: 'total', type: 'integer', example: 50),
                        new OA\Property(property: 'pages', type: 'integer', example: 5)
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));
        $status = $request->query->get('status', 'all');

        $quotes = $this->quoteRepository->findByStatusWithPagination($status, $page, $limit);
        $total = $this->quoteRepository->countByStatus($status !== 'all' ? $status : null);

        $data = array_map(function (Quote $quote) {
            return [
                'id' => $quote->getId(),
                'reference' => $quote->getReference(),
                'amount' => $quote->getAmount(),
                'powerKwc' => $quote->getPowerKwc(),
                'status' => $quote->getStatus(),
                'description' => $quote->getDescription(),
                'validUntil' => $quote->getValidUntil()?->format('Y-m-d'),
                'signedAt' => $quote->getSignedAt()?->format('Y-m-d'),
                'client' => [
                    'id' => $quote->getClient()?->getId(),
                    'fullName' => $quote->getClient()?->getFullName(),
                ],
                'user' => [
                    'id' => $quote->getUser()?->getId(),
                    'fullName' => $quote->getUser()?->getFullName(),
                ],
                'createdAt' => $quote->getCreatedAt()?->format('c'),
            ];
        }, $quotes);

        return $this->json([
            'status' => 200,
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/quotes/{id}',
        summary: 'Get quote by ID',
        tags: ['Quotes']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Quote ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Quote details',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'reference', type: 'string', example: 'QUOTE-2025-001'),
                        new OA\Property(property: 'amount', type: 'number', format: 'float', example: 15000.00),
                        new OA\Property(property: 'powerKwc', type: 'number', format: 'float', example: 7.5),
                        new OA\Property(property: 'status', type: 'string', example: 'draft'),
                        new OA\Property(property: 'description', type: 'string', example: 'Installation of solar panels'),
                        new OA\Property(property: 'validUntil', type: 'string', format: 'date', example: '2025-12-31'),
                        new OA\Property(property: 'signedAt', type: 'string', format: 'date', example: null, nullable: true),
                        new OA\Property(
                            property: 'client',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'fullName', type: 'string', example: 'Jean Dupont')
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'user',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'fullName', type: 'string', example: 'Marie Martin')
                            ],
                            type: 'object'
                        ),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-11-01T10:00:00+00:00'),
                        new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time', example: '2025-11-05T15:30:00+00:00')
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Quote not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'error', type: 'string', example: 'Quote not found')
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function show(int $id): JsonResponse
    {
        $quote = $this->quoteRepository->find($id);

        if (!$quote) {
            return $this->json([
                'status' => 404,
                'error' => 'Quote not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'status' => 200,
            'data' => [
                'id' => $quote->getId(),
                'reference' => $quote->getReference(),
                'amount' => $quote->getAmount(),
                'powerKwc' => $quote->getPowerKwc(),
                'status' => $quote->getStatus(),
                'description' => $quote->getDescription(),
                'validUntil' => $quote->getValidUntil()?->format('Y-m-d'),
                'signedAt' => $quote->getSignedAt()?->format('Y-m-d'),
                'client' => [
                    'id' => $quote->getClient()?->getId(),
                    'fullName' => $quote->getClient()?->getFullName(),
                ],
                'user' => [
                    'id' => $quote->getUser()?->getId(),
                    'fullName' => $quote->getUser()?->getFullName(),
                ],
                'createdAt' => $quote->getCreatedAt()?->format('c'),
                'updatedAt' => $quote->getUpdatedAt()?->format('c'),
            ]
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/quotes',
        summary: 'Create new quote',
        tags: ['Quotes']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['clientId', 'userId', 'amount', 'powerKwc'],
            properties: [
                new OA\Property(property: 'clientId', type: 'integer', example: 1),
                new OA\Property(property: 'userId', type: 'integer', example: 1),
                new OA\Property(property: 'amount', type: 'number', format: 'float', example: 15000.00),
                new OA\Property(property: 'powerKwc', type: 'number', format: 'float', example: 7.5),
                new OA\Property(property: 'status', type: 'string', example: 'draft'),
                new OA\Property(property: 'description', type: 'string', example: 'Installation of 20 solar panels'),
                new OA\Property(property: 'validUntil', type: 'string', format: 'date', example: '2025-12-31')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Quote created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 201),
                new OA\Property(property: 'message', type: 'string', example: 'Quote created successfully'),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'reference', type: 'string', example: 'QUOTE-2025-001')
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 400),
                new OA\Property(property: 'error', type: 'string', example: 'Validation error')
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json([
                'status' => 400,
                'error' => 'Invalid JSON'
            ], Response::HTTP_BAD_REQUEST);
        }

        $quote = new Quote();
        $quote->setAmount($data['amount'] ?? '0');
        $quote->setPowerKwc($data['powerKwc'] ?? null);
        $quote->setStatus($data['status'] ?? 'draft');
        $quote->setDescription($data['description'] ?? null);

        if (isset($data['validUntil'])) {
            $quote->setValidUntil(new \DateTimeImmutable($data['validUntil']));
        }

        if (isset($data['clientId'])) {
            $client = $this->clientRepository->find($data['clientId']);
            if (!$client) {
                return $this->json([
                    'status' => 400,
                    'error' => 'Client not found'
                ], Response::HTTP_BAD_REQUEST);
            }
            $quote->setClient($client);
        }

        if (isset($data['userId'])) {
            $user = $this->userRepository->find($data['userId']);
            if (!$user) {
                return $this->json([
                    'status' => 400,
                    'error' => 'User not found'
                ], Response::HTTP_BAD_REQUEST);
            }
            $quote->setUser($user);
        }

        $errors = $this->validator->validate($quote);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json([
                'status' => 400,
                'error' => 'Validation error',
                'details' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($quote);
        $this->entityManager->flush();

        return $this->json([
            'status' => 201,
            'message' => 'Quote created successfully',
            'data' => [
                'id' => $quote->getId(),
                'reference' => $quote->getReference(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[OA\Patch(
        path: '/api/quotes/{id}',
        summary: 'Update quote',
        tags: ['Quotes']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Quote ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'amount', type: 'number', format: 'float', example: 18000.00),
                new OA\Property(property: 'powerKwc', type: 'number', format: 'float', example: 9.0),
                new OA\Property(property: 'status', type: 'string', example: 'sent'),
                new OA\Property(property: 'description', type: 'string', example: 'Updated installation plan'),
                new OA\Property(property: 'validUntil', type: 'string', format: 'date', example: '2026-01-31')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Quote updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(property: 'message', type: 'string', example: 'Quote updated successfully')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Quote not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'error', type: 'string', example: 'Quote not found')
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function update(int $id, Request $request): JsonResponse
    {
        $quote = $this->quoteRepository->find($id);

        if (!$quote) {
            return $this->json([
                'status' => 404,
                'error' => 'Quote not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json([
                'status' => 400,
                'error' => 'Invalid JSON'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['amount'])) $quote->setAmount($data['amount']);
        if (isset($data['powerKwc'])) $quote->setPowerKwc($data['powerKwc']);
        if (isset($data['status'])) $quote->setStatus($data['status']);
        if (isset($data['description'])) $quote->setDescription($data['description']);

        if (isset($data['validUntil'])) {
            $quote->setValidUntil(new \DateTimeImmutable($data['validUntil']));
        }

        $errors = $this->validator->validate($quote);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json([
                'status' => 400,
                'error' => 'Validation error',
                'details' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json([
            'status' => 200,
            'message' => 'Quote updated successfully'
        ]);
    }

    #[Route('/{id}/send', name: 'send', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/quotes/{id}/send',
        summary: 'Send quote to client',
        tags: ['Quotes']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Quote ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Quote sent successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(property: 'message', type: 'string', example: 'Quote sent successfully')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Quote not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'error', type: 'string', example: 'Quote not found')
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function send(int $id): JsonResponse
    {
        $quote = $this->quoteRepository->find($id);

        if (!$quote) {
            return $this->json([
                'status' => 404,
                'error' => 'Quote not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $quote->setStatus('sent');
        $this->entityManager->flush();

        return $this->json([
            'status' => 200,
            'message' => 'Quote sent successfully'
        ]);
    }

    #[Route('/{id}/sign', name: 'sign', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/quotes/{id}/sign',
        summary: 'Sign quote',
        tags: ['Quotes']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Quote ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Quote signed successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(property: 'message', type: 'string', example: 'Quote signed successfully')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Quote not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'error', type: 'string', example: 'Quote not found')
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function sign(int $id): JsonResponse
    {
        $quote = $this->quoteRepository->find($id);

        if (!$quote) {
            return $this->json([
                'status' => 404,
                'error' => 'Quote not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $quote->setStatus('signed');
        $quote->setSignedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->json([
            'status' => 200,
            'message' => 'Quote signed successfully'
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/quotes/{id}',
        summary: 'Delete quote',
        tags: ['Quotes']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Quote ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Quote deleted successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(property: 'message', type: 'string', example: 'Quote deleted successfully')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Quote not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'error', type: 'string', example: 'Quote not found')
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function delete(int $id): JsonResponse
    {
        $quote = $this->quoteRepository->find($id);

        if (!$quote) {
            return $this->json([
                'status' => 404,
                'error' => 'Quote not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($quote);
        $this->entityManager->flush();

        return $this->json([
            'status' => 200,
            'message' => 'Quote deleted successfully'
        ]);
    }
}
