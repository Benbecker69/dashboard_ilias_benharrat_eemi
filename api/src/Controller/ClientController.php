<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[Route('/api/clients', name: 'api_clients_')]
class ClientController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClientRepository $clientRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/clients',
        summary: 'Liste des clients avec pagination',
        tags: ['Clients']
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Numéro de page',
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Nombre d\'éléments par page',
        schema: new OA\Schema(type: 'integer', default: 10)
    )]
    #[OA\Parameter(
        name: 'status',
        in: 'query',
        description: 'Filtrer par statut (all, prospect, active, inactive)',
        schema: new OA\Schema(type: 'string', default: 'all')
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des clients récupérée avec succès'
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));
        $status = $request->query->get('status', 'all');
        $search = $request->query->get('search', '');

        $clients = $this->clientRepository->findByStatusWithPagination($status, $page, $limit);
        $total = $this->clientRepository->countByStatus($status !== 'all' ? $status : null);

        $data = array_map(function (Client $client) {
            return [
                'id' => $client->getId(),
                'firstName' => $client->getFirstName(),
                'lastName' => $client->getLastName(),
                'fullName' => $client->getFullName(),
                'email' => $client->getEmail(),
                'phone' => $client->getPhone(),
                'address' => $client->getAddress(),
                'postalCode' => $client->getPostalCode(),
                'city' => $client->getCity(),
                'status' => $client->getStatus(),
                'notes' => $client->getNotes(),
                'createdAt' => $client->getCreatedAt()?->format('c'),
                'updatedAt' => $client->getUpdatedAt()?->format('c'),
            ];
        }, $clients);

        return $this->json([
            'status' => 200,
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ], Response::HTTP_OK, [], ['Cache-Control' => 'max-age=60']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/clients/{id}',
        summary: 'Voir un client',
        tags: ['Clients']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Client récupéré avec succès'
    )]
    #[OA\Response(
        response: 404,
        description: 'Client non trouvé'
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function show(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            return $this->json([
                'status' => 404,
                'error' => 'Client not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'status' => 200,
            'data' => [
                'id' => $client->getId(),
                'firstName' => $client->getFirstName(),
                'lastName' => $client->getLastName(),
                'fullName' => $client->getFullName(),
                'email' => $client->getEmail(),
                'phone' => $client->getPhone(),
                'address' => $client->getAddress(),
                'postalCode' => $client->getPostalCode(),
                'city' => $client->getCity(),
                'status' => $client->getStatus(),
                'notes' => $client->getNotes(),
                'createdAt' => $client->getCreatedAt()?->format('c'),
                'updatedAt' => $client->getUpdatedAt()?->format('c'),
            ]
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/clients',
        summary: 'Créer un client',
        tags: ['Clients']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['firstName', 'lastName', 'email', 'phone'],
            properties: [
                new OA\Property(property: 'firstName', type: 'string', example: 'Marie'),
                new OA\Property(property: 'lastName', type: 'string', example: 'Durand'),
                new OA\Property(property: 'email', type: 'string', example: 'marie.durand@email.com'),
                new OA\Property(property: 'phone', type: 'string', example: '06 12 34 56 78'),
                new OA\Property(property: 'address', type: 'string', example: '15 rue Victor Hugo', nullable: true),
                new OA\Property(property: 'postalCode', type: 'string', example: '69002', nullable: true),
                new OA\Property(property: 'city', type: 'string', example: 'Lyon', nullable: true),
                new OA\Property(property: 'status', type: 'string', example: 'prospect', description: 'prospect, active ou inactive'),
                new OA\Property(property: 'notes', type: 'string', example: 'Notes sur le client', nullable: true)
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Client créé avec succès'
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

        $client = new Client();
        $client->setFirstName($data['firstName'] ?? '');
        $client->setLastName($data['lastName'] ?? '');
        $client->setEmail($data['email'] ?? '');
        $client->setPhone($data['phone'] ?? '');
        $client->setAddress($data['address'] ?? null);
        $client->setPostalCode($data['postalCode'] ?? null);
        $client->setCity($data['city'] ?? null);
        $client->setStatus($data['status'] ?? 'prospect');
        $client->setNotes($data['notes'] ?? null);

        $errors = $this->validator->validate($client);
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

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return $this->json([
            'status' => 201,
            'message' => 'Client created successfully',
            'data' => [
                'id' => $client->getId(),
                'firstName' => $client->getFirstName(),
                'lastName' => $client->getLastName(),
                'email' => $client->getEmail(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[OA\Patch(
        path: '/api/clients/{id}',
        summary: 'Modifier un client',
        tags: ['Clients']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'firstName', type: 'string', example: 'Marie', nullable: true),
                new OA\Property(property: 'lastName', type: 'string', example: 'Durand', nullable: true),
                new OA\Property(property: 'status', type: 'string', example: 'active', nullable: true),
                new OA\Property(property: 'phone', type: 'string', example: '06 99 88 77 66', nullable: true)
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Client modifié avec succès'
    )]
    #[OA\Response(
        response: 404,
        description: 'Client non trouvé'
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function update(int $id, Request $request): JsonResponse
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            return $this->json([
                'status' => 404,
                'error' => 'Client not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json([
                'status' => 400,
                'error' => 'Invalid JSON'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['firstName'])) $client->setFirstName($data['firstName']);
        if (isset($data['lastName'])) $client->setLastName($data['lastName']);
        if (isset($data['email'])) $client->setEmail($data['email']);
        if (isset($data['phone'])) $client->setPhone($data['phone']);
        if (isset($data['address'])) $client->setAddress($data['address']);
        if (isset($data['postalCode'])) $client->setPostalCode($data['postalCode']);
        if (isset($data['city'])) $client->setCity($data['city']);
        if (isset($data['status'])) $client->setStatus($data['status']);
        if (isset($data['notes'])) $client->setNotes($data['notes']);

        $errors = $this->validator->validate($client);
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
            'message' => 'Client updated successfully',
            'data' => [
                'id' => $client->getId(),
                'firstName' => $client->getFirstName(),
                'lastName' => $client->getLastName(),
            ]
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/clients/{id}',
        summary: 'Supprimer un client',
        tags: ['Clients']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Client supprimé avec succès'
    )]
    #[OA\Response(
        response: 404,
        description: 'Client non trouvé'
    )]
    #[OA\Security(name: 'bearerAuth')]
    public function delete(int $id): JsonResponse
    {
        $client = $this->clientRepository->find($id);

        if (!$client) {
            return $this->json([
                'status' => 404,
                'error' => 'Client not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($client);
        $this->entityManager->flush();

        return $this->json([
            'status' => 200,
            'message' => 'Client deleted successfully'
        ]);
    }
}
