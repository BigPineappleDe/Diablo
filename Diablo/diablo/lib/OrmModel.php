<?php


namespace diablo\lib;

use diablo\lib\Config;

abstract class OrmModel
{
    public $conn;
    public $tableName;
    public $pk;
    public $e;
    public $where = "";
    public $groupBy = "";
    public $orderBy = "";
    public $limit = "";
    public $entityList = [];

    public function __construct()
    {
        $database = Config::get('database');
        try {
            $this->conn = mysqli_connect($database['DB_HOST'], $database['USERNAME'], $database['PASSWORD'], $database['DB_NAME']);
            $this->conn->query("SET NAMES 'utf8'");
        } catch (\Exception $e) {
            Logger::setLog($e->getMessage());
        }
        $this->tableName = $this->table();
        $this->pk = $this->primaryKey();
        $this->entityList = $this->entity();
    }

    //表名
    abstract protected function table();

    //主键
    abstract protected function primaryKey();

    //映射实体
    abstract protected function entity();

    //条件 默认是 =
    public function where($arr)
    {
        $arr[0] = $this->whereEntityMapping($arr[0]);
        $where = "";
        //有自定义条件
        if (count($arr) > 2) {
            if (strpos($arr[2], '"') === false || strpos($arr[2], "'") === false) {
                if ($arr[1] != 'in') {
                    $arr[2] = " '" . $arr[2] . "' ";
                }
            }
            $where = " and `" . $arr[0] . "` " . $arr[1] . " " . $arr[2] . " ";
        } else {//无自定义条件
            if (strpos($arr[1], '"') === false || strpos($arr[1], "'") === false) {
                $arr[1] = " '" . $arr[1] . "' ";
            }
            $where = " and `" . $arr[0] . "` = " . $arr[1] . " ";
        }
        $this->where .= $where;
        return $this;
    }

    //同字段 多条件
    public function whereIn($key, $arr)
    {
        $keys = $this->whereEntityMapping($key);
        $str = "";
        foreach ($arr as $key => $vo) {
            $str .= "'" . $vo . "',";
        }
        $str = substr($str, 0, strlen($str) - 1);
        $where = " and " . $keys . " in (" . $str . ") ";
        $this->where .= $where;
        return $this;
    }

    //分组
    public function groupBy($str)
    {
        $str = $this->whereEntityMapping($str);
        $this->groupBy = " group by " . $str . " ";
        return $this;
    }

    //排序
    public function orderBy($str, $px = "desc")
    {
        $str = $this->whereEntityMapping($str);
        $this->orderBy = " order by " . $str . " " . $px . " ";
        return $this;
    }

    //分页查询
    public function limit($page, $limit = null)
    {
        $this->limit = $limit ? " limit " . $page . "," . $limit : " limit " . $page;
        return $this;
    }

    //查询所有
    public function select($p = "*")
    {
        if ($p == "*") {
            if ($this->entityMapping()) {
                $p = $this->entityMapping();
            }
        } else {
            if ($this->prefixEntityMapping($p)) {
                $p = $this->prefixEntityMapping($p);
            }
        }
        if (empty($p)) {
            throw new \Exception("前缀不能为空！");
        }

        $sql = "select " . $p . " from " . $this->tableName . " where 1 " . $this->where . $this->groupBy . $this->orderBy . $this->limit;
        $data = array();
        $result = mysqli_query($this->conn, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($key)) {
                $data[$row[$key]] = $row;
            } else {
                $data[] = $row;
            }
        }
        return $data;
    }

    //查询一条结果
    public function count()
    {
        $sql = "select count(0) num from " . $this->tableName . " where 1 " . $this->where . " limit 1 ";
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $data = [];
        foreach ($row as $k => $v) {
            $data[$k] = $v;
        }
        return $data['num'];
    }

    //查询一条结果
    public function selectOne($p = "*")
    {
        if ($p == "*") {
            if ($this->entityMapping()) {
                $p = $this->entityMapping();
            }
        } else {
            if ($this->prefixEntityMapping($p)) {
                $p = $this->prefixEntityMapping($p);
            }
        }

        if (empty($p)) {
            throw new \Exception("前缀不能为空！");
        }
        $sql = "select " . $p . " from " . $this->tableName . " where 1 " . $this->where . $this->orderBy . " limit 1 ";
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if (!$row){
            return null;
        }
        $data = [];
        foreach ($row as $k => $v) {
            $data[$k] = $v;
        }
        return $data;
    }

    //执行一条sql 并返回执行结果条数
    public function query($sql)
    {
        mysqli_query($this->conn, $sql);
        $res = mysqli_affected_rows($this->conn);
        return $res;
    }

    //添加
    public function insert($arr)
    {
        $k = $v = [];
        foreach ($arr as $key => $vo) {
            //实体映射
            if (!empty($this->entityList)){
                if (array_key_exists($key, $this->entityList)) {
                    $key=$this->entityList[$key];
                }
            }
            $k[] = $key;
            $v[] = "'" . $vo . "'";
        }
        $k = implode(',', $k);
        $v = implode(',', $v);
        $sql = "insert into " . $this->tableName . " (" . $k . ") values (" . $v . ") ";
        return $this->query($sql);
    }

    //删除
    public function delete()
    {
        $sql = "delete from " . $this->tableName . " where 1 " . $this->where;
        return $this->query($sql);
    }

    //更新
    public function update($arr)
    {
        $v = [];
        foreach ($arr as $key => $vo) {
            //实体映射
            if (!empty($this->entityList)){
                if (array_key_exists($key, $this->entityList)) {
                    $key=$this->entityList[$key];
                }
            }
            $v[] = "`" . $key . "`='" . $vo . "'";
        }
        $v = implode(',', $v);
        $sql = "update " . $this->tableName . " set " . $v . " where 1 " . $this->where;
        return $this->query($sql);
    }

    //实体映射
    private function entityMapping()
    {
        if (!empty($this->entityList)) {
            $strArr = [];
            foreach ($this->entityList as $key => $vo) {
                $strArr[] = "`" . $vo . "` as `" . $key."`";
            }
            $strArr = implode(',', $strArr);
            return $strArr;
        } else {
            return false;
        }
    }

    //前缀实体映射
    private function prefixEntityMapping($p)
    {
        if (!empty($this->entityList)) {
            $arr = $this->entityList;
            $pArr = explode(',', $p);
            $kArr = $vArr = [];
            $str = "";
            foreach ($pArr as $vo) {
                //校验key是否存在 存在则取字段的值
                if (array_key_exists($vo, $arr)) {
                    $kArr[] = "`" . $arr[$vo] . "` as `" . $vo."`";
                }
                //校验值是否存在 存在则取字段的值
                if (in_array($vo, $arr)) {
                    $vArr[] = $vo;
                }
            }
            if (empty($kArr)) {
                return implode(',', $vArr);
            } else {
                return implode(',', $kArr);
            }
        } else {
            return "*";
        }
    }

    //条件筛选实体
    private function whereEntityMapping($str)
    {
        if (!empty($this->entityList)) {
            if (in_array($str, $this->entityList)) {
                return $str;
            }
            if (array_key_exists($str, $this->entityList)) {
                return $this->entityList[$str];
            }
        } else {
            return $str;
        }
    }
}