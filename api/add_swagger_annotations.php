<?php
// Script pour ajouter les annotations OpenAPI aux contrôleurs

$controllers = [
    'Client' => [
        'create' => [
            'type' => 'POST',
            'path' => '/api/clients',
            'summary' => 'Créer un client',
            'body' => [
                'required' => ['firstName', 'lastName', 'email', 'phone'],
                'properties' => [
                    'firstName' => ['type' => 'string', 'example' => 'Marie'],
                    'lastName' => ['type' => 'string', 'example' => 'Durand'],
                    'email' => ['type' => 'string', 'example' => 'marie.durand@email.com'],
                    'phone' => ['type' => 'string', 'example' => '06 12 34 56 78'],
                    'address' => ['type' => 'string', 'example' => '15 rue Victor Hugo', 'nullable' => true],
                    'postalCode' => ['type' => 'string', 'example' => '69002', 'nullable' => true],
                    'city' => ['type' => 'string', 'example' => 'Lyon', 'nullable' => true],
                    'status' => ['type' => 'string', 'example' => 'prospect', 'description' => 'prospect, active ou inactive'],
                    'notes' => ['type' => 'string', 'example' => 'Notes sur le client', 'nullable' => true]
                ]
            ]
        ],
        'show' => [
            'type' => 'GET',
            'path' => '/api/clients/{id}',
            'summary' => 'Voir un client',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]]
        ],
        'update' => [
            'type' => 'PATCH',
            'path' => '/api/clients/{id}',
            'summary' => 'Modifier un client',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]],
            'body' => [
                'required' => [],
                'properties' => [
                    'firstName' => ['type' => 'string', 'example' => 'Marie', 'nullable' => true],
                    'lastName' => ['type' => 'string', 'example' => 'Durand', 'nullable' => true],
                    'status' => ['type' => 'string', 'example' => 'active', 'nullable' => true]
                ]
            ]
        ],
        'delete' => [
            'type' => 'DELETE',
            'path' => '/api/clients/{id}',
            'summary' => 'Supprimer un client',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]]
        ]
    ],
    'Appointment' => [
        'list' => [
            'type' => 'GET',
            'path' => '/api/appointments',
            'summary' => 'Liste des rendez-vous',
            'query_params' => [
                ['name' => 'status', 'type' => 'string', 'default' => 'all', 'description' => 'Filtrer par statut'],
                ['name' => 'type', 'type' => 'string', 'description' => 'Filtrer par type'],
                ['name' => 'page', 'type' => 'integer', 'default' => 1],
                ['name' => 'limit', 'type' => 'integer', 'default' => 10]
            ]
        ],
        'today' => [
            'type' => 'GET',
            'path' => '/api/appointments/today',
            'summary' => 'Rendez-vous du jour'
        ],
        'create' => [
            'type' => 'POST',
            'path' => '/api/appointments',
            'summary' => 'Créer un rendez-vous',
            'body' => [
                'required' => ['clientId', 'userId', 'appointmentDate', 'type', 'address'],
                'properties' => [
                    'clientId' => ['type' => 'integer', 'example' => 1],
                    'userId' => ['type' => 'integer', 'example' => 1],
                    'appointmentDate' => ['type' => 'string', 'example' => '2025-11-14T14:00:00', 'description' => 'Format ISO 8601'],
                    'type' => ['type' => 'string', 'example' => 'Installation', 'description' => 'Installation, Visite technique, Signature, SAV, Autre'],
                    'status' => ['type' => 'string', 'example' => 'scheduled', 'description' => 'scheduled, confirmed, urgent, done, cancelled'],
                    'address' => ['type' => 'string', 'example' => '15 rue Victor Hugo, Lyon'],
                    'notes' => ['type' => 'string', 'example' => 'Notes supplémentaires', 'nullable' => true]
                ]
            ]
        ],
        'show' => [
            'type' => 'GET',
            'path' => '/api/appointments/{id}',
            'summary' => 'Voir un rendez-vous',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]]
        ],
        'update' => [
            'type' => 'PATCH',
            'path' => '/api/appointments/{id}',
            'summary' => 'Modifier un rendez-vous',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]],
            'body' => [
                'properties' => [
                    'appointmentDate' => ['type' => 'string', 'example' => '2025-11-14T15:00:00', 'nullable' => true],
                    'status' => ['type' => 'string', 'example' => 'confirmed', 'nullable' => true]
                ]
            ]
        ],
        'delete' => [
            'type' => 'DELETE',
            'path' => '/api/appointments/{id}',
            'summary' => 'Supprimer un rendez-vous',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]]
        ]
    ],
    'Quote' => [
        'list' => [
            'type' => 'GET',
            'path' => '/api/quotes',
            'summary' => 'Liste des devis',
            'query_params' => [
                ['name' => 'status', 'type' => 'string', 'description' => 'Filtrer par statut: draft, sent, signed, rejected'],
                ['name' => 'page', 'type' => 'integer', 'default' => 1],
                ['name' => 'limit', 'type' => 'integer', 'default' => 10]
            ]
        ],
        'create' => [
            'type' => 'POST',
            'path' => '/api/quotes',
            'summary' => 'Créer un devis',
            'body' => [
                'required' => ['clientId', 'userId', 'amount', 'powerKwc'],
                'properties' => [
                    'clientId' => ['type' => 'integer', 'example' => 1],
                    'userId' => ['type' => 'integer', 'example' => 1],
                    'amount' => ['type' => 'number', 'format' => 'float', 'example' => 15000.00],
                    'powerKwc' => ['type' => 'number', 'format' => 'float', 'example' => 7.5, 'description' => 'Puissance en kWc'],
                    'status' => ['type' => 'string', 'example' => 'draft', 'description' => 'draft, sent, signed, rejected'],
                    'description' => ['type' => 'string', 'example' => 'Installation 7.5 kWc', 'nullable' => true]
                ]
            ]
        ],
        'show' => [
            'type' => 'GET',
            'path' => '/api/quotes/{id}',
            'summary' => 'Voir un devis',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]]
        ],
        'update' => [
            'type' => 'PATCH',
            'path' => '/api/quotes/{id}',
            'summary' => 'Modifier un devis',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]],
            'body' => [
                'properties' => [
                    'amount' => ['type' => 'number', 'format' => 'float', 'example' => 16000.00, 'nullable' => true],
                    'status' => ['type' => 'string', 'example' => 'sent', 'nullable' => true]
                ]
            ]
        ],
        'send' => [
            'type' => 'PATCH',
            'path' => '/api/quotes/{id}/send',
            'summary' => 'Envoyer un devis (change status à sent)',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]]
        ],
        'sign' => [
            'type' => 'PATCH',
            'path' => '/api/quotes/{id}/sign',
            'summary' => 'Signer un devis (change status à signed)',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]]
        ],
        'delete' => [
            'type' => 'DELETE',
            'path' => '/api/quotes/{id}',
            'summary' => 'Supprimer un devis',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]]
        ]
    ],
    'SolarStudy' => [
        'list' => [
            'type' => 'GET',
            'path' => '/api/solar-studies',
            'summary' => 'Liste des études solaires',
            'query_params' => [
                ['name' => 'page', 'type' => 'integer', 'default' => 1],
                ['name' => 'limit', 'type' => 'integer', 'default' => 10]
            ]
        ],
        'create' => [
            'type' => 'POST',
            'path' => '/api/solar-studies',
            'summary' => 'Créer une étude solaire',
            'body' => [
                'required' => ['clientId', 'projectName', 'roofSurface', 'estimatedPower'],
                'properties' => [
                    'clientId' => ['type' => 'integer', 'example' => 1],
                    'projectName' => ['type' => 'string', 'example' => 'Installation résidentielle Lyon'],
                    'roofSurface' => ['type' => 'number', 'format' => 'float', 'example' => 50.0, 'description' => 'Surface en m²'],
                    'estimatedPower' => ['type' => 'number', 'format' => 'float', 'example' => 7.5, 'description' => 'Puissance en kWc'],
                    'annualProduction' => ['type' => 'number', 'format' => 'float', 'example' => 8500.0, 'description' => 'Production annuelle en kWh', 'nullable' => true],
                    'estimatedCost' => ['type' => 'number', 'format' => 'float', 'example' => 15000.00, 'nullable' => true],
                    'annualSavings' => ['type' => 'number', 'format' => 'float', 'example' => 1200.00, 'nullable' => true],
                    'paybackPeriod' => ['type' => 'integer', 'example' => 12, 'description' => 'Période de retour en années', 'nullable' => true],
                    'status' => ['type' => 'string', 'example' => 'draft', 'description' => 'draft, completed, sent']
                ]
            ]
        ],
        'show' => [
            'type' => 'GET',
            'path' => '/api/solar-studies/{id}',
            'summary' => 'Voir une étude solaire',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]]
        ],
        'update' => [
            'type' => 'PATCH',
            'path' => '/api/solar-studies/{id}',
            'summary' => 'Modifier une étude solaire',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]],
            'body' => [
                'properties' => [
                    'status' => ['type' => 'string', 'example' => 'completed', 'nullable' => true],
                    'estimatedCost' => ['type' => 'number', 'format' => 'float', 'example' => 16000.00, 'nullable' => true]
                ]
            ]
        ],
        'delete' => [
            'type' => 'DELETE',
            'path' => '/api/solar-studies/{id}',
            'summary' => 'Supprimer une étude solaire',
            'params' => [['name' => 'id', 'type' => 'integer', 'in' => 'path', 'required' => true]]
        ]
    ]
];

echo "Annotations définies pour " . count($controllers) . " contrôleurs\n";
echo "Instructions: Ajouter manuellement les annotations dans chaque contrôleur\n";
