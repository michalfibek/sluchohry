<?php

namespace App\Model;

use Nette;

class Event extends Base {

    const
        CLASS_GAME_END = 1,
        CLASS_GAME_START = 2,
        CLASS_AUTH = 3,
        CLASS_ERROR = 4,
        CLASS_ADMIN = 5,
        CLASS_PROFILE = 6;

    const
        DATA_GAME_NAME = 'game_name',
        DATA_LOGIN = 'login',
        DATA_LOGOUT = 'logout',
        DATA_PLAY_TIME = 'play_time',
        DATA_PLAY_STEPS = 'play_steps',
        DATA_DIFFICULTY = 'difficulty',
        DATA_SONG_ID = 'song_id',
        DATA_SONG_LIST = 'song_list',
        DATA_SOLVED = 'solved';

    private $httpRequest;

    public function __construct(Nette\Database\Context $db, Nette\Http\Request $httpRequest)
    {
        parent::__construct($db);
        $this->httpRequest = $httpRequest;
    }

    public function getAllView()
    {
        return $this->db->table('view_eventlog');
    }

    /**
     * @param $userId int
     * @param $eventClassId
     * @param null $eventData
     */
    private function insertRecord($userId, $eventClassId, $eventData = null)
    {
        $event = $this->db->table('event')->insert(array(
            'user_id' => $userId,
            'user_agent' => $this->httpRequest->getHeader('User-Agent'),
            'user_ip' => $this->httpRequest->getRemoteAddress(),
            'event_class_id' => $eventClassId
        ));

        if ($eventData)
            foreach ($eventData as $key => $value) {
                $this->db->table('event_data')->insert(array(
                    'event_id' => $event->getPrimary(),
                    'name' => $key,
                    'value' => $value
                ));
            }
    }

    /**
     * @param Nette\Security\User $user
     * @param array $result
     */
    public function saveGameStart(Nette\Security\User $user, array $result)
    {
        if ($result['gameName'] == 'melodicCubes')
        {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_SONG_ID => $result['songId'],
                self::DATA_DIFFICULTY => $result['difficulty']
            );
        } elseif ($result['gameName'] == 'pexeso')
        {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_SONG_LIST => $result['songList'],
                self::DATA_DIFFICULTY => $result['difficulty']
            );
        }
        $this->insertRecord($user->getId(), self::CLASS_GAME_START, $data);
    }

    /**
     * @param Nette\Security\User $user
     * @param array $result
     * @param $solved
     */
    public function saveGameEndResult(Nette\Security\User $user, array $result, $solved)
    {
        if ($result['gameName'] == 'melodicCubes')
        {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_SONG_ID => $result['songId'],
                self::DATA_DIFFICULTY => $result['difficulty'],
                self::DATA_PLAY_TIME => $result['time'],
                self::DATA_PLAY_STEPS => $result['steps'],
                self::DATA_SOLVED => $solved,
            );
        }
        if ($result['gameName'] == 'pexeso')
        {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_SONG_LIST => $result['songList'],
                self::DATA_DIFFICULTY => $result['difficulty'],
                self::DATA_PLAY_TIME => $result['time'],
                self::DATA_PLAY_STEPS => $result['steps'],
                self::DATA_SOLVED => $solved,
            );
        }
        $this->insertRecord($user->getId(), self::CLASS_GAME_END, $data);
    }

    /**
     * @param Nette\Security\User $user
     */
    public function saveUserLoggedIn(Nette\Security\User $user)
    {
        $data = array(
            self::DATA_LOGIN => true,
        );

        $this->insertRecord($user->getId(), self::CLASS_AUTH, $data);
    }

    /**
     * @param Nette\Security\User $user
     */
    public function saveUserLoggedOut(Nette\Security\User $user)
    {
        $data = array(
            self::DATA_LOGOUT => true
        );

        $this->insertRecord($user->getId(), self::CLASS_AUTH, $data);
    }

}