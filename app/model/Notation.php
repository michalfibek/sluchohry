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
    const NOTATION_REST_SYMBOL = '_';
    /**
     * @param array $omitNotations
     * @param int|array $gameLimit
     * @param string $lengthRange All
     * @return Nette\Database\Table\IRow|null
     */
    public function getRandom($omitNotations = null, $gameLimit = null, $lengthRange = NULL)
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

        if ($lengthRange) {
            list($lengthMin, $lengthMax) = explode('-', $lengthRange);
            $notationsAll = $notationsAll->where('length >= ? AND length <= ?', $lengthMin, $lengthMax);
        }

        $fetch = $notationsAll->fetchAll();

        if ($fetch)
            return $notationsAll[array_rand($fetch)];
        else
            return null;
    }

    private function getNotationLength($sheet)
    {
        $sheet = trim($sheet); // just for case

        $noteArr = preg_split('/\s+/', $sheet); // split by any whitespace

        foreach ($noteArr as $key => $note) {
            if ($note == self::NOTATION_REST_SYMBOL) unset($noteArr[$key]);
        }

        return count($noteArr);

    }

    public function updateById($id, $data)
    {
        $data['sheet'] = trim($data['sheet']); // remove whitespaces
        $data['length'] = $this->getNotationLength($data['sheet']);

        $data['update_time'] = new Nette\Utils\DateTime;
        return parent::updateById($id, $data);
    }

    public function insert($data)
    {
        $data['sheet'] = trim($data['sheet']); // remove whitespaces
        $data['length'] = $this->getNotationLength($data['sheet']);

        return parent::insert($data);
    }

}