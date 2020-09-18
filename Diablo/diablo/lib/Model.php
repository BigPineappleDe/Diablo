<?php
/**
 * 模型基类
 */

namespace diablo\lib;

use diablo\lib\Config;
use diablo\lib\Logger;

class Model
{
    public $conn;

    public function __construct()
    {
        $database = Config::get('database');
        try {
            $this->conn = mysqli_connect($database['DB_HOST'], $database['USERNAME'], $database['PASSWORD'], $database['DB_NAME']);
            $this->conn->query("SET NAMES 'utf8'");
        } catch (\Exception $e) {
            Logger::setLog($e->getMessage());
        }
    }

    //获取数据并格式化
    public function find($sql, $key = null)
    {
        $data = array();
        $result = mysqli_query($this->conn, $sql);
        while ($row = mysqli_fetch_array($result)) {
            if (!empty($key)) {
                $data[$row[$key]] = $row;
            } else {
                $data[] = $row;
            }
        }
        return $data;
    }

    //获取一条数据
    public function get($sql)
    {
        $data = [];
        $result = mysqli_query($this->conn, $sql);
        while ($row = mysqli_fetch_array($result)) {
            $data = $row;
        }
        return $data;
    }

    //查询构造器
    public function select($table, $select, $arr = null, $one = null, $limit = "")
    {
        $where = " where 1 ";
        if ($arr) {
            foreach ($arr as $key => $v) {
                $where .= ' and `' . $key . '`="' . $v . '" ';
            }
        }
        if ($limit) {
            $limit = " limit " . $limit;
        }
        $sql = "select " . $select . " from `" . $table . "` " . $where . $limit;
        $data = $one ? $this->get($sql) : $this->find($sql);
        return $data;
    }

    //添加一条数据
    public function insert($table, $arr)
    {
        $k = '';
        $v = '';
        foreach ($arr as $key => $vo) {
            $k .= ' `' . $key . '`,';
            $vo = str_replace('"', '\"', $vo);
            $v .= ' "' . $vo . '",';
        }
        $k = substr($k, 0, strlen($k) - 1);
        $v = substr($v, 0, strlen($v) - 1);
        $sql = ' INSERT INTO ' . $table . '(' . $k . ') VALUES (' . $v . ') ';
        $result = $this->query($sql);
        return mysqli_insert_id($this->conn);
    }

    //更新一条数据
    public function update($table, $arr, $whereArr)
    {
        $where = ' where 1 ';
        foreach ($whereArr as $key => $v) {
            $where .= ' and `' . $key . '`="' . $v . '" ';
        }
        $data = '';
        foreach ($arr as $key => $vo) {
            $vo = str_replace('"', '\"', $vo);
            $data .= ' `' . $key . '`="' . $vo . '",';
        }
        $data = substr($data, 0, strlen($data) - 1);
        $sql = 'UPDATE ' . $table . ' SET ' . $data . $where;
        $result = $this->query($sql);
        return $result;
    }

    //删除一条数据
    public function delete($table, $arr)
    {
        $where = ' where 1 ';
        foreach ($arr as $key => $vo) {
            $where .= ' and `' . $key . '`="' . $vo . '" ';
        }
        $sql = 'delete from ' . $table . $where;
        $result = $this->query($sql);
        return $result;
    }

    //执行一条sql
    public function query($sql)
    {
        mysqli_query($this->conn, $sql);
        $res = mysqli_affected_rows($this->conn);
        return $res;
    }


    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        mysqli_close($this->conn);//销毁
    }

}