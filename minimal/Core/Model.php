<?php

namespace Minimal\Core;

use Medoo\Medoo;

abstract class Model
{
    private $db = null;
    protected $enum_info = [];

    protected function __construct()
    {
        if (is_null($this->db)) {
            $this->db = new Medoo(C('db'));
        }
    }

    final public static function create()
    {
        return new static();
    }

    public function exist($where)
    {
        $row = $this->getRow('id', $where);
        return empty($row) ? false : true;
    }

    protected abstract function getTableName();


    public function getList($columns = '*', $where = [], $offset = 0, $limit = -1, $order = null)
    {
        if ($offset > 0 || $limit > 0) $where['LIMIT'] = [$offset, $limit];
        if (!is_null($order)) $where['ORDER'] = $order;
        return $this->db->select($this->getTableName(), $this->parseColumns($columns), $where);
    }

    public function getRow($columns = '*', $where = [], $order = null)
    {
        if (!is_null($order)) $where['ORDER'] = $order;
        return $this->db->get($this->getTableName(), $this->parseColumns($columns), $where);
    }

    public function getMap($where = [], $key = 'id', $columns = '*')
    {
        $list = $this->getList($this->parseColumns($columns), $where);
        $map = [];
        foreach ($list as $v) {
            $map[$v[$key]] = $v;
        }
        return $map;
    }


    public function getColumn($column, $where)
    {
        $row = $this->db->get($this->getTableName(), $column, $where);
        return isset($row[$column]) ? $row[$column] : null;
    }

    public function insert($data)
    {
        $db = $this->db;
        $db->insert($this->getTableName(), $data);
        return $db->id();
    }

    public function batchInsert($data)
    {
        return $this->db->insert($this->getTableName(), $data);
    }

    public function update($data, $where)
    {
        if (empty($data) || empty($where)) return false;
        return $this->db->update($this->getTableName(), $data, $where);
    }

    public function delete($where)
    {
        if (empty($where)) return false;
        return $this->db->delete($this->getTableName(), $where);
    }

    public function setInc($where, $column, $n = 1)
    {
        return $this->db->update($this->getTableName(), [$column . '[+]' => $n], $where);
    }

    public function setDec($where, $column, $n = 1)
    {
        return $this->db->update($this->getTableName(), [$column . '[-]' => $n], $where);
    }

    public function count($where)
    {
        return $this->db->count($this->getTableName(), $where);
    }

    public function max($column, $where)
    {
        return $this->db->max($this->getTableName(), $column, $where);
    }

    public function min($column, $where)
    {
        return $this->db->min($this->getTableName(), $column, $where);
    }

    public function avg($column, $where)
    {
        return $this->db->avg($this->getTableName(), $column, $where);
    }

    public function sum($column, $where)
    {
        return $this->db->sum($this->getTableName(), $column, $where);
    }

    protected function db()
    {
        return $this->db;
    }

    private function parseColumns($columns)
    {
        if (is_string($columns) && $columns !== '*') $columns = explode(',', $columns);
        return $columns;
    }

    public function __destruct()
    {
        $log = Log::init();
        $log->info($this->db->log());
        $error = $this->db->error();
        if ($error[0] !== '00000') $log->error($error);
    }
}