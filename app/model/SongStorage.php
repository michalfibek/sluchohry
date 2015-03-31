<?php
namespace App\Model;

use Nette,
    App\Services;
use Symfony\Component\Config\Definition\Exception\Exception;
use Tracy\Debugger;

/**
 * Manipulate with song files.
 */
class SongStorage extends Nette\Object
{
    const
        TABLE_NAME_SONG = 'song',
        TABLE_NAME_MARKER = 'marker',
        TABLE_NAME_GAME = 'game',
        TABLE_NAME_GENRE = 'genre';

    /** @var Nette\Database\Context */
    private $database;
    /** @var Services\UploadHandler */
    private $uploadHandler;
    /** @var Services\SongTagHandler */
    private $tagHandler;
    /** @var string contains dir reference for newly uploaded files */
    private $uploadDir;
    /** @var string contains dir for music files to save to */
    private $saveDir;
    /** @var string default file format extension */
    private $songDefaultExtension;
    /** @var array all allowed extensions in array*/
    private $allowedExtensions;

    /**
     * @param $uploadDir
     * @param $saveDir
     * @param Nette\Database\Context $database
     * @param Services\UploadHandler $uploadHandler
     * @param Services\SongTagHandler $tagHandler
     */
    public function __construct($uploadDir, $saveDir, Nette\Database\Context $database, Services\UploadHandler $uploadHandler, Services\SongTagHandler $tagHandler)
    {
        $this->uploadDir = $uploadDir;
        $this->saveDir = $saveDir;
        $this->database = $database;
        $this->uploadHandler = $uploadHandler;
        $this->tagHandler = $tagHandler;
        $this->songDefaultExtension = 'mp3';
        $this->allowedExtensions = array("mp3", "wav", "ogg");
    }

    /** @return Nette\Database\Table\Selection */
    public function getSongAll()
    {
        // TODO fetchovat i zaznamy pouzitych her, zobrazovat v templatu
        return $this->database->table(self::TABLE_NAME_SONG);
    }

    /** @return Nette\Database\Table\Selection */
    public function getGameAll()
    {
        return $this->database->table(self::TABLE_NAME_GAME)
            ->order('name ASC');
    }

    public function getSongById($songId, $requireMarkers = false)
    {
        if ($requireMarkers) {
            $hasMarkers = $this->database->query('SELECT * FROM marker WHERE song_id=?', $songId)->fetchAll();
            if (!$hasMarkers) return false;
        }
        return $this->database->table(self::TABLE_NAME_SONG)->get($songId);
    }

    public function getGameAssoc($songId)
    {
        return $this->database->query('SELECT game_id FROM game_has_song WHERE song_id=?', $songId)->fetchPairs();
    }

    /**
     * @param array $omitSongs Array of songs to skip in select
     * @param bool $requireMarkers
     * @param int $gameLimit Game id
     * @param int $songCount Count of songs
     * @return array song info
     */
    public function getSongRandom($omitSongs = null, $requireMarkers = false, $gameLimit = null, $songCount = 1)
    {
        $songsAll = $this->database->table('song');
        $markedSongs = $this->database->table('marker')->select('DISTINCT song_id')->fetchPairs(null, 'song_id');

        // fetch only songs with set markers
        if ($requireMarkers)
            $songsAll = $songsAll->where('id IN', $markedSongs);

        // skip songs with $omitSongs id's
        if ($omitSongs)
            $songsAll = $songsAll->where('id NOT IN', $omitSongs);

        // fetch only songs for certain game
        if ($gameLimit)
            $songsForGame = $this->database->table('game_has_song')->where('game_id', $gameLimit)->fetchPairs(null, 'song_id');
            $songsAll = $songsAll->where('id IN', $songsForGame);

        if ($songCount == 1) { // fetching only one song

            $key = array_rand($songsAll->fetchAll());
            if ($songsAll) return $songsAll[$key]; else return null;

        } elseif ($songCount > 1 && count($songsAll) >= $songCount) { // fetching more than one song

            $keys = array_rand($songsAll->fetchAll(), $songCount);

            foreach ($keys as $key) {
                $returnSongs[] = $songsAll[$key];
            }

            return $returnSongs;


        } else { // no song found
            return null;
        }

    }

    public function getMarkersAll($songId)
    {
        return $this->database->table(self::TABLE_NAME_SONG)->get($songId)->related(self::TABLE_NAME_MARKER)->order('timecode ASC');
    }

    /**
     * @param $songId
     * @param $cubeCount Count of splits
     * @return array Associative array of markers -> eg. array(0 => 3491, 1 => 5979, 2 => 14291)
     */
    public function getCubeMarkersByCount($songId, $cubeCount)
    {
        $cubeSplits = ($cubeCount > 2) ? $cubeCount-1 : $cubeCount;

        $markersAll = $this->database->table(self::TABLE_NAME_SONG)->get($songId)->related(self::TABLE_NAME_MARKER)->order('timecode ASC')->fetchAll();

        if (count($markersAll) < $cubeSplits) $cubeSplits = count($markersAll);

        $randKeys = array_rand($markersAll, $cubeSplits);

        $markers[] = array(0, $markersAll[$randKeys[0]]->timecode);

        if ($cubeSplits > 2)
        {
            for ($i = 0; $i < $cubeSplits-1; $i++)
            {
                $inPoint = $markersAll[$randKeys[$i]]->timecode;
                $outPoint = $markersAll[$randKeys[$i+1]]->timecode - $markersAll[$randKeys[$i]]->timecode;
                $markers[] = array($inPoint, $outPoint);
            }
        }

        $markers[] = array($markersAll[$randKeys[$cubeSplits-1]]->timecode, $this->getSongById($songId)->duration - $markersAll[$randKeys[$cubeSplits-1]]->timecode);

        return $markers;
    }

    public function getGenres()
    {
        return $this->database->table(self::TABLE_NAME_GENRE);
    }

    public function handleUpload()
    {
        $this->uploadHandler->allowedExtensions = $this->allowedExtensions;
        return $this->uploadHandler->handleUpload($this->uploadDir);
    }

    public function updateSong($songId, $values)
    {
        try {
            $values['update_time'] = new Nette\Utils\DateTime;
            $song = $this->database->table(self::TABLE_NAME_SONG)->get($songId);
            $song->update($values);
        } catch (\Exception $e) {
            Debugger::log($e->getMessage());
        }
    }

    public function updateMarkers($songId, $markers = null)
    {
        // delete old markers
        $this->deleteMarkers($songId);

        if ($markers) {
            // insert new markers
            foreach ($markers as $singleMarker) {
                $this->database->table(self::TABLE_NAME_MARKER)->insert( array(
                    'song_id' => $songId,
                    'timecode' => $singleMarker
                ));
            }
        }
    }

    public function updateGameAssoc($songId, $gameIdArray)
    {
        $currentAssoc = $this->getGameAssoc($songId);

        if ($currentAssoc == $gameIdArray) {
            return false;
        }
        Debugger::barDump($currentAssoc);
        Debugger::barDump($gameIdArray);

        // updating, so delete old records
        $this->database->table('game_has_song')->where('song_id=?',$songId)->delete();

        // do we insert?
        if ($gameIdArray)
        {
            foreach ($gameIdArray as $gameId)
            {
                $insertArray = array(
                    'game_id' => $gameId,
                    'song_id' => $songId
                );
                $this->database->query('INSERT INTO game_has_song', $insertArray);
            }
        }
    }

    public function save($uploadResult)
    {
        $uploadUUIDName = $uploadResult['uuid'];
        $uploadFilePath = $this->uploadDir . $uploadUUIDName . '/' . $this->uploadHandler->getUploadName();

        $songTags = $this->tagHandler->analyze($uploadFilePath)->fetchTags();
        list($artist, $title) = $songTags->getArtistTitle();
        if (!$artist) $artist = "";
        if (!$title) $title = Services\SongTagHandler::formatFilenameToTitle($this->uploadHandler->getUploadName(), $this->songDefaultExtension);
        $duration = $songTags->getDuration();
        $fileFormat = $songTags->getFileFormat();

        $targetFilePath = $this->saveDir . $uploadUUIDName . '.' . $fileFormat;

        // try to save file to new location
        try {
            Nette\Utils\FileSystem::copy($uploadFilePath, $targetFilePath);
            Nette\Utils\FileSystem::delete($this->uploadDir . $uploadUUIDName);
        } catch (\Exception $e) {
            throw $e;
        }

        $row['artist'] = $artist;
        $row['title'] = $title;
        $row['duration'] = $duration;
        $row['filename'] = $uploadUUIDName;

        try {
            $songInsert = $this->insertSong($row);
        } catch (\Exception $e) {
            throw $e;
        }

        $uploadResult['ext'] = $fileFormat;
        $uploadResult['artist'] = $artist;
        $uploadResult['title'] = $title;
        $uploadResult['duration'] = $duration;
        $uploadResult['songId'] = $songInsert->id;
        $uploadResult['fileName'] = $songInsert->filename;

        return $uploadResult;
    }

    public function deleteSong($songId)
    {
        $song = $this->database->table(self::TABLE_NAME_SONG)->get($songId);
        if (!$song) return false;
        try {
            // delete song file
            Nette\Utils\FileSystem::delete($this->saveDir . $song->filename . '.' . $this->songDefaultExtension);

            // finally, delete song itself from db
            $song->delete();

            return true;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteMarkers($songId)
    {
        $markers = $this->database->table(self::TABLE_NAME_SONG)->get($songId)->related(self::TABLE_NAME_MARKER);
        foreach ($markers as $singleMarker) {
            $singleMarker->delete();
        }
    }

    private function insertSong($row)
    {
        return $this->database->table(self::TABLE_NAME_SONG)->insert($row);
    }


}