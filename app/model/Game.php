<?php

namespace App\Model;

use Nette;

class Game extends Nette\Object
{
    const
        TABLE_NAME_SONG = 'song',
        TABLE_NAME_MARKER = 'marker';

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Gets all users.
     * @return array|Nette\Database\Table\IRow[]
     */
    public function getAll()
    {
        return $this->database->table(self::TABLE_NAME)->fetchAll();
    }

    /**
     * Gets user by his ID.
     * @param $id
     * @return Nette\Database\Table\IRow
     */
    public function getById($id)
    {
        return $this->database->table(self::TABLE_NAME)->get($id);
    }

    /**
     * Gets all roles to associated array.
     * @return array|Nette\Database\Table\IRow[]
     */
    public function getRoleArray()
    {
        return $this->database->table(self::TABLE_NAME_ROLE)->fetchPairs('id', 'name');
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
            $this->database->table(self::TABLE_NAME)->insert(array(
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

}