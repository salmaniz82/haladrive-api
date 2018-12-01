<?php

require_once 'app/config.php';


class Database
{

    public $connection;
    public $table = null;
    public $sqlSyntax;
    public $resource;
    public $last_id;
    public $noRows;
    public $buildStatement;
    public $queryError;
    public $doPluck = false;


    public function __construct()
    {
        // connnect to databse and establish connect in the db
        $this->connection = new mysqli(SERVER, USER, PASSWORD, DATABASE);
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        $this->connection->set_charset("utf8");
        $this->setTimeZone();
        

    }

    public function getTable()
    {
        return $this->table;
    }

    public function setTable($table)
    {
        return $this->table = $table;
    }

    public function runQuery()
    {

        if ($result = $this->connection->query($this->sqlSyntax))
        {

            if(isset($result->num_rows)) 
            {
                $this->noRows = $result->num_rows;    
            }

            
            return $this->resource = $result;
        }
        else
            {
              
                $this->queryError = mysqli_error($this->connection);
                return false;
            }
    }

    public function returnData()
    {

        $data = null;

        while( $rows = $this->resource->fetch_assoc() )
        {
            $data[] = $rows;
        }

        return $data;

    }


    public function getbyId($id, $cols = null)
    {

        if ( is_array($cols) && !empty($cols) )
        {
            $cols = implode(", ", $cols);
            $this->sqlSyntax = "SELECT ". $cols ." FROM {$this->table} WHERE id = {$id}";

        }
        else
            {
                $this->sqlSyntax = "SELECT * FROM {$this->table} WHERE id = {$id}";
            }

        $this->runQuery();
        return $this;
    }

    public function listall($cols = null)
    {

        if (is_array($cols) && !empty($cols))
        {
            $cols = implode(", ", $cols);
            $this->sqlSyntax = "SELECT ". $cols ." FROM {$this->table} ORDER BY ID DESC";
        }
        else
            {
                $this->sqlSyntax = "SELECT * FROM {$this->table}";
            }


        $this->runQuery();
        return $this;
        
    }


    public function insert($data)
    {

        if( is_array($data) && !empty($data) )
        {
            $cols = implode(', ', array_keys($data));
            $colValues = implode("', '", $data);
            
            $colValues = "'".$colValues."'";
            $sql = "INSERT INTO ". $this->table . " (". $cols .") VALUES ". "(". $colValues . ")";
            $this->sqlSyntax = $sql;
            if($this->runQuery())
            {
                return $this->last_id = $this->connection->insert_id;
            }
            else
            {
                return false;
            }

        }

    }

    public function pluck($col)
    {
        $this->sqlSyntax = "SELECT $col FROM $this->table ";

        $this->doPluck = true;

        return $this;
    }


    public function multiInsert($dataset)
    {

        $errCount = 0;

        if(!$dataset || gettype($dataset) != 'array')
        {  
            $errCount++;
            throw new Exception("Argument must be an array");
            return false;
        }
        else if(!array_key_exists('cols', $dataset) && !array_key_exists('vals', $dataset))
        {
            $errCount++;
            throw new Exception("Array missing cols and vals named keys");
            return false;
        }

        else if(sizeof($dataset['cols']) != sizeof($dataset['vals'][0]))
        {
            $errCount++;
            throw new Exception("Array Fields size miss matched");
            return false;
        }

        if($errCount == 0)
        {
            
            $cols = implode(', ', array_values($dataset['cols']));
            $vals = $dataset['vals'];

            $this->escArray($dataset['vals']);

            $query = "INSERT INTO $this->table ( " ;

            $query .= $cols . ") VALUES ";

            $counter = 0;
            foreach ($vals as $key => $value) {

                $query .= " ( ";
                $query .= "'". implode("', '", $value) ."'"; 
                $query .= " ) ";

                if($counter != sizeof($vals)-1)
                {
                    $query .= ",";
                }

                $counter++;
            }

            $this->sqlSyntax = $query;
            return $this->runQuery();

        }

    }


    public function update($data, $id)
    {
        if(is_array($data) && !empty($data) && $id != null)
        {

            $keyValues = null;
            $curIndex = 0;

            foreach ($data as $key => $value)
            {
                $curIndex++;
                $keyValues .= $key . "='";
                if($curIndex != sizeof($data))
                {
                    $keyValues .= $value ."', ";
                } else
                    {
                        $keyValues .= $value ."' ";
                    }
            }

            $sql = "UPDATE {$this->table} SET ";
            $sql .= $keyValues;
            $sql .= "WHERE id =" . $id;

            $this->sqlSyntax = $sql;
            return $this->runQuery();
        }
        else
            {
                return false;
            }

    }

    public function delete($id, $limit=true)
    {

        if(!is_array($id))
        {
            if($limit)
            {
                $sql = "DELETE FROM {$this->table} WHERE id = {$id} LIMIT 1";    
            }

            else {
                $sql = "DELETE FROM {$this->table} WHERE id = {$id}";
            }    
        }
        else {

            $col = $id[0];
            $id = $id[1];

            if($limit)
            {
                $sql = "DELETE FROM {$this->table} WHERE {$col} = {$id} LIMIT 1";
            }
            else {
                $sql = "DELETE FROM {$this->table} WHERE {$col} = {$id}";
            }

            

        }
     
        
        $this->sqlSyntax = $sql;
        return $this->runQuery();
    }

    public function returnSet($email, $password)
    {
        $password = sha1($password);
        $sql = "SELECT id, name, email, role_id FROM {$this->table} WHERE email = '{$email}' AND password = ". "'{$password}'";
        
        $this->sqlSyntax = $sql;
        $this->runQuery();

        if ($this->resource != false)
        {
            return $this->returnData();
        }
        else
            {
                return false;
            }

    }


    public function get()
    {
        /*
            return data to maintain chaining methods so in the end we can return the object instead of output
            like $db->getList(['id', 'name'])->where('id = 23')->paginate(60);
        */
    }

  

    public function rawSql($rawSqlString)
    {
        $this->sqlSyntax = $rawSqlString;
        $this->runQuery();
        return $this;
    }

    public function sanitize($colums)
    {


        foreach ($colums as $key => $value) {
            if(isset($_POST[$value]))
            {
                $data[$value] = mysqli_real_escape_string($this->connection, $_POST[$value]);  
            }
        }

        return $data;

    }


    public function escArray($dataarray)
    {

        $link = $this->connection;
        array_walk_recursive($dataarray, function(&$val) use ($link) {

            $val = $link->real_escape_string($val);

        });

    }



    public function build($queryType)
    {

        switch($queryType)
        {

            case "S":
                $string = "SELECT ";
                break;
            case "U":
                $string = "UPDATE {$this->table} SET";
                break;
            case "D": 
                $string = "DELETE FROM {$this->table} ";
                break;
            case "C": 
                $string = "COUNT {$this->table} ";
                break;
            default:
                $string = "SELECT ";

        }


        $this->sqlSyntax = $string;
        return $this;

    }


    public function Colums($cols = '*')
    {
        $string = $this->sqlSyntax;
        $string .= $cols . " ";
        $string .= "FROM {$this->table}";
        $this->sqlSyntax = $string;
        return $this;
    }


    public function Where($criteria)
    {
        $string = $this->sqlSyntax;

        if ( strpos($this->sqlSyntax, 'WHERE') ) {

            $string .= ' AND ' . $criteria . " ";

        }

        else 

        {

            $string .= ' WHERE ' . $criteria . " "; 

        }

        $this->sqlSyntax = $string;


        if($this->doPluck)
        {
            $this->sqlSyntax .= ' LIMIT 1';         
            $this->doPluck = false;

            

            $this->runQuery();


            if($this->noRows)
            {
                $pluckData = $this->returnData();

                    $pluckData = array_values($pluckData[0]);
                    $pluckData = $pluckData[0];

                    if(is_numeric($pluckData))
                    {
                        return (int) $pluckData;
                    }
                    else {
                        return $pluckData;   
                    }
            }

            else {
                return false;


            }
            
            

            
        }

        return $this;

    }


    public function paginate($limit, $offset)
    {
        $string = $this->sqlSyntax;

        $string .= " LIMIT ". $limit .' OFFSET '. $offset;

        $this->sqlSyntax = $string;

        return $this;

    }

    public function Limit($limit)
    {

        $string = $this->sqlSyntax;

        $string .= ' LIMIT ' . $limit;

        $this->sqlSyntax = $string;

        return $this;

    }

    public function go()
    {
        $this->runQuery();
        return $this;
    }


    public function showQuery()
    {
        return $this->sqlSyntax;
    }

    public function getCharset()
    {
         return $this->connection->character_set_name();
    }

    public function setTimeZone()
    {

        $now = new DateTime();
        $mins = $now->getOffset() / 60;
        $sgn = ($mins < 0 ? -1 : 1);
        $mins = abs($mins);
        $hrs = floor($mins / 60);
        $mins -= $hrs * 60;
        $offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
        $this->connection->query("SET time_zone='{$offset}'");

    }

}