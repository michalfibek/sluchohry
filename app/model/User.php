<?php

namespace App\Model;

use Nette,
    Nette\Utils\Strings,
    Nette\Security\Passwords;

class User extends Nette\Object
{
    const
        COLUMN_ID = 'id',
        COLUMN_NAME = 'username',
        COLUMN_REALNAME = 'realname',
        COLUMN_EMAIL = 'email',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_ROLE = 'role_id';

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * @return Nette\Database\Table\Selection
     */
    public function getAll()
    {
        return $this->database->table('user');
    }

    /**
     * @return Nette\Database\Table\Selection
     */
    public function getGroupAll()
    {
        return $this->database->table('group');
//            ->group('group_id')
//            ->having('COUNT(user_id) > 0');
    }

    public function getGroupCount()
    {
        // TODO properly finish this method
        return $this->database->table('user_has_group')
            ->group('group_id')
            ->select('group_id, COUNT(user_id) as cnt')
            ->fetchPairs('group_id', 'cnt');
    }

    /**
     * Gets user by his ID.
     * @param $id
     * @return Nette\Database\Table\IRow
     */
    public function getById($id)
    {
        return $this->database->table('user')->get($id);
    }

    public function getGroupById($id)
    {
        return $this->database->table('group')->get($id);
    }

    /**
     * Gets all roles to associated array.
     * @return array|Nette\Database\Table\IRow[]
     */
    public function getRoleArray()
    {
        return $this->database->table('role')->fetchPairs('id', 'name');
    }

    /**
     * Adds new user.
     * @param $username
     * @param $password
     * @param $email
     * @param null $realname
     * @throws DuplicateNameException
     * @return void
     */
    public function add($username, $password, $email, $realname, $roleId)
    {
        try {
            $this->database->table('user')->insert(array(
                    self::COLUMN_NAME => $username,
                    self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
                    self::COLUMN_EMAIL => $email,
                    self::COLUMN_REALNAME => $realname,
                    self::COLUMN_ROLE => $roleId
                )
            );
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
        }
    }

    public function addGroup($name)
    {
        try {
            $this->database->table('group')->insert(array(
                    'name' => $name
                )
            );
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
        }
    }

    public function update($id, $username, $password, $email, $realname, $roleId)
    {
        $user = $this->database->table('user')->wherePrimary($id);
        $user->update(array(
            self::COLUMN_NAME => $username,
            self::COLUMN_EMAIL => $email,
            self::COLUMN_REALNAME => $realname,
            self::COLUMN_ROLE => $roleId
        ));
        if ($password)
            $user->update(array(
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password)
            ));
    }

    public function updateGroup($id, $name)
    {
        $this->database->table('group')->wherePrimary($id)->update(array(
            'name' => $name,
        ));
    }

    public function delete($id)
    {
        $this->database->table('user')->wherePrimary($id)->delete();
    }

    public function deleteGroup($id)
    {
        $this->database->table('group')->wherePrimary($id)->delete();
    }


}