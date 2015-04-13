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
		$this->addResource('Front:Profile');
		$this->addResource('Front:Game:MelodicCubes');
		$this->addResource('Front:Game:Pexeso');
		$this->addResource('Front:Game:NoteSteps');

		$this->addResource('Admin:Default');
		$this->addResource('Admin:Song');
		$this->addResource('Admin:User');
		$this->addResource('Admin:Group');
		$this->addResource('Admin:Event');


		/**
		 * Guest - access only to games.
		 */
		$this->allow('guest', array(
			'Front:Default',
			'Front:Profile',
			'Front:Game:MelodicCubes',
			'Front:Game:Pexeso',
			'Front:Game:NoteSteps'
		));


		/**
		 * Student - inherits everything from guest
		 */
//		$this->allow('student', array(
//			'Front:Default',
//			'Front:Profile',
//			'Front:Game:MelodicCubes',
//			'Front:Game:Pexeso',
//			'Front:Game:NoteSteps'
//		));

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
		 * Editor - access to everything defined here, no special exceptions in privileges/actions.
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
	 * @param string $role Role identifier
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
	 * @param string|array $children
	 * @param string|array $parents
	 * @param bool $acceptEmptyChildren Accept empty children role as correct and return true
	 * @return bool
	 */
	public function isChildRole($children, $parents, $acceptEmptyChildren = false)
	{
		if ($children == $parents)
			return true;

		if ($acceptEmptyChildren)
		{
			if (!$children) return true;
		}
		else {
			if (!$children) return false;
		}

		if (is_array($children)) {
			foreach ($children as $singleChild) {
				if ($this->isChildRole($singleChild, $parents)) return true; // recursive check
			}
		}

		if (is_array($parents)) {
			foreach ($parents as $singleParent) {
				if ($this->isChildRole($children, $singleParent)) return true; // recursive check
			}
		}

		if (in_array($children, $this->getRoleAncestors($parents))) return true; // main final check of element

		return false;
	}
}