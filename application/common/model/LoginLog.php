<?php

namespace app\common\model;

use think\Model;

class LoginLog extends Model
{

    /**
     * 获取所有 文章
     * @param $limit
     * @param $rows
     * @param int $where
     * @return array
     */
    public function getList($limit, $rows, $where = 0)
    {
        $count = $this->where($where)->count();
        $data = $this->limit($limit, $rows)->where($where)->field('update_time',true)->order('id desc')->select();
        return [
            "total" => $count,
            "rows" => $data
        ];
    }
}
