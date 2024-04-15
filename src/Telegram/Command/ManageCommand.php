<?php

/*
 * This file is part of the boshurik-bot-example.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Telegram\Command;

use App\Repository\EventRepository;
use App\Repository\UserRepository;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use Doctrine\ORM\EntityManagerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use App\Entity\Event;

class ManageCommand extends AbstractCommand implements PublicCommandInterface
{

    private $eventRepository;
    private $userRepository;
    private $entityManager;

    public function __construct(EventRepository $eventRepository,
                                UserRepository $userRepository,
                                EntityManagerInterface $entityManager)
    {
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function getName(): string
    {
        return '/manage';
    }

    public function getDescription(): string
    {
        return 'Example command';
    }

    public function execute(BotApi $api, Update $update): void
    {
        #Check if this chat is a group
        if ($update->getMessage()->getChat()->getType() != 'group') {
            $api->sendMessage($update->getMessage()->getChat()->getId(), 'This command only works in groups');
            return;
        }

        #Get all admins of the chat adn check if the bot is admin
        $admins = $api->getChatAdministrators($update->getMessage()->getChat()->getId());
        $admins = array_map(function ($member) {
            return $member->getUser()->getId();
        }, $admins);
        $botId = $api->getMe()->getId();
        if (!in_array($botId, $admins)) {
            $api->sendMessage($update->getMessage()->getChat()->getId(), 'I am not admin');
            return;
        }
        #Check if the user that send the command is admin
        $userId = $update->getMessage()->getFrom()->getId();
        if (!in_array($userId, $admins)) {
            $api->sendMessage($update->getMessage()->getChat()->getId(), 'You are not admin');
            return;
        }

        #Check if a event with the same name exists in the database
        $group_name = $update->getMessage()->getChat()->getTitle();
        $event = $this->eventRepository->findBy(['name' => $group_name]);
        if ($event) {
            $api->sendMessage($update->getMessage()->getChat()->getId(), 'Event already exists');
            return;
        }

        $admin_user = $this->userRepository->findOneBy(['telegram_id' => $userId]);
        if (!$admin_user) {
            $api->sendMessage($update->getMessage()->getChat()->getId(), 'You are not registered in the database');
            return;
        }
        #Check if the user is admin
        if (!in_array("ROLE_ADMIN", $admin_user->getRoles())){
            $api->sendMessage($update->getMessage()->getChat()->getId(), 'You are not admin');
            return;
        }

        #Create a new event
        $event = new Event();
        $event->setName($group_name);
        $event->setTelegramGroup($update->getMessage()->getChat()->getId());
        $event->setAdmin($admin_user);
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $api->sendMessage($update->getMessage()->getChat()->getId(), 'Event created');
    }
}
