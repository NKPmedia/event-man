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

use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class HelloCommand extends AbstractCommand implements PublicCommandInterface
{
    public function getName(): string
    {
        return '/start';
    }

    public function getDescription(): string
    {
        return 'Start command';
    }

    public function execute(BotApi $api, Update $update): void
    {
        $text = "Hallo, {$update->getMessage()->getFrom()->getFirstName()} \n";
        $text .= "Dies ist ein Bot um sich bei Events anzumelden. \n";
        $text .= "Verwende /list um alle Events zu sehen. \n";
        $text .= "Verwende /register um dich für ein Event anzumelden. \n";
        $text .= "Du wirst benachrichtigt, wenn du für ein Event angenommen oder abgelehnt wurdest. \n";
        $api->sendMessage($update->getMessage()->getChat()->getId(), $text, );
    }
}
