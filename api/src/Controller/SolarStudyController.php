<?php

namespace App\Controller;

use App\Entity\SolarStudy;
use App\Repository\SolarStudyRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/solar-studies', name: 'api_solar_studies_')]
class SolarStudyController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SolarStudyRepository $solarStudyRepository,
        private ClientRepository $clientRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/solar-studies',
        summary: 'Get list of solar studies',
        tags: ['Solar Studies']
    )]
    #[OA\Response(
        response: 200,
        description: 'List of solar studies',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'projectName', type: 'string', example: 'Installation Lyon'),
                            new OA\Property(property: 'roofSurface', type: 'number', format: 'float', example: 50.0),
                            new OA\Property(property: 'estimatedPower', type: 'number', format: 'float', example: 7.5),
                            new OA\Property(property: 'annualProduction', type: 'number', format: 'float', example: 8500.0),
                            new OA\Property(property: 'estimatedCost', type: 'number', format: 'float', example: 15000.00),
                            new OA\Property(property: 'annualSavings', type: 'number', format: 'float', example: 1200.00),
                            new OA\Property(property: 'paybackPeriod', type: 'number', format: 'float', example: 12.5),
                            new OA\Property(property: 'status', type: 'string', example: 'pending'),
                            new OA\Property(
                                property: 'client',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'fullName', type: 'string', example: 'Jean Dupont')
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
    public function list(): JsonResponse
    {
        $studies = $this->solarStudyRepository->findAll();

        $data = array_map(function (SolarStudy $study) {
            return [
                'id' => $study->getId(),
                'projectName' => $study->getProjectName(),
                'roofSurface' => $study->getRoofSurface(),
                'estimatedPower' => $study->getEstimatedPower(),
                'annualProduction' => $study->getAnnualProduction(),
                'estimatedCost' => $study->getEstimatedCost(),
                'annualSavings' => $study->getAnnualSavings(),
                'paybackPeriod' => $study->getPaybackPeriod(),
                'status' => $study->getStatus(),
                'client' => [
                    'id' => $study->getClient()?->getId(),
                    'fullName' => $study->getClient()?->getFullName(),
                ],
                'createdAt' => $study->getCreatedAt()?->format('c'),
            ];
        }, $studies);

        return $this->json([
            'status' => 200,
            'data' => $data
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/solar-studies/{id}',
        summary: 'Get solar study by ID',
        tags: ['Solar Studies']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Solar study ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Solar study details',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'projectName', type: 'string', example: 'Installation Lyon'),
                        new OA\Property(property: 'roofSurface', type: 'number', format: 'float', example: 50.0),
                        new OA\Property(property: 'estimatedPower', type: 'number', format: 'float', example: 7.5),
                        new OA\Property(property: 'annualProduction', type: 'number', format: 'float', example: 8500.0),
                        new OA\Property(property: 'estimatedCost', type: 'number', format: 'float', example: 15000.00),
                        new OA\Property(property: 'annualSavings', type: 'number', format: 'float', example: 1200.00),
                        new OA\Property(property: 'paybackPeriod', type: 'number', format: 'float', example: 12.5),
                        new OA\Property(property: 'status', type: 'string', example: 'pending'),
                        new OA\Property(property: 'notes', type: 'string', example: 'South-facing roof with optimal angle'),
                        new OA\Property(
                            property: 'client',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'fullName', type: 'string', example: 'Jean Dupont')
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
        description: 'Solar study not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'error', type: 'string', example: 'Solar study not found')
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function show(int $id): JsonResponse
    {
        $study = $this->solarStudyRepository->find($id);

        if (!$study) {
            return $this->json([
                'status' => 404,
                'error' => 'Solar study not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'status' => 200,
            'data' => [
                'id' => $study->getId(),
                'projectName' => $study->getProjectName(),
                'roofSurface' => $study->getRoofSurface(),
                'estimatedPower' => $study->getEstimatedPower(),
                'annualProduction' => $study->getAnnualProduction(),
                'estimatedCost' => $study->getEstimatedCost(),
                'annualSavings' => $study->getAnnualSavings(),
                'paybackPeriod' => $study->getPaybackPeriod(),
                'status' => $study->getStatus(),
                'notes' => $study->getNotes(),
                'client' => [
                    'id' => $study->getClient()?->getId(),
                    'fullName' => $study->getClient()?->getFullName(),
                ],
                'createdAt' => $study->getCreatedAt()?->format('c'),
                'updatedAt' => $study->getUpdatedAt()?->format('c'),
            ]
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/solar-studies',
        summary: 'Create new solar study',
        tags: ['Solar Studies']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['clientId', 'projectName', 'roofSurface', 'estimatedPower', 'annualProduction'],
            properties: [
                new OA\Property(property: 'clientId', type: 'integer', example: 1),
                new OA\Property(property: 'projectName', type: 'string', example: 'Installation Lyon'),
                new OA\Property(property: 'roofSurface', type: 'number', format: 'float', example: 50.0),
                new OA\Property(property: 'estimatedPower', type: 'number', format: 'float', example: 7.5),
                new OA\Property(property: 'annualProduction', type: 'number', format: 'float', example: 8500.0),
                new OA\Property(property: 'estimatedCost', type: 'number', format: 'float', example: 15000.00),
                new OA\Property(property: 'annualSavings', type: 'number', format: 'float', example: 1200.00),
                new OA\Property(property: 'paybackPeriod', type: 'number', format: 'float', example: 12.5),
                new OA\Property(property: 'status', type: 'string', example: 'pending'),
                new OA\Property(property: 'notes', type: 'string', example: 'South-facing roof with optimal angle')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Solar study created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 201),
                new OA\Property(property: 'message', type: 'string', example: 'Solar study created successfully'),
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

        $study = new SolarStudy();
        $study->setProjectName($data['projectName'] ?? '');
        $study->setRoofSurface($data['roofSurface'] ?? null);
        $study->setEstimatedPower($data['estimatedPower'] ?? null);
        $study->setAnnualProduction($data['annualProduction'] ?? null);
        $study->setEstimatedCost($data['estimatedCost'] ?? null);
        $study->setAnnualSavings($data['annualSavings'] ?? null);
        $study->setPaybackPeriod($data['paybackPeriod'] ?? null);
        $study->setStatus($data['status'] ?? 'pending');
        $study->setNotes($data['notes'] ?? null);

        if (isset($data['clientId'])) {
            $client = $this->clientRepository->find($data['clientId']);
            if (!$client) {
                return $this->json([
                    'status' => 400,
                    'error' => 'Client not found'
                ], Response::HTTP_BAD_REQUEST);
            }
            $study->setClient($client);
        }

        $errors = $this->validator->validate($study);
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

        $this->entityManager->persist($study);
        $this->entityManager->flush();

        return $this->json([
            'status' => 201,
            'message' => 'Solar study created successfully',
            'data' => ['id' => $study->getId()]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[OA\Patch(
        path: '/api/solar-studies/{id}',
        summary: 'Update solar study',
        tags: ['Solar Studies']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Solar study ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'projectName', type: 'string', example: 'Installation Lyon Updated'),
                new OA\Property(property: 'roofSurface', type: 'number', format: 'float', example: 55.0),
                new OA\Property(property: 'estimatedPower', type: 'number', format: 'float', example: 8.0),
                new OA\Property(property: 'annualProduction', type: 'number', format: 'float', example: 9000.0),
                new OA\Property(property: 'estimatedCost', type: 'number', format: 'float', example: 16000.00),
                new OA\Property(property: 'annualSavings', type: 'number', format: 'float', example: 1300.00),
                new OA\Property(property: 'paybackPeriod', type: 'number', format: 'float', example: 12.0),
                new OA\Property(property: 'status', type: 'string', example: 'completed'),
                new OA\Property(property: 'notes', type: 'string', example: 'Updated roof analysis')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Solar study updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(property: 'message', type: 'string', example: 'Solar study updated successfully')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Solar study not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'error', type: 'string', example: 'Solar study not found')
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function update(int $id, Request $request): JsonResponse
    {
        $study = $this->solarStudyRepository->find($id);

        if (!$study) {
            return $this->json([
                'status' => 404,
                'error' => 'Solar study not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json([
                'status' => 400,
                'error' => 'Invalid JSON'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['projectName'])) $study->setProjectName($data['projectName']);
        if (isset($data['roofSurface'])) $study->setRoofSurface($data['roofSurface']);
        if (isset($data['estimatedPower'])) $study->setEstimatedPower($data['estimatedPower']);
        if (isset($data['annualProduction'])) $study->setAnnualProduction($data['annualProduction']);
        if (isset($data['estimatedCost'])) $study->setEstimatedCost($data['estimatedCost']);
        if (isset($data['annualSavings'])) $study->setAnnualSavings($data['annualSavings']);
        if (isset($data['paybackPeriod'])) $study->setPaybackPeriod($data['paybackPeriod']);
        if (isset($data['status'])) $study->setStatus($data['status']);
        if (isset($data['notes'])) $study->setNotes($data['notes']);

        $errors = $this->validator->validate($study);
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
            'message' => 'Solar study updated successfully'
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/solar-studies/{id}',
        summary: 'Delete solar study',
        tags: ['Solar Studies']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Solar study ID',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Solar study deleted successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 200),
                new OA\Property(property: 'message', type: 'string', example: 'Solar study deleted successfully')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Solar study not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'error', type: 'string', example: 'Solar study not found')
            ]
        )
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function delete(int $id): JsonResponse
    {
        $study = $this->solarStudyRepository->find($id);

        if (!$study) {
            return $this->json([
                'status' => 404,
                'error' => 'Solar study not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($study);
        $this->entityManager->flush();

        return $this->json([
            'status' => 200,
            'message' => 'Solar study deleted successfully'
        ]);
    }
}
