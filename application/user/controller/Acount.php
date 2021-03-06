<?php

namespace app\user\controller;

use app\common\model\BrowseRecord;
use think\Request;
use app\common\controller\Common;
use think\Session;
use think\Validate;
class Acount extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $node_id=$this->getSiteSession('login_site');
        $param=$this->request->get();
        $starttime = 0;
        $stoptime = time();
        $where = [
            'node_id'=>$node_id["node_id"],
            'site_id'=>$this->getSiteSession('website')["id"]
        ];
        if (isset($param["time"])) {
            list($start_time, $stop_time) = $param['time'];
            $starttime = (!empty(intval($start_time))) ? strtotime($start_time) : $starttime;
            $stoptime = (!empty(intval($stop_time))) ? strtotime($stop_time) : $stoptime;
        }
        $where["create_time"] = ['between', [$starttime, $stoptime]];
        $browse=new BrowseRecord();
        $arr = $browse->field('engine,count(id) as keyCount')->where($where)->group('engine')->order("keyCount","desc")->select();
        $arrcount = $browse->where($where)->count();
        $temp=[];
        foreach ($arr as $k=>$v){
            $temp[]=["value"=>round($v['keyCount']/$arrcount*100,2),"name"=>$v['engine']];
        }
        return $this->resultArray('','',$temp);

    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }

    /**
     * 小网站用户存储站点信息
     * @return array
     */
    public function siteInfo()
    {

    }

    /**
     * 设置session 全部都放进去 以后有用
     * @param $site_id
     * @param $site_name
     */
    public function setSession($site_info)
    {

    }
}
