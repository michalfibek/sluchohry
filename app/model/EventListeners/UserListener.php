<?php

namespace App\Model\EventListeners;

use Nette,
    App,
    Tracy\Debugger;

class UserListener extends Nette\Object implements \Kdyby\Events\Subscriber
{
    private $event;
    /**
     * @var User
     */
    private $userModel;
    /**
     * @var Nette\Security\User
     */
    private $user;

    function __construct(Nette\Security\User $user, App\Model\Event $event, App\Model\User $userModel)
    {
        $this->event = $event;
        $this->userModel = $userModel;
        $this->user = $user;
    }

    public function getSubscribedEvents()
    {
        return array(
            'App\Module\Base\Presenters\BaseGamePresenter::onGameStart',
            'App\Module\Base\Presenters\BaseGamePresenter::onGameEnd',
            'App\Module\Base\Presenters\BaseGamePresenter::onGameForceEnd',
//            'Nette\Application\Application::onError' => 'onError',
//            'App\Module\Base\Presenters\BasePresenter::onStartup' => 'onStartup',
            'Nette\Security\User::onLoggedIn',
            'Nette\Security\User::onLoggedOut'
        );
    }

    public function onGameStart($result)
    {
        $this->event->saveGameStart($this->user, $result);
    }

    public function onGameEnd($result)
    {
        $this->event->saveGameEndResult($this->user, $result, true);
    }

    public function onGameForceEnd($result)
    {
        $this->event->saveGameEndResult($this->user, $result, false);
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
        $this->userModel->updateById($this->user->getId(), array('last_login_time' => new Nette\Utils\DateTime));
        $this->event->saveUserLoggedIn($this->user);
    }
    public function onLoggedOut()
    {
        $this->event->saveUserLoggedOut($this->user);
    }

}