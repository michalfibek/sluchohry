<?php

namespace App\Model;

use Nette,
    Nette\Utils\Strings,
    Nette\Security\Passwords;
use Tracy\Debugger;

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
        if (!$data['avatar_id'])
            $data['avatar_id'] = 1; // set default avatar

        $data['password'] = Passwords::hash($data['password']);

        return parent::insert($data);
    }

    public function updateById($id, $data)
    {
        if (!$data['avatar_id'])
            $data['avatar_id'] = 1; // set default avatar

        if (isset($data['password']))
        {
            if (strlen($data['password']) > 0 ) // correct password length verification is already in form
                $data['password'] = Passwords::hash($data['password']);
            else
                unset($data['password']);
        }

        return parent::updateById($id, $data);
    }

}