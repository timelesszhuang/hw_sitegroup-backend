<?php

namespace app\admin\controller;

use app\admin\model\Useragent;
use app\common\controller\Common;
use app\common\model\BrowseRecord;
use think\Db;
use think\Request;
use think\Session;
use think\Validate;
use app\common\traits\Obtrait;
use Closure;

/**
 * 站点 最小的节点相关操作
 * @author xingzhuang
 * 2017年5月17
 */
class Site extends Common
{
    use Obtrait;
    public $all_count = [];

    /**
     * 显示资源列表
     * @return \think\Response
     * @author xingzhuang
     */
    public function index()
    {
        $request = $this->getLimit();
        $site_name = $this->request->get('site_name');
        $site_type = $this->request->get('site_type_id');
        $url = $this->request->get('url');
        $where = [];
        if (!empty($site_name)) {
            $where["site_name"] = ["like", "%$site_name%"];
        }
        if (!empty($site_type)) {
            $where["site_type"] =$site_type;
        }
        if (!empty($url)) {
            $where["url"] = ["like", "%$url%"];
        }
        $user = $this->getSessionUser();
        $where["node_id"] = $user["user_node_id"];
        $data = (new \app\admin\model\Site())->getAll($request["limit"], $request["rows"], $where);
        return $this->resultArray('', '', $data);
    }

    /**
     * 显示创建资源表单页.
     * @return \think\Response
     * @author xingzhuang
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $rule = [
            ['site_name', 'require', '请填写网站名称'],
            ['menu', 'require', "请选择菜单"],
            ['template_id', 'require', '请选择模板'],
            ['support_hotline', 'require', '请填写电话号码'],
            ['domain_id', 'require', '请选择域名'],
            ['domain', 'require', '请选择域名'],
            ['site_type', 'require', '请选择网站类型'],
            ['user_id', "require", "请选择用户"],
            ["user_name", "require", "请选择用户名"],
            ["site_type_name", "require", "请填写网站类型名称"],
            ["keyword_ids", "require", "请填写关键字"],
            ["url", "require", "请输入url"]
        ];
        $validate = new Validate($rule);
        $data = $this->request->post();
        if (!$validate->check($data)) {
            return $this->resultArray($validate->getError(), 'failed');
        }
        if (!$this->searchHttp($data["url"])) {
            $data["url"] = "http://" . $data["url"];
        }
        if (!empty($data["link_id"])) {
            $data["link_id"] = "," . implode(",", $data["link_id"]) . ",";
        }
        $data["node_id"] = $this->getSessionUser()['user_node_id'];
        $data["menu"] = "," . implode(",", $data["menu"]) . ",";
        $errorKey = $this->checkHasBC($data["keyword_ids"]);
        //验证关键字是否在C类中存在
        if (empty($errorKey)) {
            $data["keyword_ids"] = "," . implode(",", $data["keyword_ids"]) . ",";
        } else {
            return $this->resultArray($errorKey . " 缺少B、C类关键词", 'failed');
        }
        //公共代码
        if (!empty($data["public_code"])) {
            $data["public_code"] = implode(",", $data["public_code"]);
        }
        if (!\app\admin\model\Site::create($data)) {
            return $this->resultArray('添加失败', 'failed');
        }
        return $this->resultArray('添加成功');
    }

    /**
     * 根据传入的关键字数组 判断C类中是否有  如果返回空即验证通过 否则不通过
     * @param $arr
     * @return string
     */
    public function checkHasBC($arr)
    {
        $temp = '';
        foreach ($arr as $item) {
            $keyB = \app\admin\model\Keyword::where(["path" => ["like", "%,$item,%"], "tag" => "C"])->find();
            if (is_null($keyB)) {
                $getKey = \app\admin\model\Keyword::get($item);
                $temp .= $getKey->name . ",";
            }
        }
        return $temp;
    }

    /**
     * 显示指定的资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function read($id)
    {
        return $this->getread((new \app\admin\model\Site), $id);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $rule = [
            ['site_name', 'require', '请填写网站名称'],
            ['menu', 'require', "请选择菜单"],
            ['template_id', 'require', '请选择模板'],
            ['support_hotline', 'require', '请填写电话号码'],
            ['domain_id', 'require', '请选择域名'],
            ['domain', 'require', '请选择域名'],
            ['site_type', 'require', '请选择网站类型'],
            ["site_type_name", "require", "请填写网站类型名称"],
            ["keyword_ids", "require", "请填写关键字"],
            ["url", "require", "请输入url"]
        ];
        $validate = new Validate($rule);
        $data = $this->request->put();
        $user = $this->getSessionUser();
        $where = [
            "id" => $id,
            "node_id" => $user["user_node_id"]
        ];
        if (!empty($data["link_id"])) {
            $data["link_id"] = "," . implode(",", $data["link_id"]) . ",";
        }
        //公共代码
        if (!empty($data["public_code"])) {
            $data["public_code"] = implode(",", $data["public_code"]);
        }
        $data["menu"] = "," . implode(",", $data["menu"]) . ",";
        $data["keyword_ids"] = "," . implode(",", $data["keyword_ids"]) . ",";
        if (!(new \app\admin\model\Site)->save($data, $where)) {
            return $this->resultArray('修改失败', 'failed');
        }
        return $this->resultArray('修改成功');
    }

    /**
     * 删除指定资源 模板暂时不支持删除操作
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id)
    {

    }


    /**
     * 传输模板文件到站点服务器
     * @access public
     */
    public function uploadTemplateFile($dest, $path,$type,$id)
    {
        $dest = $dest . '/index.php/filemanage/uploadFile';
        $this->sendFile(ROOT_PATH . "public/" . $path, $dest, $type,$id);
    }

    /**
     * 修改为主站
     * @param $id
     * @return array
     */
    public function setMainSite($id)
    {
        $main_site = $this->request->post("main_site");
        if (empty($main_site)) {
            return $this->resultArray('请选择是否是主站', 'failed');
        }
        if($main_site!=10){
            Db::name('site')
            ->where('main_site',20)
            ->setField('main_site', '10');
        }
        $data = ["main_site" => $main_site];


        return $this->publicUpdate((new \app\admin\model\Site()), $data, $id);
    }

    /**
     * 修改ftp信息
     * @param $id
     * @return array
     */
    public function saveFtp($id)
    {
        $user = (new Common())->getSessionUser();
        $where = [
            "id" => $id,
            "node_id" => $user["user_node_id"],
        ];
        $data = $this->request->put();
        if (!\app\admin\model\Site::where($where)->update($data)) {
            return $this->resultArray('修改失败', 'failed');
        }
        return $this->resultArray('修改成功');
    }

    /**
     * 获取手机网站
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function mobileSite()
    {
        $user = $this->getSessionUser();
        $where = [
            "is_mobile" => 20,
            "node_id" => $user["user_node_id"],
        ];
        $data = (new \app\admin\model\Site)->where($where)->field("id,site_name as text")->select();
        return $this->resultArray('', '', $data);
    }

    /**
     * 中断前台操作,继续执行后台请求
     * @param $id 模板id
     * @param $type
     * @return array
     */
    public function ignoreFrontend($template_id,$site_id,$type)
    {
        $this->open_start("正在发送模板,请等待..");
        $user = $this->getSessionUser();
        $nid = $user["user_node_id"];
        $where = [
            "id" => $site_id,
            "node_id" => $nid
        ];
        $send = function () use ($template_id,$site_id,$type,$where) {
            $site = \app\admin\model\Site::where($where)->find();
            switch($type){
                    case "activity":
                        $template=\app\admin\model\Activity::where(["id"=>$template_id])->field("id,code_path as path")->find();
                        if(!$template){
                            exit("未找到模板");
                        }
                        $id=$template->id;
                        break;
                    case "template":
                        $template = \app\admin\model\Template::get($site["template_id"]);
                        if(!$template){
                            exit("未找到模板");
                        }
                        $id=$template->id;
                        break;
                }

            return [$template,$site,$type,$id];

        };
        $this->runClosuse($send);
    }

    /**
     * 执行发送模板
     * @param Closure $closure
     */
    public function runClosuse(Closure $closure)
    {
        list($template,$site,$type,$id)=$closure();
        $upload = $this->uploadTemplateFile($site->url, $template->path,$type,$id);
    }

    /**
     * 获取
     * @return array
     */
    public function getSites()
    {
        $field = "id,site_name,url";

        $SiteData = $this->getList((new \app\admin\model\Site), $field);
        $Site = $SiteData['data'];
        $arr = [];
        foreach ($Site as $k=>$v){
         $v['text']= $v['site_name'].'['.$v['url'].']';
            $arr[$k] = $v;
        }
        return $this->resultArray('','',$Site);


    }

    /**
     * 统一站点发送get请求接口
     * @param $id
     * @param $name
     */
    public function siteGetCurl($id, $name)
    {
        $func = function () use ($id) {
            $user = $this->getSessionUser();
            $nid = $user["user_node_id"];
            $where = [
                "id" => $id,
                "node_id" => $nid
            ];
            $site = \app\admin\model\Site::where($where)->find();
            if (is_null($site)) {
                return $this->resultArray('发送失败,无此记录!', 'failed');
            }
            return $site->url;
        };
        return $this->callGetClosure($func, $name);
    }

    /**
     * 统一站点get调用接口
     * @param Closure $closure
     * @param $name
     */
    public function callGetClosure(closure $closure, $name)
    {
        $url = $closure();
        $switchUrl = function () use ($url, $name) {
            $NewUrl = '';
            $msg = '';
            switch ($name) {
                case "aKeyGeneration":
                    $msg = "正在一键生成...";
                    $NewUrl = $url . "/allstatic";
                    break;
                case "generatIndex":
                    $msg = "正在生成首页...";
                    $NewUrl = $url . "/indexstatic";
                    break;
                case "generatArticle":
                    $msg = "正在生成文章页...";
                    $NewUrl = $url . "/articlestatic";
                    break;
                case "generatMenu":
                    $msg = "正在生成栏目...";
                    $NewUrl = $url . "/menustatic";
                    break;
                case "clearCache":
                    $msg = "正在清除...";
                    $NewUrl = $url . "/clearCache";
                    break;
            }
            return [$NewUrl, $msg];
        };
        $this->getSwitchUrl($switchUrl);
    }

    /**
     * 根据name获取指定的url和msg
     * @param $name
     * @return array
     */
    public function getSwitchUrl(Closure $closure)
    {
        list($url, $msg) = $closure();
        //断开前台请求
        $this->open_start($msg);
        //发送curl get请求
        $this->curl_get($url);
    }

    /**
     * 获取活动模板
     * @param $id
     */
    public function getActivily($id)
    {
        $arr=[];
        foreach ($this->getSiteInfo($id) as $item) {
            $arr[]=$item;
        }
        return $this->resultArray('', '', $arr);

    }

    /**
     * 遍历site和activily
     * @param $id
     * @return \Generator
     */
    public function getSiteInfo($id)
    {
        $user = $this->getSessionUser();
        $where = [
            'node_id' => $user["user_node_id"],
        ];
        $activily = \app\admin\model\Activity::where($where)->field("id,name")->select();
        $site = \app\admin\model\Site::get($id);
        foreach ($activily as $item) {
            yield $this->foreachActivily($item, $site->sync_id);
        }
    }

    /**
     * 检测是否已同步
     * @param $item
     * @param string $sync_id
     */
    public function foreachActivily($item, $sync_id = '')
    {
        $arr='';
        if (!empty($sync_id)) {
            if (strpos($sync_id, "," . $item->id . ",")!==false) {
                $arr=["id"=>$item->id,"name"=>$item->name,"issync"=>"已同步","sync"=>"重新发送"];
            }else{
                $arr=["id"=>$item->id,"name"=>$item->name,"issync"=>"未同步","sync"=>"同步"];
            }
        }else{
            $arr=["id"=>$item->id,"name"=>$item->name,"issync"=>"未同步","sync"=>"同步"];
        }
        return $arr;
    }

    /**
     * 站点统计
     * @return array
     */
    public function SiteCount()
    {
        $user = $this->getSessionUser();
        $where = [
            'node_id' => $user["user_node_id"],
        ];
        $site = new \app\admin\model\Site();
        $arr = $site->field('site_type_name,count(id) as nameCount')->where($where)->group('site_type_name')->order("nameCount", "desc")->select();
//        $arrcount = $site->where($where)->count();
        $temp = [];
        $valueArr = [];
        $nameArr = [];
        foreach ($arr as $k => $v) {
            $valueArr[] = $v['nameCount'];
            $nameArr[] = $v['site_type_name'];
        }
        $temp = ["value" => $valueArr, "name" => $nameArr];
        return $this->resultArray('', '', $temp);
    }

    public function enginecount()
    {
        $user = $this->getSessionUser();
        $starttime = time() - 86400 * 9;
        $stoptime = time();
//        $time=strtotime(date("Y-m-d 0:0:0"))-86400*9;
        $where = [];
        $where['node_id'] = $user["user_node_id"];

        $where['create_time'] = ['between', [$starttime, $stoptime]];

        $userAgent = Db::name("useragent")->where($where)->field("engine,create_time")->select();
        $Agent = [];
        $Engine = [];
        foreach ($userAgent as $v) {
            $engine = $v['engine'];
            if (!in_array($engine, $Engine)) {
                array_push($Engine, $engine);
            }
            $date = date('m-d', $v['create_time']);
            if (!array_key_exists($engine, $Agent)) {
                $Agent[$engine] = [];
            }
            if (array_key_exists($date, $Agent[$engine])) {
                $Agent[$engine][$date] += 1;
            } else {
                $Agent[$engine][$date] = 1;
            }
        }
        $date_diff = $this->get_date_diff($starttime, $stoptime);
        foreach ($Engine as $engine) {
            foreach ($date_diff as $date) {
                ksort($Agent[$engine]);
                if (!array_key_exists($date, $Agent[$engine])) {
                    $Agent[$engine][$date] = 0;
                }
            }
        }
        array_walk($Agent, [$this, "formatter"]);
        if (empty($userAgent)) {
            $this->all_count[0] = [
                "name" => "sougou",
                "data" => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                "type" => "line",
                "stack" => '总量',
            ];
            $this->all_count[1] = [
                "name" => "baidu",
                "data" => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                "type" => "line",
                "stack" => '总量',
            ];
        }
        $temp = ["time" => $date_diff, "type" => $this->all_count];
        return $this->resultArray('', '', $temp);
    }

    /**
     *获取某段时间内的时间显示
     * @param $stime
     * @param $etime
     * @return array
     */
    public function get_date_diff($stime, $etime)
    {
        //将起始时间转换成 2014-05 年月的格式
        $st = strtotime(date("Y-m-d", $stime));
        $et = strtotime(date("Y-m-d", $etime));
        $no_time = false;
        if ($et > time()) {
            $et = time();
            $no_time = true;
        }
        $arr = array();
        while ($st) {
            array_push($arr, date("m-d", $st));
            $st = strtotime("+1 day", $st);
            if ($st >= $et) {
                break;
            }
        }
        if (!$no_time) {
            array_push($arr, date("m-d", $st));
        }
        //返回格式 array("2014-01","2014-02",.....)的时间戳形式
        return $arr;
    }

    public function formatter($value, $key)
    {
        $this->all_count[] = [
            "name" => $key,
            "data" => array_values($value),
            "type" => "line",
            "stack" => '总量',
        ];

    }

    public function commontype()
    {
        $menu = new Menu();
        $menudata = $menu->getMenu();
        $template = new Template();
        $templatedata = $template->getTemplate();
        $sitetype = new Sitetype();
        $sitetypedata = $sitetype->getSiteType();
        $contactway = new Contactway();
        $contactwaydata = $contactway->getContactway();
        $domain = new Domain();
        $domaindata = $domain->getDomain();
        $usertype = new Siteuser();
        $usertypedata = $usertype->getUsers();
        $keyword = new Keyword();
        $keyworddata =  $keyword->index();
        $link = new Links();
        $linkdata = $link->getLinks();
        $mobilesite = new Site();
        $mobilesitedata = $mobilesite->mobileSite();
        $code = new Code();
        $codedata = $code->getCodes();
        $data = [
            'menutype'=>$menudata['data'],
            'temptype'=>$templatedata['data'],
            'sitetype'=>$sitetypedata['data'],
            'hotline'=>$contactwaydata['data'],
            'domainlist'=>$domaindata['data'],
            'userlist'=>$usertypedata['data'],
            'keyword'=>$keyworddata['data'],
            'link'=>$linkdata['data'],
            'mobileSite'=>$mobilesitedata['data'],
            'code'=>$codedata['data'],

        ];
       return $this->resultArray('','',$data);
    }



}
