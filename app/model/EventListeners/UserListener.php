<?php

namespace App\Model\EventListeners;

use Nette,
    App,
    Tracy\Debugger;

class UserListener extends Nette\Object implements \Kdyby\Events\Subscriber
{
    private $event;

    /** @var User */
    private $userModel;

    /** @var Nette\Security\User */
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
            'App\Components\UserProfile::onSuccessAdd' => 'onUserProfileSuccessAdd',
            'App\Components\UserProfile::onSuccessEdit' => array('onUserProfileSuccessEdit', 30),
            'App\Components\UserProfile::onEditFail' => 'onUserProfileEditFail',
            'App\Components\UserProfile::onNoChange' => 'onUserProfileNoChange',
            'App\Components\UserProfile::onDuplicateEmail' => 'onUserProfileDuplicateEmail',
            'App\Components\UserProfile::onDuplicateUsername' => 'onUserProfileDuplicateUsername',
            'App\Components\UserProfile::onAccessDenied' => 'onUserProfileAccessDenied',
            'App\Components\UserProfile::onNotFound' => 'onUserProfileNotFound',
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

    public function onUserProfileSuccessAdd($values)
    {
        $this->event->saveUserProfileCreated($this->user, $values);
    }

    public function onUserProfileSuccessEdit($values)
    {
        $this->event->saveUserProfileEdited($this->user, $values);
    }

    public function onUserProfileEditFail($values)
    {

    }

    public function onUserProfileNoChange($values)
    {

    }

    public function onUserProfileDuplicateEmail($values)
    {

    }

    public function onUserProfileDuplicateUsername($values)
    {

    }

    public function onUserProfileAccessDenied($values)
    {

    }

    public function onUserProfileNotFound()
    {

    }

}