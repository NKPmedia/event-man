<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use TelegramBot\Api\BotApi;

class EventsController extends AbstractController
{
    #[Route('/events')]
    public function index(): Response
    {
        $user = $this->getUser();

        $events = $this->getUser()->getEvents();

        return $this->render('events/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/events/{id}')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $eventRepository = $entityManager->getRepository(Event::class);
        $event = $eventRepository->findOneBy(['id' => $id]);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $registrationRepository = $entityManager->getRepository(Registration::class);

        $registeredUsersPending = $registrationRepository->findBy([
            'event' => $event,
            'status' => Registration::STATUS_PENDING,
        ], ['rank'=>'ASC']);
        $registeredUsersAccepted = $registrationRepository->findBy([
            'event' => $event,
            'status' => Registration::STATUS_ACCEPTED,
        ], ['rank'=>'ASC']);
        $registeredUsersRejected = $registrationRepository->findBy([
            'event' => $event,
            'status' => Registration::STATUS_REJECTED,
        ], ['rank'=>'ASC']);

        return $this->render('events/show.html.twig', [
            'event' => $event,
            'registeredUsersPending' => $registeredUsersPending,
            'registeredUsersAccepted' => $registeredUsersAccepted,
            'registeredUsersRejected' => $registeredUsersRejected,
        ]);
    }
}
