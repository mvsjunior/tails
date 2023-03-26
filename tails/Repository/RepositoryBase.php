<?php

namespace VolgPhp\Tails\Repository;

use VolgPhp\Tails\Connection;

abstract class RepositoryBase {

    static $conn;
    protected $TABLE;
    protected $modelClass;
    protected array $newData  = [];
    protected $fillable;

    const CONDITION_KEY      = 0;
    const CONDITION_OPERATOR = 1;
    const CONDITION_VALUE    = 2;

    public function __construct() 
    {
        self::setConnection();
    }

    public static function setConnection($connectionName = "default")
    {
        self::$conn = Connection::open($connectionName);
    }

    /** *****************************************************************
     *                          READ
     *  -----------------------------------------------------------------
     *  - Métodos para consulta dos dados na tabela
     *    - getAll()    : array 
     *    - find(<id>)  : modelObj
    */

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
                $where .= " {$condition[self::CONDITION_KEY]} {$condition[self::CONDITION_OPERATOR]} :{$condition[self::CONDITION_KEY]} " 
                            . ( isset($condition[3]) ? $condition[3] : "" ) 
                            . " ";

                $dataQuery[ $condition[self::CONDITION_KEY] ] = $condition[self::CONDITION_VALUE];
            }

            $stmt = $conn->prepare("SELECT * FROM {$this->TABLE} {$where}");
            $stmt->execute($dataQuery);
        }

        return $stmt->fetchAll(\PDO::FETCH_CLASS, $this->modelClass);
    }

    public function find($id)
    {
        $conn = self::$conn;

        $stmt = $conn->prepare("SELECT * FROM {$this->TABLE} WHERE id=:id");
        $stmt->execute(["id" => $id]);

        return $stmt->fetchObject($this->modelClass);
    }


    /** *****************************************************************
     *                          SAVE
     *  -----------------------------------------------------------------
     *  - Métodos para salvar os registros na tabela
     *    - save(<array<model>>): void
    */
    public function save(array $models = [])
    {
        $hasNewData = empty($model) === false;

        if($hasNewData)
        {
            return;
        }
        else
        {
            foreach($models as $model)
            {
                try
                {
                    $this->executeSave($model);
                }
                catch (\Exception $e)
                {
                    echo $e->getMessage();
                }
            }    
        }
    }
    
    public function executeSave($model)
    {
        $modelArray  = get_object_vars($model);

        $dataPrepared = $this->buildInsertPrepareValues($modelArray);

        $sql = "INSERT INTO {$this->TABLE}  {$dataPrepared['preparedValues']}";

        self::$conn->prepare($sql)->execute($dataPrepared['preparedKeysAndValues']);
    }
    
    public function buildInsertPrepareValues(array $newData = []): array
    {
        $execResult = [];

        $setColumn = " ";
        $setValues = " ";

        foreach($newData as $attribute => $value)
        {
            $setColumn .= " {$attribute},";
            $setValues .= " :{$attribute},";

            $dataValues[$attribute]  = $value;
        }

        $setColumn = substr($setColumn, 0, -1);
        $setValues = substr($setValues, 0, -1);

        $sqlStmt   = "({$setColumn}) VALUES ({$setValues})";

        $execResult["preparedValues"]        = $sqlStmt;
        $execResult["preparedKeysAndValues"] = $dataValues;

        return $execResult;
    }

    /** *****************************************************************
     *                          UPDATE
     *  -----------------------------------------------------------------
     *  - Métodos para atualizar os registros na tabela
     *    - update(<array<model>>)    : array 
    */
    public function update(array $models = [])
    {
        $hasNewData = empty($model) === false;

        if($hasNewData)
        {
            return;
        }
        else
        {
            foreach($models as $model)
            {
                try
                {
                    $this->executeUpdate($model);
                }
                catch (\Exception $e)
                {
                    echo $e->getMessage();
                }
            }    
        }
    }

    public function executeUpdate($model):void
    {
        $modelArray  = get_object_vars($model);

        $dataPrepared = $this->buildUpdatePrepareValues($modelArray);

        $dataPrepared["preparedKeysAndValues"]["id"] = $model->id;

        $sql = "UPDATE {$this->TABLE} SET {$dataPrepared['preparedValues']} WHERE id=:id";

        self::$conn->prepare($sql)->execute($dataPrepared['preparedKeysAndValues']);
    }


    public function buildUpdatePrepareValues(array $newData = []): array
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

   /** *****************************************************************
    *                          DELETE
    *  -----------------------------------------------------------------
    *
   */
    public function delete($id = null)
    {
        $conn = self::$conn;

        $stmt = $conn->prepare("DELETE FROM {$this->TABLE} WHERE id=?");
        $stmt->execute([$id]);
    }
}