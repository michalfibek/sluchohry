<?php

namespace App\Model;

use Nette;

class Group extends Base
{
    public function getUserCount()
    {
        // TODO properly finish this method
        return $this->db->table('user_has_group')
            ->group('group_id')
            ->select('group_id, role_id, COUNT(user_id) as cnt')
            ->fetchPairs('group_id', 'cnt');
    }

    public function getGroupRoles($groups)
    {
        return $this->db->table($this->tableName)->where('group.id', $groups)->select('role.name')->fetchPairs(NULL, 'name');
    }

    /**
     * Gets all roles to associated array.
     * @return array|Nette\Database\Table\IRow[]
     */
    public function getRolePairs()
    {
        return $this->db->table('role')->fetchPairs('id', 'name');
    }

}