<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Appointment;
use App\Entity\Client;
use App\Entity\Quote;
use App\Entity\SolarStudy;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('admin@solarcrm.com');
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');
        $user->setPhone('06 12 34 56 78');
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        $clients = [];
        $clientsData = [
            ['Martin', 'Sophie', 'sophie.martin@email.com', '06 23 45 67 89', '12 rue de la République', '69001', 'Lyon', 'active'],
            ['Rousseau', 'Pierre', 'pierre.rousseau@email.com', '07 34 56 78 90', '45 avenue Jean Jaurès', '69100', 'Villeurbanne', 'prospect'],
            ['Bernard', 'Marie', 'marie.bernard@email.com', '06 45 67 89 01', '8 place Bellecour', '69002', 'Lyon', 'active'],
            ['Lefebvre', 'Thomas', 'thomas.lefebvre@email.com', '07 56 78 90 12', '23 rue Victor Hugo', '69003', 'Lyon', 'active'],
            ['Moreau', 'Julie', 'julie.moreau@email.com', '06 67 89 01 23', '67 cours Lafayette', '69006', 'Lyon', 'prospect'],
            ['Simon', 'Alexandre', 'alexandre.simon@email.com', '07 78 90 12 34', '89 rue Garibaldi', '69007', 'Lyon', 'active'],
            ['Laurent', 'Isabelle', 'isabelle.laurent@email.com', '06 89 01 23 45', '34 rue de la Part-Dieu', '69003', 'Lyon', 'active'],
            ['Michel', 'François', 'francois.michel@email.com', '07 90 12 34 56', '56 avenue des Frères Lumière', '69008', 'Lyon', 'prospect'],
        ];

        foreach ($clientsData as $clientData) {
            $client = new Client();
            $client->setLastName($clientData[0]);
            $client->setFirstName($clientData[1]);
            $client->setEmail($clientData[2]);
            $client->setPhone($clientData[3]);
            $client->setAddress($clientData[4]);
            $client->setPostalCode($clientData[5]);
            $client->setCity($clientData[6]);
            $client->setStatus($clientData[7]);
            $client->setAssignedTo($user);
            $manager->persist($client);
            $clients[] = $client;
        }

        $today = new \DateTime();
        $appointmentsData = [
            [$clients[2], '14:30', 'Installation', 'urgent'],
            [$clients[1], '16:00', 'Visite technique', 'scheduled'],
            [$clients[3], '17:30', 'Signature', 'scheduled'],
        ];

        foreach ($appointmentsData as $apptData) {
            $appointment = new Appointment();
            $appointment->setClient($apptData[0]);
            $appointment->setUser($user);
            $time = explode(':', $apptData[1]);
            $date = (clone $today)->setTime((int)$time[0], (int)$time[1]);
            $appointment->setAppointmentDate($date);
            $appointment->setType($apptData[2]);
            $appointment->setStatus($apptData[3]);
            $appointment->setAddress($apptData[0]->getAddress() . ', ' . $apptData[0]->getCity());
            $manager->persist($appointment);
        }

        $quotesData = [
            [$clients[0], '12500.00', '6.00', 'sent'],
            [$clients[2], '18750.00', '9.00', 'sent'],
            [$clients[3], '15000.00', '7.50', 'signed'],
            [$clients[5], '21000.00', '10.50', 'draft'],
            [$clients[6], '9800.00', '4.80', 'signed'],
        ];

        foreach ($quotesData as $quoteData) {
            $quote = new Quote();
            $quote->setClient($quoteData[0]);
            $quote->setUser($user);
            $quote->setAmount($quoteData[1]);
            $quote->setPowerKwc($quoteData[2]);
            $quote->setStatus($quoteData[3]);
            $quote->setDescription('Installation panneaux photovoltaïques ' . $quoteData[2] . ' kWc');
            $quote->setValidUntil((new \DateTimeImmutable())->modify('+30 days'));
            if ($quoteData[3] === 'signed') {
                $quote->setSignedAt(new \DateTimeImmutable());
            }
            $manager->persist($quote);
        }

        $studiesData = [
            [$clients[0], 'Installation résidentielle Lyon', '45.00', '6.00', '7200.00', '12500.00', '1800.00', 7, 'completed'],
            [$clients[1], 'Projet maison individuelle', '60.00', '9.00', '10800.00', '18750.00', '2700.00', 7, 'in_progress'],
            [$clients[4], 'Étude faisabilité toiture', '30.00', '4.50', '5400.00', '9800.00', '1350.00', 7, 'pending'],
        ];

        foreach ($studiesData as $studyData) {
            $study = new SolarStudy();
            $study->setClient($studyData[0]);
            $study->setProjectName($studyData[1]);
            $study->setRoofSurface($studyData[2]);
            $study->setEstimatedPower($studyData[3]);
            $study->setAnnualProduction($studyData[4]);
            $study->setEstimatedCost($studyData[5]);
            $study->setAnnualSavings($studyData[6]);
            $study->setPaybackPeriod($studyData[7]);
            $study->setStatus($studyData[8]);
            $manager->persist($study);
        }

        $activitiesData = [
            ['rdv', 'Rendez-vous avec M. Bernard', 'completed', $clients[2], 2],
            ['devis', 'Devis #2024-087 envoyé', 'pending', $clients[0], 4],
            ['client', 'Nouveau client : Mme Rousseau', 'new', $clients[1], 24],
        ];

        foreach ($activitiesData as $actData) {
            $activity = new Activity();
            $activity->setType($actData[0]);
            $activity->setTitle($actData[1]);
            $activity->setStatus($actData[2]);
            $activity->setClient($actData[3]);
            $activity->setUser($user);
            $activity->setCreatedAt((new \DateTimeImmutable())->modify('-' . $actData[4] . ' hours'));
            $manager->persist($activity);
        }

        $manager->flush();
    }
}
