<?php
namespace App\Model;

use Nette;
use Tracy\Debugger;

abstract class Base extends Nette\Object
{
    const TABLE_PREFIX = '';

    protected $db;

    /** @var string */
    protected $tableName;


    public function __construct(\Nette\Database\Context $connection)
    {
        $this->db = $connection;
        $this->tableName = $this->tableNameByClass(get_class($this));
    }

    /**
     * Gets table name by class
     * @param string $className
     * @return string
     * @result: Pages => pages, ArticleTag => article_tag
     */
    private function tableNameByClass($className)
    {
        $tableName = explode("\\", $className);
        $tableName = strtr(array_pop($tableName), array('Insecure' => ''));
        $tableName = lcfirst($tableName);

        $replace = array('insecure' => ''); // A => _a
        foreach (range("A", "Z") as $letter) {
            $replace[$letter] = "_" . strtolower($letter);
        }

        return self::TABLE_PREFIX . strtr($tableName, $replace);
    }

    /**
     * Returns table row by primary id
     *
     * @param int $id Primary id
     * @return bool|row False or row itself
     */
    public function getById($id)
    {
        return $this->db->table($this->tableName)->get($id);
    }

    /**
     * Returns table row by primary id
     *
     * @param string $colName column name
     * @param string $value column value
     * @return Nette\Database\Table\IRow|boolean
     */
    public function getByColumn($colName, $value)
    {
        return $this->db->table($this->tableName)->where($colName, $value)->fetch();
    }

    /**
     * Inserts data into table
     *
     * @param array $data Associative array
     * @return boolean|ActiveRow Returned row or FALSE when fail
     */
    public function insert($data)
    {
        return $this->db->table($this->tableName)->insert($data);
    }

    /**
     * Checks for unique parameters, eg. user email.
     *
     * @param $columnName Column name
     * @param $value Column value
     * @param $id User id
     * @return Nette\Database\Table\Selection
     */
    public function isUniqueColumn($columnName, $value, $id = null)
    {
        $record = $this->db->table($this->tableName)->where($columnName, $value);

        if ($id) {
            $primary = $this->db->table($this->tableName)->getPrimary();
            $record = $record->where($primary.' <>',$id);
        }

        $row = $record->fetch();

        if ($row == false) // is unique -> okay
            return true;
        else // is not unique -> bad
            return false;
    }

    /**
     * Updates row in db by id
     *
     * @param int $id
     * @param array $data - array in format column => value
     * @return int|FALSE number of affeceted rows or FALSE when error
     */
    public function updateById($id, $data)
    {
        if (is_array($id)) {
            return $this->db->table($this->tableName)->where('id', $id)->update($data);
        } else {
            return $this->db->table($this->tableName)->wherePrimary($id)->update($data);
        }
    }

    /**
     * Deletes row by primary ID
     *
     * @param $id
     * @return int
     */
    public function deleteById($id)
    {
        return $this->db->table($this->tableName)->wherePrimary($id)->delete();
    }


    /**
     * Deletes everything from table
     *
     * @return int
     */
    public function deleteAll()
    {
        return $this->db->table($this->tableName)->delete();
    }

    /**
     * Fetch data from table with limit
     *
     * @param int $limit
     * @param int $offset
     * @return \Nette\Database\Table\Selection
     */
    public function get($limit = 30, $offset = 0)
    {
        return $this->getAll()->limit($limit, $offset);
    }

    /**
     * Fetch everything from table
     *
     * @return \Nette\Database\Table\Selection
     */
    public function getAll()
    {
        return $this->db->table($this->tableName);
    }

    /**
     * Fetches data into array grouped by $column
     *
     * @param $column
     * @return array
     */
    public function getGroupAsArray($column = "name")
    {
        $array = array();
        $rows = $this->getAll()->group($column);
        foreach ($rows as $row)
            $array[$row->$column] = $row->$column;
        return $array;
    }

    /**
     * Fetch data from the table by id
     *
     * @param string $column
     * @return array
     */
    public function getGroupAsArrayWithId($column = "name")
    {
        $array = array();
        $rows = $this->getAll()->select("id, " . $column);
        foreach ($rows as $row)
            $array[$row->id] = $row->$column;
        return $array;
    }

    /**
     * Fetches everything as array
     *
     * @param string $column
     * @return array
     */
    public function getAsArray($column = "name")
    {
        $array = array();
        $rows = $this->getAll()->select("id, ".$column);
        foreach ($rows as $row)
            $array[$row->id] = $row->$column;
        return $array;
    }

    /**
     * @param string $firstColumn
     * @param string $secondColumn
     * @return array
     */
    public function getAllPairs($firstColumn, $secondColumn)
    {
        $array = array();
        $rows = $this->getAll();
        foreach ($rows as $row) {
            $array[$row->$firstColumn] = $row->$secondColumn;
        }
        return $array;
    }

    /**
     * Fetches count of rows in table
     *
     * @return bool|int - count of rows
     */
    public function getCount()
    {
        return $this->db->table($this->tableName)->count('*');
    }

    /**
     * Select particular columns from table
     *
     * @param $columns
     * @return $this|Nette\Database\Table\Selection
     */
    public function select($columns)
    {
        return $this->db->table($this->tableName)->select($columns);
    }

    /**
     * Applies standard query.
     *
     * @param $query
     * @return Nette\Database\ResultSet
     */
    public function query($query)
    {
        return $this->db->query($query);
    }

    protected function setLocaleNames($localeName = 'cs_CZ')
    {
        return $this->db->query('SET lc_time_names = ' . $localeName);
    }
}