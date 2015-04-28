<?php

namespace App\Module\Base\Presenters;


use Nette,
    App\Model,
    App\Services,
    Tracy\Debugger;

abstract class BaseGamePresenter extends BasePresenter
{
    const
        GAME_MELODIC_CUBES = 1,
        GAME_PEXESO = 2,
        GAME_NOTE_STEPS = 3,
        GAME_FADERS = 4,
        HARDER_CHALLENGE_EVERY = 7;

    /** @inject @var Model\Game */
    public $game;

    /** @inject @var Model\Score */
    public $score;

    /** @var \Nette\Http\SessionSection */
    protected $gameHistory;

    protected $gameId;
    protected $difficulty;
    protected $gameAssets;
    public $onGameStart = array();
    public $onGameEnd = array();
    public $onGameForceEnd = array();

    abstract protected function getAssetsById($id);
    abstract protected function getAssetsRandom();

    public function startup()
    {
        parent::startup();

        if ($this->isSignalReceiver($this, 'gameStart') || $this->isSignalReceiver($this, 'gameEnd') || $this->isSignalReceiver($this, 'gameForceEnd')) {
            $this->processSignal();
        }

        $this->gameHistory = $this->getSession(__CLASS__); // get session by specific game name
    }

    protected function historyAdd($recordId = NULL, $recordKey = NULL)
    {
        if (!$recordId) $recordId = date("dHis");
        if (!$recordKey) $recordKey = $recordId;
        $this->gameHistory->offsetSet($recordKey,$recordId);
    }

    protected function historyGetAll()
    {
        if (empty($this->gameHistory->getIterator()->getArrayCopy()))
            return null;

        return $this->gameHistory->getIterator()->getArrayCopy();
    }

    protected function historyGetCount()
    {
        if (empty($this->gameHistory->getIterator()->getArrayCopy()))
            return 0;
        return count($this->gameHistory->getIterator()->getArrayCopy());
    }

    protected function historyClear()
    {
        $this->gameHistory->remove();
    }

    protected function getVariationByDifficulty($difficulty)
    {
        return $this->game->getDifficultyVariation($this->gameId, $difficulty);
    }

    public function handleGameStart(array $result)
    {
        $this->onGameStart($result);

        $this->presenter->terminate();
    }

    public function handleGameEnd(array $result)
    {
        $gameId = $this->game->getByName($result['gameName'])->id;

        $score = $this->score->processGameEndResult($this->user->getId(), $gameId, $result); // return score to game

        $result['score'] = $score['score'];
        $this->onGameEnd($result);

        $this->sendResponse(new Nette\Application\Responses\JsonResponse($score));
    }

    public function handleGameForceEnd(array $result)
    {
        $result['score'] = 0;

        $this->onGameForceEnd($result);

        $this->presenter->terminate();
    }

    protected function beforeRender()
    {
        parent::beforeRender();

        if ($this->difficulty == 1)
        {
            $this->template->difficultyName = 'easy';
            $this->template->difficultySymbol = 'fa fa-bicycle';
        }
        if ($this->difficulty == 2) {
            $this->template->difficultyName = 'medium';
            $this->template->difficultySymbol = 'fa fa-car';
        }
        if ($this->difficulty == 3) {
            $this->template->difficultyName = 'hard';
            $this->template->difficultySymbol = 'fa fa-rocket';
        }
        $this->template->difficultySymbol = ''; // override - no symbol

        $historyCount = (int)$this->historyGetCount();

        if ($historyCount % self::HARDER_CHALLENGE_EVERY == 0) {
            if ($this->difficulty == 1) {
                $this->template->harderLevel = 2;
                $this->template->harderName = 'medium';
                $this->template->harderSymbol = 'fa fa-car';
            } elseif ($this->difficulty == 2) {
                $this->template->harderLevel = 3;
                $this->template->harderName = 'hard';
                $this->template->harderSymbol = 'fa fa-rocket';
            }
        }
    }

}
