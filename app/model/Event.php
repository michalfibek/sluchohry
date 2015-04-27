<?php

namespace App\Model;

use Nette;
use Tracy\Debugger;

class Event extends Base {

    const
        CLASS_AUTH = 1,
        CLASS_ERROR = 2,
        CLASS_PROFILE = 3,
        CLASS_ADMIN = 4,
        CLASS_GAME_STARTED = 5,
        CLASS_GAME_SOLVED = 6,
        CLASS_GAME_CLOSED = 7;

    const
        DATA_GAME_NAME = 'game_name',
        DATA_SCORE = 'score',
        DATA_LOGIN = 'login',
        DATA_LOGOUT = 'logout',
        DATA_PROFILE_SAVED = 'profile_saved',
        DATA_PROFILE_CREATED = 'profile_created',
        DATA_PLAY_TIME = 'play_time',
        DATA_DIFFICULTY = 'difficulty',
        DATA_SOLVED = 'solved',
        DATA_CUBE_COUNT = 'cube_count', // melodicCubes
        DATA_PLAY_STEPS = 'play_steps', // melodicCubes, pexeso, faders
        DATA_SONG_ID = 'song_id', // melodicCubes
        DATA_SONG_LIST = 'song_list', // pexeso
        DATA_BAD_ATTEMPTS = 'bad_attempts', // noteSteps
        DATA_NOTE_COUNT = 'note_count', // noteSteps
        DATA_SHIFT_SIGNS = 'shift_signs', // noteSteps
        DATA_FIRST_LETTER = 'first_note', // noteSteps
        DATA_SLIDER_COUNT = 'slider_count', // faders
        DATA_NOTATION_ID = 'notation_id', // faders
        DATA_USER_PLAY_COUNT = 'user_melody_played', // faders
        DATA_ORIGINAL_PLAY_COUNT = 'original_melody_played', // faders
        DATA_EVAL_ATTEMPT = 'eval_attempt'; // melodicCubes, faders

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

    public function getAllEventClass() {
        return $this->db->table('event_class');
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
                if (is_array($value)) $value = implode(',', $value);
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
        if ($result['gameName'] == 'melodicCubes') {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_SONG_ID => $result['songId'],
                self::DATA_CUBE_COUNT => $result['cubeCount'],
                self::DATA_DIFFICULTY => $result['difficulty'],
            );
        } elseif ($result['gameName'] == 'pexeso') {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_SONG_LIST => $result['songList'],
                self::DATA_DIFFICULTY => $result['difficulty'],
            );
        } elseif ($result['gameName'] == 'noteSteps') {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_FIRST_LETTER => $result['firstLetter'],
                self::DATA_NOTE_COUNT => $result['noteCount'],
                self::DATA_SHIFT_SIGNS => $result['shiftSigns'],
                self::DATA_DIFFICULTY => $result['difficulty'],
            );
        } elseif ($result['gameName'] == 'faders') {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_NOTATION_ID => $result['notationId'],
                self::DATA_DIFFICULTY => $result['difficulty'],
                self::DATA_SLIDER_COUNT => $result['sliderCount'],
            );
        }
        $this->insertRecord($user->getId(), self::CLASS_GAME_STARTED, $data);
    }

    /**
     * @param Nette\Security\User $user
     * @param array $result
     * @param $solved
     */
    public function saveGameEndResult(Nette\Security\User $user, array $result, $solved)
    {
        if ($result['gameName'] == 'melodicCubes') {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_SCORE => $result['score'],
                self::DATA_SONG_ID => $result['songId'],
                self::DATA_CUBE_COUNT => $result['cubeCount'],
                self::DATA_DIFFICULTY => $result['difficulty'],
                self::DATA_PLAY_STEPS => $result['steps'],
                self::DATA_EVAL_ATTEMPT => $result['evalAttempt'],
                self::DATA_PLAY_TIME => $result['time'],
            );
        } else if ($result['gameName'] == 'pexeso') {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_SCORE => $result['score'],
                self::DATA_SONG_LIST => $result['songList'],
                self::DATA_DIFFICULTY => $result['difficulty'],
                self::DATA_PLAY_STEPS => $result['steps'],
                self::DATA_PLAY_TIME => $result['time'],
            );
        } else if ($result['gameName'] == 'noteSteps') {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_SCORE => $result['score'],
                self::DATA_FIRST_LETTER => $result['firstLetter'],
                self::DATA_NOTE_COUNT => $result['noteCount'],
                self::DATA_SHIFT_SIGNS => $result['shiftSigns'],
                self::DATA_DIFFICULTY => $result['difficulty'],
                self::DATA_BAD_ATTEMPTS => $result['steps'],
                self::DATA_PLAY_TIME => $result['time'],
            );
        } elseif ($result['gameName'] == 'faders') {
            $data = array(
                self::DATA_GAME_NAME => $result['gameName'],
                self::DATA_SCORE => $result['score'],
                self::DATA_NOTATION_ID => $result['notationId'],
                self::DATA_DIFFICULTY => $result['difficulty'],
                self::DATA_SLIDER_COUNT => $result['sliderCount'],
                self::DATA_PLAY_STEPS => $result['steps'],
                self::DATA_USER_PLAY_COUNT => $result['userPlayCount'],
                self::DATA_ORIGINAL_PLAY_COUNT => $result['originalPlayCount'],
                self::DATA_EVAL_ATTEMPT => $result['evalAttempt'],
                self::DATA_PLAY_TIME => $result['time'],
            );
        }

        $recordClass = ($solved) ? self::CLASS_GAME_SOLVED : self::CLASS_GAME_CLOSED;

        $this->insertRecord($user->getId(), $recordClass, $data);
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

    /**
     * @param Nette\Security\User $user
     * @param array $result
     */
    public function saveUserProfileEdited(Nette\Security\User $user, $result)
    {
        $result[self::DATA_PROFILE_SAVED] = true;

        $this->insertRecord($user->getId(), self::CLASS_PROFILE, $result);
    }

    /**
     * @param Nette\Security\User $user
     * @param array $result
     */
    public function saveUserProfileCreated(Nette\Security\User $user, $result)
    {
        $result[self::DATA_PROFILE_CREATED] = true;

        $this->insertRecord($user->getId(), self::CLASS_PROFILE, $result);
    }

}