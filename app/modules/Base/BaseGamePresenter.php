<?php

namespace App\Module\Base\Presenters;


abstract class BaseGamePresenter extends BasePresenter
{
    /** @var \Nette\Http\SessionSection */
    protected $gameSession;

    protected $difficulty;
    protected $gameAssets;
    public $onStart = array();
    public $onEnd = array();
    public $onForceEnd = array();

    abstract protected function getAssetsById($id);
    abstract protected function getAssetsRandom();

    public function startup() {

        parent::startup();
        $this->gameSession = $this->getSession('game');
    }

    protected function handleForceEnd()
    {
        $this->onForceEnd($this);
    }

    public function renderDefault()
    {
        $this->onStart($this);
    }

    public function actionEvaluate()
    {
        $this->onEnd($this);
    }

}
