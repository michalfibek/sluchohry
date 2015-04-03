<?php
namespace App\Model\Security;

use Nette,
	Nette\Security\Permission;
use Tracy\Debugger;

class Acl extends Permission
{

	public function __construct()
	{
		$this->addRole('guest');
		$this->addRole('student', 'guest');
		$this->addRole('teacher', 'student');
		$this->addRole('editor', 'teacher');
		$this->addRole('admin', 'editor');

		$this->addResource('Front:Default');
		$this->addResource('Front:Game:MelodicCubes');
		$this->addResource('Front:Game:Pexeso');

		$this->addResource('Admin:Default');
		$this->addResource('Admin:Song');
		$this->addResource('Admin:User');
		$this->addResource('Admin:Group');

		/**
		 * Student - access only to games.
		 */
		$this->allow('student', array(
			'Front:Default',
			'Front:Game:MelodicCubes',
			'Front:Game:Pexeso'
		));

		/**
		 * Teacher - access only to basic listing of users, etc. Defined by privilege (action)
		 * fine grained permission control.
		 */
		$this->allow('teacher', array(
			'Admin:Default',
			'Admin:User',
			'Admin:Group'
		), array(
			'default'
		));

		/**
		 * Editor - access to everything defined here, no exceptions in privileges/actions.
		 */
		$this->allow('editor', array(
			'Admin:Default',
			'Admin:Song',
			'Admin:User',
			'Admin:Group'
		));

		$this->allow('admin', Permission::ALL, Permission::ALL);

	}

	/**
	 * Returns array of current role's ancestors
	 *
	 * @param $role string - role identifier
	 * @return array
	 */
	private	 function getRoleAncestors($role)
	{
		$ancestors = array();
		foreach($this->getRoleParents($role) as $parent)
			$ancestors += array($parent => TRUE) +
				array_flip($this->getRoleAncestors($parent));

		return array_keys($ancestors);
	}

	/**
	 * Returns true if role is equal to parent or just one of his children.
	 *
	 * @param $child string
	 * @param $parent string
	 * @return bool
	 */
	public function isChildRole($child, $parent)
	{
		if ($child == $parent)
			return true;
		else
			return in_array($child, $this->getRoleAncestors($parent));
	}
}