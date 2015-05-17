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
        DIFFICULTY_COUNT = 3,
        MAX_SCORE = 1000,
        MAX_STEPS_PENALTY = 500,
        MAX_TIME_PENALTY = 500,
        PEXESO_PAIR_THRESHOLD = 12,
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
     * @param int $userId
     * @param int $gameId
     * @param int $difficultyId
     * @return array|bool|mixed|Nette\Database\Table\IRow
     */
    public function getByUser($userId, $gameId, $difficultyId)
    {
        return $this->db->table($this->tableName)->where('user_id', $userId)->where('game_id', $gameId)->where('difficulty_id', $difficultyId)->fetch()->value;
    }

    /**
     * @param int $userId
     * @param int $gameId
     * @param bool $initEmpty
     * @return array|bool|mixed|Nette\Database\Table\IRow
     */
    public function getAllByUser($userId, $gameId, $initEmpty = false)
    {
        $score = $this->db->table($this->tableName)->where('user_id', $userId)->where('game_id',$gameId)->fetchPairs('difficulty_id', 'value');

        if ($initEmpty) {
            for ($i = 1; $i <= self::DIFFICULTY_COUNT; $i++)
                $return[$i] = isset($score[$i]) ? $score[$i] : 0;
            return $return;
        } else
            return $score;

        return $return;
    }

    public function getListByGame($gameId, $difficultyId, $groupLimit = NULL)
    {
        $userList = $this->db->table('user')->order('username ASC');
        if ($groupLimit) {
            $usersForGroupLimit = $this->db->table('user_has_group')->where('group_id', $groupLimit)->fetchPairs(NULL, 'user_id');
            $userList = $userList->where('id', $usersForGroupLimit);
        }

        foreach ($userList as $key => $user) {
            $score = $user->related('score')->where('game_id', $gameId)->where('difficulty_id', $difficultyId)->fetch();
            $result[$key]['user_id'] = $user->id;
            $result[$key]['realname'] = $user->realname;
            $result[$key]['score'] = isset($score->value) ? $score->value : 0;
        }
        return $result;
    }

    public function getScoreSum($userId)
    {
        $games = $this->db->table('game')->fetchAll();

        $difficulties = $this->db->table('difficulty')->fetchAll();

        foreach ($difficulties as $d) {
            $scoreSum[$d->id] = 0; // init array with zero scores per difficulty
        }

        foreach ($games as $g) {
            $scoreForGame = $this->db->table($this->tableName)->where('user_id', $userId)->where('game_id',$g->id)->fetchPairs('difficulty_id', 'value');

            foreach ($scoreForGame as $difficulty => $scoreValue) {
                $scoreSum[$difficulty] = $scoreSum[$difficulty] + $scoreValue;
            }
        };

        return $scoreSum;

    }

    public function getPlayCountPerUser($userId)
    {
        return $this->db->table('score')->where('user_id', $userId)->select('game_id, sum(play_count) AS play_count')->group('game_id');
    }

    public function getScoreView()
    {
        return $this->db->table('view_scores');
    }

    public function getTopThree($gameId)
    {
        $i = 1;
        foreach ($this->db->table($this->tableName)->where('game_id', $gameId)->group('user_id')->select('*, SUM(value) AS value')->order('value DESC')->limit('3') as $s) {
            $topPlayers[$i]['user_id'] = $s->ref('user')->id;
            $topPlayers[$i]['realname'] = $s->ref('user')->realname;
            $topPlayers[$i]['avatar_filename'] = $s->ref('user')->ref('avatar')->filename;
            $topPlayers[$i]['score'] = $s->value;
            $topPlayers[$i]['difficulty_id'] = $s->difficulty;
            $topPlayers[$i]['score_time'] = $s->update_time;
            $i++;
        }

        return $topPlayers;
    }

    /**
     * @param $userId
     * @param $gameId
     * @param $difficultyId
     * @param $value
     * @return bool|int|Nette\Database\Table\IRow
     */
    private function updateScore($userId, $gameId, $difficultyId, $value, $time)
    {
        $scoreData = array(
            'user_id' => $userId,
            'game_id' => $gameId,
            'difficulty_id' => $difficultyId,
            'value' => $value
        );
        $row = $this->db->table($this->tableName)
            ->where('user_id', $userId)
            ->where('game_id', $gameId)
            ->where('difficulty_id', $difficultyId);

//        Debugger::log('fetch:'.$row->fetch());

        if ($data = $row->fetch())
        {
            $row->update(array('play_count' => $data->play_count + 1));

            if ($data->best_time > $time)
                $row->update(array('best_time' => $time)); // best time updated
                // TODO update in additional function and return this status

            if ($data->value > $value)
                return false; // no insert - old score value is bigger

            return $row->update(array(
                'value' => $value
            ));
        } else {
            $scoreData['play_count'] = 1;
            $scoreData['best_time'] = $time;
            return $this->db->table($this->tableName)->insert($scoreData);
        }
    }

    /**
     * @param $time
     * @param $steps
     * @param $cubeCount
     * @return int
     */
    private function calcScoreMelodicCubes($time, $steps, $cubeCount)
    {
        $timePenalty = $this->getTimePenalty($time);
        $stepsPenalty = $steps * 30;
        if ($stepsPenalty > self::MAX_STEPS_PENALTY) $stepsPenalty = self::MAX_STEPS_PENALTY;
//        echo 'timepen:' . $timePenalty . ';';
//        echo 'stepspen:' . $stepsPenalty . ';';

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
        $maxTime = ($songCount * 60 * 1000) * 0.8;
        $timePenalty = $this->getTimePenalty($time, $maxTime);

        if ($steps < $songCount) $steps = $songCount;

        $stepsPenalty = ($steps - $songCount) * 12;

        if ($stepsPenalty > self::MAX_STEPS_PENALTY) $stepsPenalty = self::MAX_STEPS_PENALTY;
//        echo 'timepen:' . $timePenalty . ';';
//        echo 'stepspen:' . $stepsPenalty . ';';

        return intval(round(self::MAX_SCORE - $timePenalty - $stepsPenalty));
    }

    /**
     * @param $time
     * @param $badSteps
     * @return int
     */
    private function calcScoreNoteSteps($time, $badSteps)
    {
        $timePenalty = $this->getTimePenalty($time);
        $stepsPenalty = $badSteps * 30;

        return intval(round(self::MAX_SCORE - $timePenalty - $stepsPenalty));
    }

    /**
     * @param $time
     * @param $steps
     * @param $sliderCount
     * @return int
     */
    private function calcScoreFaders($time, $steps, $sliderCount)
    {
        $maxTime = ($sliderCount * 60 * 1000) * 0.8; // maximum time to get timePenalty < self::MAX_TIME_PENALTY is calculated by slider count
        $timePenalty = $this->getTimePenalty($time, $maxTime);

        $sliderCountAffect = ($sliderCount >= 15) ? 1 : (15 - $sliderCount);
        $stepsPenalty = $steps * $sliderCountAffect;

        $score = intval(round(self::MAX_SCORE - $timePenalty - $stepsPenalty));

        return $score;
    }

    /**
     * @param int $time in milliseconds
     * @param int $maxTime
     * @return int
     */
    private function getTimePenalty($time, $maxTime = self::MAX_TIME_MSEC)
    {
        if ($time >= $maxTime) return self::MAX_TIME_PENALTY;

        $timeConstant = $maxTime / self::MAX_TIME_PENALTY;

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

        } elseif ($gameId == 4) { // noteSteps
            $score = $this->calcScoreFaders($result['time'], $result['steps'], $result['sliderCount']);
        }

        // possible range problem fix
        if ($score < 0) {
            $score = 0;
        } else if ($score > self::MAX_SCORE) {
            $score = self::MAX_SCORE;
        }

        $updated = $this->updateScore($userId, $gameId, $result['difficulty'], $score, $result['time']);

        $currentGameRecord = $this->db->table($this->tableName)->where('game_id', (string)$gameId)->max('value');

        $gameRecord = (($currentGameRecord == $score) && $updated) ? true : false;

        return array(
            'score' => $score,
            'personalRecord' => (bool)$updated,
            'gameRecord' => $gameRecord
        );
    }

}