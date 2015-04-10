<?php

namespace App\Model;

use Nette,
    Nette\Utils\Strings,
    Nette\Security\Passwords;
use Tracy\Debugger;

class User extends Base
{
    public function insert($data)
    {
        $data['password'] = Passwords::hash($data['password']);

        return parent::insert($data);
    }

    /**
     * Returns associative array of group id's belonged to user
     *
     * @param int $id
     * @return array|null
     */
    public function getUserGroupIds($id)
    {
        return $this->getById($id)->related('group')->fetchPairs(NULL, 'group_id');
    }

    /**
     * @param int $id User id
     * @return array Array of role names
     */
    public function getUserRoles($id)
    {
        $groups = $this->getUserGroupIds($id);
        return $this->db->table('group')->where('group.id', $groups)->select('role.name')->fetchPairs(NULL, 'name');
    }

    public function updateById($id, $data)
    {
        if (isset($data['group_id']))
        {
            $this->addGroupsToUser($id, $data['group_id']);
        }

        if (isset($data['password']))
        {
            if (strlen($data['password']) > 0 ) // correct password length verification is already in form
                $data['password'] = Passwords::hash($data['password']);
            else
                unset($data['password']);
        }

        return parent::updateById($id, $data);
    }

    private function addGroupsToUser($id, $groups)
    {
        $this->db->table('user_has_group')->where('user_id',$id)->delete(); // TODO - maybe you shouldn't delete groups every time, huh?

        if (is_array($groups)) { // multiple groups

            foreach ($groups as $group)
                $this->db->table('user_has_group')->insert(array(
                    'user_id' => $id,
                    'group_id' => $group
                ));

        } else { // single group

            $this->db->table('user_has_group')->insert(array(
                'user_id' => $id,
                'group_id' => $group
            ));

        }
    }

}