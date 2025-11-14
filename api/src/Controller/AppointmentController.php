<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
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

#[Route('/api/appointments', name: 'api_appointments_')]
class AppointmentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AppointmentRepository $appointmentRepository,
        private ClientRepository $clientRepository,
        private UserRepository $userRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/appointments',
        summary: 'Get list of appointments',
        tags: ['Appointments']
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
        schema: new OA\Schema(type: 'string', enum: ['scheduled', 'completed', 'cancelled'])
    )]
    #[OA\Parameter(
        name: 'type',
        in: 'query',
        description: 'Filter by type',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['Installation', 'Consultation', 'Maintenance', 'Follow-up'])
    )]
    #[OA\Response(
        response: 200,
        description: 'List of appointments',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'appointmentDate', type: 'string', format: 'date-time', example: '2025-11-14T14:00:00+00:00'),
                            new OA\Property(property: 'type', type: 'string', example: 'Installation'),
                            new OA\Property(property: 'status', type: 'string', example: 'scheduled'),
                            new OA\Property(property: 'address', type: 'string', example: '15 rue Victor Hugo, Lyon'),
                            new OA\Property(property: 'notes', type: 'string', example: 'Client prefers afternoon appointments'),
                            new OA\Property(
                                property: 'client',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'fullName', type: 'string', example: 'Jean Dupont'),
                                    new OA\Property(property: 'phone', type: 'string', example: '0612345678')
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
                )
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));
        $status = $request->query->get('status');
        $type = $request->query->get('type');

        $appointments = $this->appointmentRepository->findWithFilters($status, $type, $page, $limit);

        $data = array_map(function (Appointment $appointment) {
            return [
                'id' => $appointment->getId(),
                'appointmentDate' => $appointment->getAppointmentDate()?->format('c'),
                'type' => $appointment->getType(),
                'status' => $appointment->getStatus(),
                'address' => $appointment->getAddress(),
                'notes' => $appointment->getNotes(),
                'client' => [
                    'id' => $appointment->getClient()?->getId(),
                    'fullName' => $appointment->getClient()?->getFullName(),
                    'phone' => $appointment->getClient()?->getPhone(),
                ],
                'user' => [
                    'id' => $appointment->getUser()?->getId(),
                    'fullName' => $appointment->getUser()?->getFullName(),
                ],
                'createdAt' => $appointment->getCreatedAt()?->format('c'),
            ];
        }, $appointments);

        return $this->json([
            'status' => 200,
            'data' => $data
        ]);
    }

    #[Route('/today', name: 'today', methods: ['GET'])]
    #[OA\Get(
        path: '/api/appointments/today',
        summary: 'Get today\'s appointments',
        tags: ['Appointments']
    )]
    #[OA\Response(
        response: 200,
        description: 'Today\'s appointments',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'appointmentDate', type: 'string', format: 'date-time', example: '2025-11-13T14:00:00+00:00'),
                            new OA\Property(property: 'time', type: 'string', example: '14:00'),
                            new OA\Property(property: 'type', type: 'string', example: 'Installation'),
                            new OA\Property(property: 'status', type: 'string', example: 'scheduled'),
                            new OA\Property(property: 'address', type: 'string', example: '15 rue Victor Hugo, Lyon'),
                            new OA\Property(
                                property: 'client',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'fullName', type: 'string', example: 'Jean Dupont'),
                                    new OA\Property(property: 'phone', type: 'string', example: '0612345678')
                                ],
                                type: 'object'
                            )
                        ],
                        type: 'object'
                    )
                )
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function today(): JsonResponse
    {
        $appointments = $this->appointmentRepository->findTodayAppointments();

        $data = array_map(function (Appointment $appointment) {
            return [
                'id' => $appointment->getId(),
                'appointmentDate' => $appointment->getAppointmentDate()?->format('c'),
                'time' => $appointment->getAppointmentDate()?->format('H:i'),
                'type' => $appointment->getType(),
                'status' => $appointment->getStatus(),
                'address' => $appointment->getAddress(),
                'client' => [
                    'id' => $appointment->getClient()?->getId(),
                    'fullName' => $appointment->getClient()?->getFullName(),
                    'phone' => $appointment->getClient()?->getPhone(),
                ],
            ];
        }, $appointments);

        return $this->json([
            'status' => 200,
            'data' => $data
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/appointments/{id}',
        summary: 'Get appointment by ID',
        tags: ['Appointments']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Appointment ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Appointment details',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'appointmentDate', type: 'string', format: 'date-time', example: '2025-11-14T14:00:00+00:00'),
                        new OA\Property(property: 'type', type: 'string', example: 'Installation'),
                        new OA\Property(property: 'status', type: 'string', example: 'scheduled'),
                        new OA\Property(property: 'address', type: 'string', example: '15 rue Victor Hugo, Lyon'),
                        new OA\Property(property: 'notes', type: 'string', example: 'Client prefers afternoon appointments'),
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
                        )
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Appointment not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'error', type: 'string', example: 'Appointment not found')
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function show(int $id): JsonResponse
    {
        $appointment = $this->appointmentRepository->find($id);

        if (!$appointment) {
            return $this->json([
                'status' => 404,
                'error' => 'Appointment not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'status' => 200,
            'data' => [
                'id' => $appointment->getId(),
                'appointmentDate' => $appointment->getAppointmentDate()?->format('c'),
                'type' => $appointment->getType(),
                'status' => $appointment->getStatus(),
                'address' => $appointment->getAddress(),
                'notes' => $appointment->getNotes(),
                'client' => [
                    'id' => $appointment->getClient()?->getId(),
                    'fullName' => $appointment->getClient()?->getFullName(),
                ],
                'user' => [
                    'id' => $appointment->getUser()?->getId(),
                    'fullName' => $appointment->getUser()?->getFullName(),
                ],
            ]
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/appointments',
        summary: 'Create new appointment',
        tags: ['Appointments']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['clientId', 'userId', 'appointmentDate', 'type', 'address'],
            properties: [
                new OA\Property(property: 'clientId', type: 'integer', example: 1),
                new OA\Property(property: 'userId', type: 'integer', example: 1),
                new OA\Property(property: 'appointmentDate', type: 'string', format: 'date-time', example: '2025-11-14T14:00:00'),
                new OA\Property(property: 'type', type: 'string', example: 'Installation'),
                new OA\Property(property: 'status', type: 'string', example: 'scheduled'),
                new OA\Property(property: 'address', type: 'string', example: '15 rue Victor Hugo, Lyon'),
                new OA\Property(property: 'notes', type: 'string', example: 'Client prefers afternoon appointments')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Appointment created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 201),
                new OA\Property(property: 'message', type: 'string', example: 'Appointment created successfully'),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1)
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

        $appointment = new Appointment();

        if (isset($data['appointmentDate'])) {
            $appointment->setAppointmentDate(new \DateTime($data['appointmentDate']));
        }

        $appointment->setType($data['type'] ?? '');
        $appointment->setStatus($data['status'] ?? 'scheduled');
        $appointment->setAddress($data['address'] ?? null);
        $appointment->setNotes($data['notes'] ?? null);

        if (isset($data['clientId'])) {
            $client = $this->clientRepository->find($data['clientId']);
            if (!$client) {
                return $this->json([
                    'status' => 400,
                    'error' => 'Client not found'
                ], Response::HTTP_BAD_REQUEST);
            }
            $appointment->setClient($client);
        }

        if (isset($data['userId'])) {
            $user = $this->userRepository->find($data['userId']);
            if (!$user) {
                return $this->json([
                    'status' => 400,
                    'error' => 'User not found'
                ], Response::HTTP_BAD_REQUEST);
            }
            $appointment->setUser($user);
        }

        $errors = $this->validator->validate($appointment);
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

        $this->entityManager->persist($appointment);
        $this->entityManager->flush();

        return $this->json([
            'status' => 201,
            'message' => 'Appointment created successfully',
            'data' => ['id' => $appointment->getId()]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[OA\Patch(
        path: '/api/appointments/{id}',
        summary: 'Update appointment',
        tags: ['Appointments']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Appointment ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'appointmentDate', type: 'string', format: 'date-time', example: '2025-11-15T10:00:00'),
                new OA\Property(property: 'type', type: 'string', example: 'Consultation'),
                new OA\Property(property: 'status', type: 'string', example: 'completed'),
                new OA\Property(property: 'address', type: 'string', example: '20 avenue de la République, Paris'),
                new OA\Property(property: 'notes', type: 'string', example: 'Updated notes')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Appointment updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(property: 'message', type: 'string', example: 'Appointment updated successfully')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Appointment not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'error', type: 'string', example: 'Appointment not found')
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function update(int $id, Request $request): JsonResponse
    {
        $appointment = $this->appointmentRepository->find($id);

        if (!$appointment) {
            return $this->json([
                'status' => 404,
                'error' => 'Appointment not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json([
                'status' => 400,
                'error' => 'Invalid JSON'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['appointmentDate'])) {
            $appointment->setAppointmentDate(new \DateTime($data['appointmentDate']));
        }
        if (isset($data['type'])) $appointment->setType($data['type']);
        if (isset($data['status'])) $appointment->setStatus($data['status']);
        if (isset($data['address'])) $appointment->setAddress($data['address']);
        if (isset($data['notes'])) $appointment->setNotes($data['notes']);

        $errors = $this->validator->validate($appointment);
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
            'message' => 'Appointment updated successfully'
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/appointments/{id}',
        summary: 'Delete appointment',
        tags: ['Appointments']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Appointment ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Appointment deleted successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(property: 'message', type: 'string', example: 'Appointment deleted successfully')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Appointment not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'error', type: 'string', example: 'Appointment not found')
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function delete(int $id): JsonResponse
    {
        $appointment = $this->appointmentRepository->find($id);

        if (!$appointment) {
            return $this->json([
                'status' => 404,
                'error' => 'Appointment not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($appointment);
        $this->entityManager->flush();

        return $this->json([
            'status' => 200,
            'message' => 'Appointment deleted successfully'
        ]);
    }
}
