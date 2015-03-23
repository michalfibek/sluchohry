<?php

namespace App\Module\Base\Presenters;


abstract class BaseGamePresenter extends BasePresenter {

    protected $difficulty;
    protected $gameAssets;
    public $onStart = array();
    public $onEnd = array();
    public $onForceEnd = array();

    abstract protected function getAssetsById($id);
    abstract protected function getAssetsRandom();

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
