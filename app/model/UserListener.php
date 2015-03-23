<?php

namespace App\Model;

use Nette;

class UserListener extends Nette\Object implements \Kdyby\Events\Subscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'App\Module\Base\Presenters\BaseGamePresenter::onStart' => 'onGameStart',
            'App\Module\Base\Presenters\BaseGamePresenter::onEnd' => 'onEnd',
            'App\Module\Base\Presenters\BaseGamePresenter::onForceEnd' => 'onForceEnd',
            'Nette\Application\Application::onError' => 'onError',
            'App\Module\Base\Presenters\BasePresenter::onStartup' => 'onStartup',
            'Nette\Security\User::onLoggedIn' => 'onLoggedIn',
            'Nette\Security\User::onLoggedOut' => 'onLoggedOut'
        );
    }

    public function onGameStart(\App\Module\Base\Presenters\BaseGamePresenter $presenter)
    {
        \Tracy\Debugger::barDump('Game start: '. $presenter->getName());
    }

    public function onEnd(\App\Module\Base\Presenters\BaseGamePresenter $presenter)
    {
        \Tracy\Debugger::barDump('Game end: '. $presenter->getName());
    }

    public function onForceEnd(\App\Module\Base\Presenters\BaseGamePresenter $presenter)
    {
        \Tracy\Debugger::barDump('Game forced end: '. $presenter->getName());
    }

    public function onStartup(\App\Module\Base\Presenters\BasePresenter $presenter)
    {
//        \Tracy\Debugger::barDump($presenter->getName() . ':' . $presenter->getView());
    }

    public function onError(Nette\Application\Application $sender, \Exception $e)
    {
        \Tracy\Debugger::barDump('Error: ' . $e->getMessage());
    }
    public function onLoggedIn(Nette\Security\User $user)
    {
        \Tracy\Debugger::barDump('Auth: login: ' . $user->getIdentity()->getId());
    }
    public function onLoggedOut(Nette\Security\User $user)
    {
        \Tracy\Debugger::barDump('Auth: login: ' . $user->getIdentity()->getId());
    }

}