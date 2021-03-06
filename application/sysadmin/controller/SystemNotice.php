<?php

namespace app\sysadmin\controller;

use app\common\controller\Common;
use think\Request;
use app\sysadmin\model\SystemNotice as Sys;
use think\Validate;

class SystemNotice extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $request = $this->getLimit();
        $title = $this->request->get('title');
        $where = [];
        $data = (new Sys())->getList($request["limit"], $request["rows"], $where);
        return $this->resultArray('', '', $data);
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
        $rule = [
            ["title", "require", "请输入标题"],
            ["content", "require", "请输入内容"],
        ];
        $validate = new Validate($rule);
        $data = $request->post();
        if (!$validate->check($data)) {
            return $this->resultArray($validate->getError(), "failed");
        }
        if(isset($data["node_ids"]) && !empty($data["node_ids"])){
            $ids=implode(",",$data["node_ids"]);
            $data["node_ids"]=",".$ids.",";
        }else{
            $nodeCollection=\app\sysadmin\model\Node::all();
            if(!empty($nodeCollection)){
                $nodeArr=collection($nodeCollection)->toArray();
                $nodeStr=implode(",",array_column($nodeArr,"id"));
                $data["node_ids"]=",".$nodeStr.",";
            }
        }
        if (!Sys::create($data)) {
            return $this->resultArray("添加失败", "failed");
        }
        return $this->resultArray("添加成功");
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $find=Sys::where(["id"=>$id])->field("create_time,update_time", true)->find();
        if(!empty($find["node_ids"])){
            $find["node_ids"]=trim($find["node_ids"],",");
        }
        return $this->resultArray("","",$find);
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
        $rule = [
            ["title", "require", "请输入标题"],
            ["content", "require", "请输入内容"],
        ];
        $validate = new Validate($rule);
        $data = $request->post();
        if (!$validate->check($data)) {
            return $this->resultArray($validate->getError(), "failed");
        }
        if(isset($data["node_ids"]) && !empty($data["node_ids"])){
            $ids=implode(",",$data["node_ids"]);
            $data["node_ids"]=",".$ids.",";
        }else{
            $nodeCollection=\app\sysadmin\model\Node::all();
            if(!empty($nodeCollection)){
                $nodeArr=collection($nodeCollection)->toArray();
                $nodeStr=implode(",",array_column($nodeArr,"id"));
                $data["node_ids"]=",".$nodeStr.",";
            }
        }
        if (!(new Sys)->save($data, ["id" => $id])) {
            return $this->resultArray('修改失败', 'failed');
        }
        return $this->resultArray('修改成功');
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
}
