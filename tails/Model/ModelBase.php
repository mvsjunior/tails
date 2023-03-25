<?php

namespace VolgPhp\Tails\Model;

use VolgPhp\Tails\Connection;

abstract class ModelBase {

    static $conn;
    protected $TABLE;
    protected $className = __CLASS__;
    protected array $newData  = [];
    protected $fillable;

    public function __construct() 
    {
        self::setConnection();
    }

    public static function setConnection($connectionName = "default")
    {
        self::$conn = Connection::open($connectionName);
    }

    public function __set($key, $value)
    {
        for($i = 0; $i < count($this->fillable); $i++)
        {
            if($key == $this->fillable[$i])
            {
                $this->{$key}        = $value;
                $this->newData[$key] = $value;
            }
        }
    }

    public function __get($key)
    {
        return $this->{$key};
    }

    public function getAll($conditions = [])
    {
        $conn = self::$conn;

        $dataQuery = [];

        $where = "WHERE ";

        if(empty($conditions))
        {
            $stmt = $conn->query("SELECT * FROM {$this->TABLE}");
        }
        else
        {
            foreach($conditions as $condition)
            {
                $where .= " {$condition[0]} {$condition[1]} :{$condition[0]} " . (isset($condition[3]) ? $condition[3] : "") . " ";
                $dataQuery += [$condition[0] => $condition[2]];
            }

            $stmt = $conn->prepare("SELECT * FROM {$this->TABLE} {$where}");
            $stmt->execute($dataQuery);
        }

        return $stmt->fetchAll(\PDO::FETCH_CLASS, $this->className);
    }

    public function find($id)
    {
        $conn = self::$conn;

        $stmt = $conn->prepare("SELECT * FROM {$this->TABLE} WHERE id=:id");
        $stmt->execute(["id" => $id]);

        return $stmt->fetchObject($this->className);
    }

    public function saveChanges()
    {
        $hasNewData = empty($this->newData);

        if($hasNewData)
        {
            return;
        }
        else
        {
            $dataPrepared = $this->buildSqlPrepareValues($this->newData);

            $dataPrepared["preparedKeysAndValues"]["id"] = $this->id;

            $sql = "UPDATE {$this->TABLE} SET {$dataPrepared['preparedValues']} WHERE id=:id";

            try
            {
                self::$conn->prepare($sql)->execute($dataPrepared['preparedKeysAndValues']);
            }
            catch (\Exception $e)
            {
                echo $e->getMessage();
            }
        }
    }

    public function buildSqlPrepareValues(array $newData = []): array
    {
        $execResult = [];

        $sqlSetValues = " ";

        foreach($newData as $attribute => $value)
        {
            $sqlSetValues           .= " {$attribute}=:{$attribute},";
            $dataValues[$attribute]  = $value;
        }

        $sqlSetValues = substr($sqlSetValues, 0, -1);

        $execResult["preparedValues"] = $sqlSetValues;
        $execResult["preparedKeysAndValues"] = $dataValues;

        return $execResult;
    }
}