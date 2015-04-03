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
            ->select('group_id, COUNT(user_id) as cnt')
            ->fetchPairs('group_id', 'cnt');
    }

}