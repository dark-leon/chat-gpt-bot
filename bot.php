<?php

require_once 'vendor/autoload.php';
require_once 'db.php';

$db = new DB();

const ADMIN_ID = 778912691; //editted  22222
const SECONDADMIN_ID = 2100460287; //editted22222
try {
    $bot = new \TelegramBot\Api\Client('6067817482:AAGoZ0axiOdCdPnA1epkXAW0qfeg3-SiJgw');
    $client = OpenAI::client('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');

    $bot->command('start', function ($message) use ($bot, $db) {
        $chat_id = $message->getChat()->getId();
        if (!$db->checkUser($chat_id)) {
            $db->insert($message);
        }
        $bot->sendMessage($chat_id, 'Hello, welcome to AI Bot @Rakhim_dev');
    });

    $bot->command('users', function ($message) use ($bot, $db) {
        $chat_id = $message->getChat()->getId();
        if ($chat_id == ADMIN_ID || $chat_id == SECONDADMIN_ID) {
            $users = $db->getAllUsers();
            $msg = '';
            foreach ($users as $user) {
                $msg .= <<<EOF
<b>Username: </b> <i>{$user['username']}</i>
<b>First Name: </b> <i>{$user['first_name']}</i>
<b>Last Name: </b> <i>{$user['last_name']}</i>
<b>Attempts: </b> <i>{$user['attempts']}</i>
-----------------------------------------------------------------\n
EOF;
            }
            $bot->sendMessage($message->getChat()->getId(), $msg, 'HTML');
        }
    });

    $bot->on(function (\TelegramBot\Api\Types\Update $update) use ($bot, $client, $db) {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();

        if (!$db->checkUser($id)) {
            $bot->sendMessage('Ishlatishdan avval /start buyrug\'ini bering!');
        } else {
            $db->incrementAttempts($id);
            if (strlen($message->getText()) <= 2000) {

                $bot->sendChatAction($id, 'typing');

                $result = $client->completions()->create([
                    'model' => 'text-davinci-003', //model nomi
                    'prompt' => $message->getText(),
                    'max_tokens' => 4000 //maximum javob uzunligi
                ]);

                $bot->sendChatAction($id, 'typing');

                $msg = '';
                foreach ($result['choices'] as $choice) {
                    $msg .= $choice['text'];
                }

                $bot->sendMessage($id, $msg);

            } else {
                $bot->sendMessage($id, 'Iltimos qisqaroq so\'rovni amalga oshiring');
            }
        }

    }, function () {
        return true;
    });

    $bot->run();

} catch (\TelegramBot\Api\Exception $e) {
    $e->getMessage();
}