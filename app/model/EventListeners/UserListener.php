<?php

namespace App\Model\EventListeners;

use Nette,
    App,
    Tracy\Debugger;

class UserListener extends Nette\Object implements \Kdyby\Events\Subscriber
{
    private $event;

    function __construct(App\Model\Event $event)
    {
        $this->event = $event;
    }

    public function getSubscribedEvents()
    {
        return array(
            'App\Module\Base\Presenters\BaseGamePresenter::onGameStart' => 'onGameStart',
            'App\Module\Base\Presenters\BaseGamePresenter::onGameEnd' => 'onGameEnd',
            'App\Module\Base\Presenters\BaseGamePresenter::onGameForceEnd' => 'onGameForceEnd',
            'Nette\Application\Application::onError' => 'onError',
//            'App\Module\Base\Presenters\BasePresenter::onStartup' => 'onStartup',
            'Nette\Security\User::onLoggedIn' => 'onLoggedIn',
            'Nette\Security\User::onLoggedOut' => 'onLoggedOut'
        );
    }

    public function onGameStart($result)
    {
        $this->event->saveGameStart($result);
    }

    public function onGameEnd($result)
    {
        $this->event->saveGameEndResult($result, true);
    }

    public function onGameForceEnd($result)
    {
        $this->event->saveGameEndResult($result, false);
    }

    public function onStartup(\App\Module\Base\Presenters\BasePresenter $presenter)
    {
//        \Tracy\Debugger::barDump($presenter->getName() . ':' . $presenter->getView());
    }

    public function onError(Nette\Application\Application $sender, \Exception $e)
    {
//        Debugger::barDump('Error: ' . $e->getMessage());
    }
    public function onLoggedIn()
    {
        $this->event->saveUserLoggedIn();
    }
    public function onLoggedOut()
    {
        $this->event->saveUserLoggedOut();
    }

}