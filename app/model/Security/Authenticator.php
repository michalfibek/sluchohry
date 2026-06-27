<?php

namespace App\Model\Security;

use Nette,
	App\Model,
	Nette\Utils\Strings,
	Nette\Security\Passwords;
use Tracy\Debugger;



class Authenticator extends Model\Base implements Nette\Security\Authenticator
{
	const
		DEFAULT_ROLE = 'guest';

	/** @var User */
	private $userModel;

	/** @var Group */
	private $groupModel;

	/** @var Passwords */
	private $passwords;

	public function __construct(Model\User $userModel, Model\Group $groupModel, Passwords $passwords)
	{
		$this->userModel = $userModel;
		$this->groupModel = $groupModel;
		$this->passwords = $passwords;
	}

	/**
	 * Performs an authentication.
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(string $user, string $password): Nette\Security\IIdentity
	{
		$username = $user;
		$userRow = $this->userModel->getByColumn('username', $username);

		if (!$userRow) {
			
			if (in_array($username, array('dpadmin', 'dpzak'))) { // specialni info kvuli testovani diplomky, casem odstranit
				throw new Nette\Security\AuthenticationException('front.auth.loginForm.usernameIncorrectDPtesting', self::IDENTITY_NOT_FOUND);
			}

			throw new Nette\Security\AuthenticationException('front.auth.loginForm.usernameIncorrect', self::IDENTITY_NOT_FOUND);

		} elseif (!$this->passwords->verify($password, $userRow['password'])) {
			throw new Nette\Security\AuthenticationException('front.auth.loginForm.passwordIncorrect', self::INVALID_CREDENTIAL);

		} elseif ($this->passwords->needsRehash($userRow['password'])) {
			$userRow->update(array(
				'password' => $this->passwords->hash($password),
			));
		}

		$userArr = $userRow->toArray();

		$roles = $this->userModel->getUserRoles($userRow->id);

		if (!$roles)
			$roles = self::DEFAULT_ROLE;

		unset($userArr['role_id']);
		unset($userArr['password']);

		return new Nette\Security\SimpleIdentity($userRow['id'], $roles, $userArr);
	}

}
