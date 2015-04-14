<?php
namespace App\Model;

use Nette,
    App\Services;
use Tracy\Debugger;

/**
 * Game table
 */
class Game extends Base
{
    /**
     * @param Nette\Database\Context $db
     */
    public function __construct(Nette\Database\Context $db)
    {
        parent::__construct($db);
    }

    public function getDifficultyVariation($gameId, $difficultyId)
    {
        return $this->db->table('difficulty_variation')->where('game_id', $gameId)->where('difficulty_id', $difficultyId)->fetch()->value;
    }

    public function getByName($name)
    {
        return $this->getByColumn('name', $name);
    }

    public function getBySong($songId)
    {
        return $this->db->table('game_has_song')->where('song_id', $songId);
//        return $this->db->query('SELECT game_id FROM game_has_song WHERE song_id=?', $songId);
    }

    public function updateSongAssoc($songId, $gameIdArray)
    {
        $currentAssoc = $this->getBySong($songId);

        if ($currentAssoc == $gameIdArray) {
            return false;
        }

        // updating, so delete old records
        $this->db->table('game_has_song')->where('song_id',$songId)->delete();

        // do we insert?
        if ($gameIdArray)
        {
            foreach ($gameIdArray as $gameId)
            {
                $insertArray = array(
                    'game_id' => $gameId,
                    'song_id' => $songId
                );
                return $this->db->table('game_has_song')->insert($insertArray);
            }
        }
    }

}