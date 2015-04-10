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
		 * Student - access only to games.
		 */
		$this->allow('student', array(
			'Front:Default',
			'Front:Profile',
			'Front:Game:MelodicCubes',
			'Front:Game:Pexeso',
			'Front:Game:NoteSteps'
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
	 * @return bool
	 */
	public function isChildRole($children, $parents)
	{
		if ($children == $parents)
			return true;
		else
			if (is_array($children)) {
				foreach ($children as $singleChild) {

					if (is_array($parents)) {
						foreach ($parents as $singleParent) {
							if (in_array($singleChild, $this->getRoleAncestors($singleParent))) return true;
						}
					} // is_array($parents)
					else {
						if (in_array($singleChild, $this->getRoleAncestors($parents))) return true;
					} // NOT is_array($parents)

				} // foreach ($children as $singleChild)

			} // is_array($children)
			else {

				if (is_array($parents)) {
					foreach ($parents as $singleParent) {
						if (in_array($children, $this->getRoleAncestors($singleParent))) return true;
					}
				} // is_array($parents)
				else {
					if (in_array($children, $this->getRoleAncestors($parents))) return true;
				} // NOT is_array($parents)

			} // NOT is_array($child)

		return false;
	}
}