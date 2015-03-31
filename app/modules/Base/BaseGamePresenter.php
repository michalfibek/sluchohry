<?php

namespace App\Module\Base\Presenters;


use Tracy\Debugger;

abstract class BaseGamePresenter extends BasePresenter
{
    const
        GAME_MELODIC_CUBES = 1,
        GAME_PEXESO = 2;

    /** @var \Nette\Http\SessionSection */
    protected $gameSession;

    protected $difficulty;
    protected $gameAssets;
    public $onGameStart = array();
    public $onGameEnd = array();
    public $onGameForceEnd = array();

    abstract protected function getAssetsById($id);
    abstract protected function getAssetsRandom();

    public function startup() {

        parent::startup();
        $this->gameSession = $this->getSession('game');
    }

    public function handleGameEnd(array $result)
    {
        $this->onGameEnd($result);
    }

    public function handleGameForceEnd(array $result)
    {
        $this->onGameForceEnd($result);
    }

    public function renderDefault()
    {
//        $this->onGameStart($this);
    }

    public function actionEvaluate()
    {
        $this->onEnd($this);
    }

}
