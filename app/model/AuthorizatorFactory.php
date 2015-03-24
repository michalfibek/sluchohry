<?php
namespace App\Model;

use Nette\Security\Permission;

class AuthorizatorFactory
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

		$permission->allow('student', array(
			'Front:Default',
			'Front:Game:MelodicCubes',
			'Front:Game:MelodicCubes'
		));

		$permission->allow('editor', array(
			'Admin:Default',
			'Admin:Song',
			'Admin:User'
		));

		$permission->allow('admin', Permission::ALL, Permission::ALL);

		return $permission;
	}

}