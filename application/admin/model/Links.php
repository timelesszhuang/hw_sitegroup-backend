<?php

namespace app\admin\model;

use think\Model;

class Links extends Model
{
    //只读字段
    protected $readonly=["node_id"];
    /**
     * 获取所有数据
     * @param $limit
     * @param $rows
     * @param $where
     * @return array
     * @author guozhen
     */
    public function getAll($limit, $rows, $where)
    {
        $count = $this->where($where)->count();
        $data = $this->limit($limit, $rows)->where($where)->field('update_time',true)->order('id','desc')->select();
        return [
            "total" => $count,
            "rows" => $data
        ];

    }
}
