<?php
namespace App\Model;

use Nette,
    App\Services;
use Tracy\Debugger;

/**
 * Game table
 */
class Score extends Base
{
    const
        MAX_SCORE = 1000,
        MAX_STEPS_PENALTY = 500,
        MAX_TIME_PENALTY = 500,
        MAX_TIME_MSEC = 300000; // 5*60*1000

    /** @var Game */
    private $game;

    /**
     * @param Nette\Database\Context $db
     * @param Game $game
     */
    public function __construct(Nette\Database\Context $db, Game $game)
    {
        parent::__construct($db);
        $this->game = $game;
    }

    /**
     * @param $userId
     * @param $gameId
     * @param $difficultyId
     * @param $value
     * @return bool|int|Nette\Database\Table\IRow
     */
    private function updateScore($userId, $gameId, $difficultyId, $value)
    {
        $scoreData = array(
            'user_id' => $userId,
            'game_id' => $gameId,
            'difficulty_id' => $difficultyId,
            'value' => $value
        );
        $row = $this->db->table('score')
            ->where('user_id', $userId)
            ->where('game_id', $gameId)
            ->where('difficulty_id', $difficultyId);

//        Debugger::log('fetch:'.$row->fetch());

        if ($data = $row->fetch())
        {
            if ($data->value > $value)
                return false; // no insert - old score value is bigger

            return $row->update(array(
                'value' => $value
            ));
        } else {
            return $this->db->table('score')->insert($scoreData);
        }
    }

    /**
     * @param $time
     * @param $steps
     * @param $songCount
     * @return int
     */
    private function calcScorePexeso($time, $steps, $songCount)
    {
        $timePenalty = $this->getTimePenalty($time);

        $minSteps = $songCount*2;
        $stepsPenalty = self::MAX_STEPS_PENALTY - (self::MAX_STEPS_PENALTY / ($steps/$minSteps));

        return intval(round(self::MAX_SCORE - $timePenalty - $stepsPenalty));
    }

    /**
     * @param int $time in milliseconds
     * @return int
     */
    private function getTimePenalty($time)
    {
        $timeConstant = self::MAX_TIME_MSEC/self::MAX_TIME_PENALTY;

        if ($time >= self::MAX_TIME_MSEC) return self::MAX_TIME_PENALTY;

        return $time/$timeConstant;
    }

    /**
     * @param Nette\Security\User $user
     * @param array $result
     * @return array score result
     */
    public function processGameEndResult(Nette\Security\User $user, array $result)
    {
        $score = 0; // default value - if score evaluation goes wrong

        if ($result['gameName'] == 'melodicCubes') {

        } elseif ($result['gameName'] == 'pexeso') {
            $songCount = count(explode(',', $result['songList']));
            $score = $this->calcScorePexeso($result['time'], $result['steps'], $songCount);

        } elseif ($result['gameName'] == 'noteSteps') {

        }

        $updated = $this->updateScore(
            $user->getId(),
            $this->game->getByColumn('name', $result['gameName']),
            $result['difficulty'],
            $score);

//        $gameRecord = $this->

        return array(
            'score' => $score,
            'personalRecord' => $updated,
            'gameRecord' => false
        );
    }

}