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
    abstract protected function setAssetsByDifficulty();

    public function startup()
    {
        parent::startup();
        $this->gameSession = $this->getSession('game');
    }

    public function handleGameStart(array $result)
    {
        $this->onGameStart($result);
    }

    public function handleGameEnd(array $result)
    {
        $this->onGameEnd($result);
    }

    public function handleGameForceEnd(array $result)
    {
        $this->onGameForceEnd($result);
    }

}
