<?php

namespace App\Model\Security;

use Nette,
	App\Model,
	Nette\Utils\Strings,
	Nette\Security\Passwords;
use Tracy\Debugger;



class Authenticator extends Model\Base implements Nette\Security\IAuthenticator
{
	const
		DEFAULT_ROLE = 'guest';

	/** @var User */
	private $userModel;

	/** @var Group */
	private $groupModel;

	public function __construct(Model\User $userModel, Model\Group $groupModel)
	{
		$this->userModel = $userModel;
		$this->groupModel = $groupModel;
	}

	/**
	 * Performs an authentication.
	 * @param array $credentials
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$userRow = $this->userModel->getByColumn('username', $username);

		if (!$userRow) {
			throw new Nette\Security\AuthenticationException('front.auth.loginForm.usernameIncorrect', self::IDENTITY_NOT_FOUND);

		} elseif (!Passwords::verify($password, $userRow['password'])) {
			throw new Nette\Security\AuthenticationException('front.auth.loginForm.passwordIncorrect', self::INVALID_CREDENTIAL);

		} elseif (Passwords::needsRehash($userRow['password'])) {
			$userRow->update(array(
				'password' => Passwords::hash($password),
			));
		}

		$userArr = $userRow->toArray();

		$roles = $this->userModel->getUserRoles($userRow->id);

		if (!$roles)
			$roles = self::DEFAULT_ROLE;

		unset($userArr['role_id']);
		unset($userArr['password']);

		return new Nette\Security\Identity($userRow['id'], $roles, $userArr);
	}

}
