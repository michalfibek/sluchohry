<?php
namespace App\Model;

use Nette,
    App\Services;

/**
 * Manipulate with song files.
 */
class Notation extends Base
{
    public function getRandom()
    {
        $notationsAll = $this->getAll();
        $key = array_rand($notationsAll->fetchAll());

        if ($notationsAll) return $notationsAll[$key]; else return null;
    }

    public function updateById($id, $data)
    {
        $data['update_time'] = new Nette\Utils\DateTime;
        return parent::updateById($id, $data);
    }
}