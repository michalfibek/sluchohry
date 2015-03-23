<?php

namespace App\Model;

use Nette;

class UserListener extends Nette\Object implements \Kdyby\Events\Subscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'App\Module\Front\Presenters\GamePresenter::onStart' => 'onGameStart',
            'Nette\Application\Application::onPresenter' => 'onPresenter',
            'Nette\Application\Application::onError' => 'onError',
            'Nette\Security\User::onLoggedIn' => 'onLoggedIn',
            'Nette\Security\User::onLoggedOut' => 'onLoggedOut'
        );
    }

    public function onGameStart()
    {
        \Tracy\Debugger::barDump('game');
    }

    public function onPresenter(Nette\Application\Application $app)
    {
        \Tracy\Debugger::barDump($app->getPresenter());
    }

    public function onError(Nette\Application\Application $sender, \Exception $e)
    {
        \Tracy\Debugger::barDump('Error: ' . $e->getMessage());
    }
    public function onLoggedIn(Nette\Security\User $user)
    {
        \Tracy\Debugger::barDump('user id: ' . $user->getIdentity()->getId());
    }
    public function onLoggedOut(Nette\Security\User $user)
    {
        \Tracy\Debugger::barDump('user id: ' . $user->getIdentity()->getId());
    }

}