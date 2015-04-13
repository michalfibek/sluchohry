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
        if (isset($data['group_id']))
        {
            $this->addGroupsToUser($id, $data['group_id']);
            unset($data['group_id']);
        }

        $data['password'] = Passwords::hash($data['password']);

        return parent::insert($data);
    }

    /**
     * Returns associative array of group id's belonged to user
     *
     * @param int $id
     * @return array|null
     */
    public function getUserGroups($id)
    {
        return $this->getById($id)->related('group');
    }

    /**
     * @param int $id User id
     * @return array Array of role names
     */
    public function getUserRoles($id)
    {
        $groups = $this->getUserGroups($id)->fetchPairs(NULL, 'group_id');
        return $this->db->table('group')->where('group.id', $groups)->select('role.name')->fetchPairs(NULL, 'name');
    }

    public function updateById($id, $data)
    {
        if (isset($data['group_id']))
        {
            $groupResult = $this->addGroupsToUser($id, $data['group_id']);
            unset($data['group_id']);
        }

        if (isset($data['password']))
        {
            if (strlen($data['password']) > 0 ) // correct password length verification is already in form
                $data['password'] = Passwords::hash($data['password']);
            else
                unset($data['password']);
        }

        $updateResult = parent::updateById($id, $data);

        return ($updateResult) ? $updateResult : $groupResult;
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

            return true;

        } else { // single group

           return $this->db->table('user_has_group')->insert(array(
                'user_id' => $id,
                'group_id' => $group
            ));

        }
    }
}