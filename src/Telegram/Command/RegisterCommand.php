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

use App\Entity\Registration;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use BoShurik\TelegramBotBundle\Event\UpdateEvent;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use Doctrine\ORM\EntityManagerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Update;
use App\Entity\Event;

class RegisterCommand extends AbstractCommand implements PublicCommandInterface
{

    private $eventRepository;
    private $userRepository;
    private $entityManager;

    private const REGEX_INDEX = '#/register (\d+)#';

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
        return '/register';
    }

    public function getDescription(): string
    {
        return 'Example command';
    }

    protected function getTarget(): int
    {
        return self::TARGET_ALL;
    }

    public function execute(BotApi $api, Update $update): void
    {
        if ($update->getCallbackQuery()) {
            $callbackData = $update->getCallbackQuery()->getData();
            if (preg_match(self::REGEX_INDEX, $callbackData, $matches)) {
                $event = $this->eventRepository->find($matches[1]);

                if (!$event) {
                    $api->sendMessage($update->getCallbackQuery()->getMessage()->getChat()->getId(),
                        'Event not found');
                    return;
                }

                #Check if the user is already registered for the event
                $registration = $this->entityManager->getRepository(Registration::class)->findOneBy([
                    'event' => $event,
                    'telegram_id' => $update->getCallbackQuery()->getFrom()->getId()
                ]);
                if ($registration) {
                    $api->sendMessage($update->getCallbackQuery()->getMessage()->getChat()->getId(),
                        'You are already registered for event: ' . $event->getName());
                    return;
                }

                #get the maximum rank of all Registrations for the event
                $maxRank = $this->entityManager->getRepository(Registration::class)->getMaxRank($event->getId());

                $registration = new Registration();
                $registration->setEvent($event);
                $registration->setStatus(Registration::STATUS_PENDING);
                $registration->setTelegramId($update->getCallbackQuery()->getFrom()->getId());
                $registration->setRank($maxRank + 1);
                $registration->setTelegramUsername($update->getCallbackQuery()->getFrom()->getUsername());
                $registration->setTelegramFirstName($update->getCallbackQuery()->getFrom()->getFirstName());
                $registration->setTelegramLastName($update->getCallbackQuery()->getFrom()->getLastName());
                $registration->setTelegramChatId($update->getCallbackQuery()->getMessage()->getChat()->getId());


                $this->entityManager->persist($registration);
                $this->entityManager->flush();

                $api->sendMessage($update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'You have registered for event: ' . $event->getName() . PHP_EOL
                    . "Wait for the confirmation from the organizer.");

            }
        }
        else {
            #List all events as inline buttons
            $events = $this->eventRepository->findAll();
            $inlineKeyboard = [];
            foreach ($events as $event) {
                $inlineKeyboard[] = [
                    ['text' => $event->getName(), 'callback_data' => '/register ' . $event->getId()]
                ];
            }
            $api->sendMessage($update->getMessage()->getChat()->getId(),
                'Choose event:',
                null,
                false,
                null,
                new InlineKeyboardMarkup($inlineKeyboard));
        }
    }
}