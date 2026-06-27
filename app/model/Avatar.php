<?php
namespace App\Model;

use Nette;

/**
 * Assign user avatars
 */
class Avatar extends Base
{
    /** @var string - directory for avatar files */
    private $saveDir;

    /**
     * @param $saveDir string
     * @param Nette\Database\Explorer $db
     */
    public function __construct($saveDir, Nette\Database\Explorer $db)
    {
        parent::__construct($db);
        $this->saveDir = $saveDir;
    }

    public function getDir()
    {
        return '/assets/images/avatar';
    }

    public function getDefault() // get default avatar
    {
        return parent::getById(1);
    }

}