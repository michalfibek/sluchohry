<?php


namespace App\Model;

use Nette;

class EventLogger extends Nette\Object {

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
        DATA_SOLVED = 'solved';

    /** @var Nette\Database\Context */
    private $database;
    private $user;
    private $httpRequest;

    public function __construct(Nette\Database\Context $database, Nette\Security\User $user, Nette\Http\Request $httpRequest)
    {
        $this->database = $database;
        $this->user = $user;
        $this->httpRequest = $httpRequest;
    }

    private function insertRecord($eventClassId, $eventData = null)
    {
        $event = $this->database->table('event')->insert(array(
            'user_id' => $this->user->getId(),
            'user_agent' => $this->httpRequest->getHeader('User-Agent'),
            'user_ip' => $this->httpRequest->getRemoteAddress(),
            'event_class_id' => $eventClassId
        ));

        if ($eventData)
            foreach ($eventData as $key => $value) {
                $this->database->table('event_data')->insert(array(
                    'event_id' => $event->getPrimary(),
                    'name' => $key,
                    'value' => $value
                ));
            }
    }

    public function saveGameStart(array $result)
    {
        if ($result['gameName'] == 'melodicCubes')
        {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_SONG_ID => $result['songId'],
                self::DATA_DIFFICULTY => $result['difficulty']
            );
        }
        $this->insertRecord(self::CLASS_GAME_START, $data);
    }

    public function saveGameEndResult(array $result, $solved)
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
        $this->insertRecord(self::CLASS_GAME_END, $data);
    }

    public function saveUserLoggedIn()
    {
        $data = array(
            self::DATA_LOGIN => true,
        );

        $this->insertRecord(self::CLASS_AUTH, $data);
    }

    public function saveUserLoggedOut()
    {
        $data = array(
            self::DATA_LOGOUT => true
        );

        $this->insertRecord(self::CLASS_AUTH, $data);
    }

}