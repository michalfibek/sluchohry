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
        }

        // skip notations with $omitNotations id's
        if ($omitNotations) {
            if ($gameLimit) {
                foreach ($notationsForGame as $keyGame => $idViaGame) {
                    if ($keyOmit = array_search($idViaGame, $omitNotations)) {
                        unset($omitNotations[$keyOmit]);
                        unset($notationsForGame[$keyGame]);
                    }
                }

            } else {
                $notationsAll = $notationsAll->where('id NOT', $omitNotations);
            }
        }

        if ($gameLimit) {
            $notationsAll = $notationsAll->where('id', $notationsForGame);
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