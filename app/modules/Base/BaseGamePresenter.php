<?php

namespace App\Module\Base\Presenters;


abstract class BaseGamePresenter extends BasePresenter {

    protected $difficulty;
    protected $gameAssets;

    abstract protected function getAssets();
    abstract protected function onStart();
    abstract protected function onEnd();
    abstract protected function onForceEnd();

}
