<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Registration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use TelegramBot\Api\BotApi;

class RegistrationController extends AbstractController
{
    #[Route('/registration/{id}/accpet', name: 'registration_accept')]
    public function registration_accept(EntityManagerInterface $entityManager, int $id, BotApi $api): Response
    {
        #Check if user is admin of the event
        $registration = $entityManager->getRepository(Registration::class)->find($id);
        $admin_id = $registration->getEvent()->getAdmin()->getId();
        if ($admin_id != $this->getUser()->getId()) {
            $this->createAccessDeniedException("You are not admin of this event.");
        }

        $registration->setStatus(Registration::STATUS_ACCEPTED);
        $entityManager->persist($registration);
        $entityManager->flush();

        $api->sendMessage($registration->getTelegramChatId(),
            "Your registration for event " . $registration->getEvent()->getName() . " has been accepted."
        );

        $api->sendMessage($registration->getTelegramChatId(),
            "Join the group: " . $api->getChat($registration->getEvent()->getTelegramGroup())->getInviteLink()
        );

        return $this->redirectToRoute('app_events_show', ['id' => $registration->getEvent()->getId()]);
    }

    #[Route('/registration/{id}/reject', name: 'registration_reject')]
    public function registration_reject(EntityManagerInterface $entityManager, int $id, BotApi $api): Response
    {
        #Check if user is admin of the event
        $registration = $entityManager->getRepository(Registration::class)->find($id);
        $admin_id = $registration->getEvent()->getAdmin()->getId();
        if ($admin_id != $this->getUser()->getId()) {
            $this->createAccessDeniedException("You are not admin of this event.");
        }

        $registration->setStatus(Registration::STATUS_REJECTED);
        $entityManager->persist($registration);
        $entityManager->flush();

        $api->sendMessage($registration->getTelegramChatId(),
            "Your registration for event " . $registration->getEvent()->getName() . " has been rejected."
        );

        return $this->redirectToRoute('app_events_show', ['id' => $registration->getEvent()->getId()]);
    }
}
