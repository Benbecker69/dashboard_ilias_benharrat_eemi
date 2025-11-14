<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[Route('/api/activities', name: 'api_activities_')]
class ActivityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ActivityRepository $activityRepository,
        private ClientRepository $clientRepository,
        private UserRepository $userRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/activities',
        summary: 'Liste des activités récentes',
        tags: ['Activités']
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Nombre maximum d\'activités',
        schema: new OA\Schema(type: 'integer', default: 10)
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des activités récupérée avec succès'
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function list(Request $request): JsonResponse
    {
        $limit = min(50, max(1, (int) $request->query->get('limit', 10)));
        $activities = $this->activityRepository->findRecent($limit);

        $data = array_map(function (Activity $activity) {
            return [
                'id' => $activity->getId(),
                'type' => $activity->getType(),
                'title' => $activity->getTitle(),
                'description' => $activity->getDescription(),
                'status' => $activity->getStatus(),
                'client' => $activity->getClient() ? [
                    'id' => $activity->getClient()->getId(),
                    'fullName' => $activity->getClient()->getFullName(),
                ] : null,
                'user' => $activity->getUser() ? [
                    'id' => $activity->getUser()->getId(),
                    'fullName' => $activity->getUser()->getFullName(),
                ] : null,
                'time' => $activity->getRelativeTime(),
                'createdAt' => $activity->getCreatedAt()?->format('c'),
            ];
        }, $activities);

        return $this->json([
            'status' => 200,
            'data' => $data
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $activity = $this->activityRepository->find($id);

        if (!$activity) {
            return $this->json([
                'status' => 404,
                'error' => 'Activity not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'status' => 200,
            'data' => [
                'id' => $activity->getId(),
                'type' => $activity->getType(),
                'title' => $activity->getTitle(),
                'description' => $activity->getDescription(),
                'status' => $activity->getStatus(),
                'createdAt' => $activity->getCreatedAt()?->format('c'),
            ]
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/activities',
        summary: 'Créer une activité',
        tags: ['Activités']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['type', 'title'],
            properties: [
                new OA\Property(property: 'type', type: 'string', example: 'rdv', description: 'Type: rdv, devis, client, study, other'),
                new OA\Property(property: 'title', type: 'string', example: 'Rendez-vous confirmé'),
                new OA\Property(property: 'description', type: 'string', example: 'Le client a confirmé le rendez-vous', nullable: true),
                new OA\Property(property: 'status', type: 'string', example: 'new', description: 'Status: new, in_progress, done'),
                new OA\Property(property: 'userId', type: 'integer', example: 1, nullable: true),
                new OA\Property(property: 'clientId', type: 'integer', example: 1, nullable: true)
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Activité créée avec succès'
    )]
    #[OA\Response(
        response: 400,
        description: 'Erreur de validation'
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

        $activity = new Activity();
        $activity->setType($data['type'] ?? '');
        $activity->setTitle($data['title'] ?? '');
        $activity->setDescription($data['description'] ?? null);
        $activity->setStatus($data['status'] ?? 'new');

        if (isset($data['userId'])) {
            $user = $this->userRepository->find($data['userId']);
            if ($user) {
                $activity->setUser($user);
            }
        }

        if (isset($data['clientId'])) {
            $client = $this->clientRepository->find($data['clientId']);
            if ($client) {
                $activity->setClient($client);
            }
        }

        $errors = $this->validator->validate($activity);
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

        $this->entityManager->persist($activity);
        $this->entityManager->flush();

        return $this->json([
            'status' => 201,
            'message' => 'Activity created successfully',
            'data' => ['id' => $activity->getId()]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $activity = $this->activityRepository->find($id);

        if (!$activity) {
            return $this->json([
                'status' => 404,
                'error' => 'Activity not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($activity);
        $this->entityManager->flush();

        return $this->json([
            'status' => 200,
            'message' => 'Activity deleted successfully'
        ]);
    }
}
