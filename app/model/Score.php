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
     * @param $cubeCount
     * @return int
     */
    public function calcScoreMelodicCubes($time, $steps, $cubeCount)
    {
        $timePenalty = $this->getTimePenalty($time);
        $stepsPenalty = round(self::MAX_STEPS_PENALTY - (self::MAX_STEPS_PENALTY / $steps));

        return intval(round(self::MAX_SCORE - $timePenalty - $stepsPenalty));
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

        $minSteps = ($songCount*2)-1;
        $stepsPenalty = round(self::MAX_STEPS_PENALTY - (self::MAX_STEPS_PENALTY / ($steps/$minSteps)));

        return intval(round(self::MAX_SCORE - $timePenalty - $stepsPenalty));
    }

    public function calcScoreNoteSteps($time, $badSteps)
    {
        $timePenalty = $this->getTimePenalty($time);
        $stepsPenalty = $badSteps * 30;

        return intval(round(self::MAX_SCORE - $timePenalty - $stepsPenalty));
    }

    /**
     * @param int $time in milliseconds
     * @return int
     */
    private function getTimePenalty($time)
    {
        if ($time >= self::MAX_TIME_MSEC) return self::MAX_TIME_PENALTY;

        $timeConstant = self::MAX_TIME_MSEC / self::MAX_TIME_PENALTY;

        return round($time / $timeConstant); // todo change penalty to more non-linear function
    }

     /**
     * @param int $userId
     * @param int $gameId
     * @param array $result
     * @return array score result
     */
    public function processGameEndResult($userId, $gameId, array $result)
    {
        $score = 0; // default value mn('name', $result['gameName']);

        if ($gameId == 1) { // melodicCubes
            $score = $this->calcScoreMelodicCubes($result['time'], $result['steps'], $result['cubeCount']);

        } elseif ($gameId == 2) { // pexeso
            $songCount = count(explode(',', $result['songList']));
            $score = $this->calcScorePexeso($result['time'], $result['steps'], $songCount);

        } elseif ($gameId == 3) { // noteSteps
            $score = $this->calcScoreNoteSteps($result['time'], $result['steps']);
        }

        $updated = $this->updateScore($userId, $gameId, $result['difficulty'], $score);

        $currentGameRecord = $this->db->table('score')->where('game_id', (string)$gameId)->max('value');

        $gameRecord = (($currentGameRecord == $score) && $updated) ? true : false;

        return array(
            'score' => $score,
            'personalRecord' => (bool)$updated,
            'gameRecord' => $gameRecord
        );
    }

}