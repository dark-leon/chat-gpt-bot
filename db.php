<?php

require_once 'vendor/autoload.php';

use Jajo\JSONDB;

class DB
{
    private $db;

    public function __construct()
    {
        $this->db = new JSONDB(__DIR__ . '/database');
    }

    function insert($message)
    {
        $this->db->insert('users.json',
            [
                'chat_id' => $message->getChat()->getId(),
                'first_name' => $message->getChat()->getFirstName(),
                'last_name' => $message->getChat()->getLastName(),
                'username' => $message->getChat()->getUsername(),
                'attempts' => 1
            ]
        );
    }

    public function incrementAttempts($chat_id)
    {
        $users = $this->db->select()
            ->from('users.json')
            ->where(['chat_id' => $chat_id])
            ->get();
        if (count($users) != 0) {
            $this->db->update(['attempts' => $users[0]['attempts'] + 1])
                ->from('users.json')
                ->where(['chat_id' => $chat_id])
                ->trigger();
            return true;
        } else {
            return false;
        }
    }

    public function checkUser($chat_id)
    {
        $users = $this->db->select()
            ->from('users.json')
            ->where(['chat_id' => $chat_id])
            ->get();
        if (count($users) != 0) {
            return $users[0];
        } else {
            return false;
        }
    }

    public function getAllUsers()
    {
        return $this->db->select()
            ->from('users.json')
            ->order_by( 'attempts', JSONDB::DESC )
            ->get();
    }

}
