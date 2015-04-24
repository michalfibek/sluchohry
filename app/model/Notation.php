<?php
namespace App\Model;

use Nette,
    App\Services;
use Tracy\Debugger;

/**
 * Manipulate with notation files.
 */
class Notation extends Base
{
    public function getRandom($omitNotations = null, $gameLimit = null)
    {
        $notationsAll = $this->getAll();

        // fetch notations only for certain game
        if ($gameLimit) {
            $notationsForGame = $this->db->table('game_has_notation')->where('game_id', $gameLimit)->fetchPairs(null, 'notation_id');
            $notationsAll = $notationsAll->where('id IN', $notationsForGame);
        }

        // skip notations with $omitSongs id's
        if ($omitNotations) {
            $notationsAll = $notationsAll->where('id NOT IN', $omitNotations);
        }

        $fetch = $notationsAll->fetchAll();

        if ($fetch)
            return $notationsAll[array_rand($fetch)];
        else
            return null;
    }

    public function updateById($id, $data)
    {
        $data['update_time'] = new Nette\Utils\DateTime;
        return parent::updateById($id, $data);
    }
}