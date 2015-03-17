<?php
namespace App\Model;

use Nette;

class SongStorage extends Nette\Object
{
    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
        $this->songBaseDir = $_SERVER['DOCUMENT_ROOT'] . $this->songDir; // TODO check dir paths!
        $this->uploadBaseDir = __DIR__ . $this->uploadDir;
        $this->songDefaultFormat = 'mp3';
    }

    /** @return Nette\Database\Table\Selection */
    public function getAll()
    {
        return $this->database->table('song');
    }

    public function getById($id)
    {
        return $this->getAll()->get($id);
    }

    public function insert($values)
    {
        return $this->getAll()->insert($values);
    }
}