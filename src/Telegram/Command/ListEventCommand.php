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

class ListEventCommand extends AbstractCommand implements PublicCommandInterface
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
        return '/list';
    }

    public function getDescription(): string
    {
        return 'Example command';
    }

    public function execute(BotApi $api, Update $update): void
    {
        $events = $this->eventRepository->findAll();
        $message = 'Events:'.PHP_EOL;
        foreach ($events as $event) {
            $message .= $event->getName().PHP_EOL;
        }
        $api->sendMessage($update->getMessage()->getChat()->getId(), $message);
    }
}