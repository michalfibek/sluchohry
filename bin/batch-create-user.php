<?php

$container = require __DIR__ . '/../app/bootstrap.php';
$manager = $container->getByType('App\Model\User');


foreach ($users as $u) {
	list($username, $realname, $email, $password) = explode(';', $u);

	$groupId = 5; /// GROUP DEF

	$insertData = array(
		'username' => $username,
		'password' => $password,
		'email' => $email,
		'realname' => $realname,
		'avatar_id' => 1,
		'group_id' => $groupId,
	);

	try {
		$manager->insert($insertData);
		echo "User $username was added.\n";

	} catch (App\Model\DuplicateNameException $e) {
		echo "Error: duplicate name.\n";
		exit(1);
	}
}
