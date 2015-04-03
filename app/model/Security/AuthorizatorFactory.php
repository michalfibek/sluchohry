<?php
namespace App\Model\Security;

use Nette,
	Nette\Security\Permission;

class AuthorizatorFactory extends Nette\Object
{

	/**
	 * @return \Nette\Security\IAuthorizator
	 */
	public function create()
	{
		$permission = new Permission();

		$permission->addRole('guest');
		$permission->addRole('student', 'guest');
		$permission->addRole('teacher', 'student');
		$permission->addRole('editor', 'teacher');
		$permission->addRole('admin', 'editor');

		$permission->addResource('Front:Default');
		$permission->addResource('Front:Game:MelodicCubes');
		$permission->addResource('Front:Game:Pexeso');

		$permission->addResource('Admin:Default');
		$permission->addResource('Admin:Song');
		$permission->addResource('Admin:User');
		$permission->addResource('Admin:Group');

		$permission->allow('student', array(
			'Front:Default',
			'Front:Game:MelodicCubes',
			'Front:Game:Pexeso'
		));

		/**
		 * Teacher - access only to basic listing of users, etc. Defined by privilege (action)
		 * fine grained permission control.
		 */
		$permission->allow('teacher', array(
			'Admin:Default',
			'Admin:User',
			'Admin:Group'
		), array(
			'default'
		));

		/**
		 * Editor - access to everything defined here, no exceptions in privileges/actions.
		 */
		$permission->allow('editor', array(
			'Admin:Default',
			'Admin:Song',
			'Admin:User',
			'Admin:Group'
		));

		$permission->allow('admin', Permission::ALL, Permission::ALL);

		return $permission;
	}
}