<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
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
    public function show(int $id): Response
    {
        $event = $this->getDoctrine()->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        return $this->render('events/show.html.twig', [
            'event' => $event,
        ]);
    }
}
