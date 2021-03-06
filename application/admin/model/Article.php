<?php
/**
 * Created by PhpStorm.
 * User: qiangbi
 * Date: 17-4-26
 * Time: 下午2:25
 */

namespace app\admin\model;

use think\Model;

class Article extends Model
{
    //只读字段
    protected $readonly = ["node_id"];

    /**
     * 初始化函数
     * @author guozhen
     */
    public static function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        // 文章阅读数量随机生成 添加图片缩略图
        Article::event("before_write", function ($article) {
            $rule = '/<img[\s\S]*?src\s*=\s*[\"|\'](.*?)[\"|\'][\s\S]*?>/';
            if (isset($article->content)) {
//                self::replaceBase64($article->content);
                preg_match($rule, $article->content, $matches);
                if (!empty($matches)) {
                    $imgs=explode('<img',$matches[0]);
                    //给图片添加alt属性
                    $lastImg="<img alt="."'$article->title'".$imgs[1];
                    $article->thumbnails = $lastImg;
                    // 如果是base64的图片
                    if (preg_match('/(data:\s*image\/(\w+);base64,)/',$lastImg,$result)){
                        $type = $result[2];
                        $article->thumbnails_name=md5(uniqid(rand(), true)).".$type";
                    }
                }
            }
            $article->readcount = rand(100, 10000);
        });
    }

    /**
     * 获取所有 文章
     * @param $limit
     * @param $rows
     * @param int $where
     * @return array
     */
    public function getArticle($limit, $rows, $where = 0)
    {
        $count = $this->where($where)->count();
        $data = $this->limit($limit, $rows)->where($where)->field('content,summary,update_time,readcount',true)->order('id desc')->select();
        return [
            "total" => $count,
            "rows" => $data
        ];
    }

    /**
     * 获取所有 文章
     * @param $limit
     * @param $rows
     * @param int $where
     * @return array
     */
    public function getArticletdk($limit, $rows, $w = '',$wheresite='',$wheretype_id='')
    {
        $count = $this->where('articletype_id', 'in', $wheretype_id)->where($w)->whereOr($wheresite)->count();
        $articledata = $this->limit($limit, $rows)->where('articletype_id', 'in', $wheretype_id)->where($w)->whereOr($wheresite)->field('id,title,create_time')->order('id desc')->select();
        return [
            "total" => $count,
            "rows" => $articledata
        ];
    }


}