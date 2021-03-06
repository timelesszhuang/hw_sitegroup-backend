<?php

namespace app\admin\model;

use think\Db;
use think\Model;

class WeixinKeyword extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'sc_weixin_keyword';

    protected $connection = [
        // 数据库类型
        'type' => 'mysql',
        // 数据库连接DSN配置
        'dsn' => '',
        // 服务器地址
        'hostname' => 'rdsfjnifbfjnifbo.mysql.rds.aliyuncs.com',
//        // 数据库名
        'database' => 'scrapy',
        // 数据库用户名
        'username' => 'scrapy',
        // 数据库密码
        'password' => '201671Zhuang',
        // 数据库连接端口
        'hostport' => '',
        // 数据库连接参数
        'params' => [],
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => 'sc_',
    ];

    /**
     * 获取所有关键字
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getKeyword($limit, $rows, $where = 0)
    {
        $count = $this->where($where)->count();
        $data = Db::connect($this->connection)->table("sc_weixin_keyword")->where($where)->order('id desc,status')->limit($limit, $rows)->select();
        return [
            "total" => $count,
            "rows" => $data
        ];
    }



    /**
     * 修改关键词
     * @param $id
     * @param $name
     * @return false|int
     */
    public function editKeyword($id, $name)
    {
        return Db::connect($this->connection)->table("sc_weixin_keyword")->where(["id" => $id])->update(["name" => $name]);
    }

    /**
     * 获取一条数据
     * @param $id
     * @return null|static
     */
    public function getOne($id)
    {
        $key = self::get($id);
        return $key;
    }

    /**
     * 当期关键字未审核爬取
     * @param $id
     * @return int|string
     */
    public function stopStatus($id)
    {
        return Db::connect($this->connection)->table("sc_weixin_keyword")->where(["id" => $id])->update(["status" => 20]);
    }

    /**
     * 关键字已审核爬取
     * @param $id
     * @return int|string
     */
    public function startStatus($id)
    {
        return Db::connect($this->connection)->table("sc_weixin_keyword")->where(["id" => $id])->update(["status" => 10]);
    }

    /**
     * 开启爬取某个关键字
     * @param $id
     * @return int|string
     */
    public function startScrapy($id)
    {
        return Db::connect($this->connection)->table("sc_weixin_keyword")->where(["id" => $id])->update(["scrapystatus" => 10]);
    }

    /**
     * 停止爬取某个关键字
     * @param $id
     * @return int|string
     */
    public function stopScrapy($id)
    {
        return Db::connect($this->connection)->table("sc_weixin_keyword")->where(["id" => $id])->update(["scrapystatus" => 20]);
    }

    /**
     * 获取列表格式的数据
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getKeyList()
    {
        $where = [];
        $where['status'] = 10;
        $arr = [];
        $data = Db::connect($this->connection)->table("sc_weixin_keyword")->where($where)->field("id,name as text,scrapystatus,type_name")->order('type_name')->select();
        foreach ($data as $k => $v) {
               $arr[$v['type_name']][]= $v;
        }
       return $arr;
    }

    public function getlist(){
        $where['status'] = 10;
        $data = Db::connect($this->connection)->table("sc_weixin_keyword")->where($where)->field("id,name as text,scrapystatus,type_name")->order('type_name')->select();
        return $data;
    }


}
