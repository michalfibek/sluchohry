<?php

namespace App\Model;

use Nette,
    Nette\Utils\Strings,
    Nette\Security\Passwords;

class User extends Base
{
    /**
     * Gets all roles to associated array.
     * @return array|Nette\Database\Table\IRow[]
     */
    public function getRolePairs()
    {
        return $this->db->table('role')->fetchPairs('id', 'name');
    }

    public function insert($data)
    {
        $data['password'] = Passwords::hash($data['password']);

        return parent::insert($data);
    }

    public function updateById($id, $data)
    {

        if ($data['password'])
            $data['password'] = Passwords::hash($data['password']);
        else
            unset($data['password']);

        return parent::updateById($id, $data);
    }

}