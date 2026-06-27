<?php

namespace App\Model\EventListeners;

use Nette,
    App,
    App\Model,
    Tracy\Debugger;

class UserListener
{
    use Nette\SmartObject;

    private $event;

    /** @var User */
    private $userModel;

    /** @var Model\Score */
    private $score;

    function __construct(Model\Event $event, Model\User $userModel)
    {
        $this->event = $event;
        $this->userModel = $userModel;
    }

    public function onGameStart(Nette\Security\User $user, $result)
    {
        $this->event->saveGameStart($user, $result);
    }

    public function onGameEnd(Nette\Security\User $user, $result)
    {
        $this->event->saveGameEndResult($user, $result, true);
    }

    public function onGameForceEnd(Nette\Security\User $user, $result)
    {
        $this->event->saveGameEndResult($user, $result, false);
    }

    public function onStartup(\App\Module\Base\Presenters\BasePresenter $presenter)
    {
//        \Tracy\Debugger::barDump($presenter->getName() . ':' . $presenter->getView());
    }

    public function onError(Nette\Application\Application $sender, \Exception $e)
    {
//        Debugger::barDump('Error: ' . $e->getMessage());
    }
    public function onLoggedIn(Nette\Security\User $user)
    {
        $this->userModel->updateById($user->getId(), array('last_login_time' => new Nette\Utils\DateTime));
        $this->event->saveUserLoggedIn($user);
    }
    public function onLoggedOut(Nette\Security\User $user)
    {
        $this->event->saveUserLoggedOut($user);
    }

    public function onUserProfileSuccessAdd(Nette\Security\User $user, $values)
    {
        $this->event->saveUserProfileCreated($user, $values);
    }

    public function onUserProfileSuccessEdit(Nette\Security\User $user, $values)
    {
        $this->event->saveUserProfileEdited($user, $values);
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