<?php
namespace app\index\controller;

use http\Params;
use think\Cache;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class ConfigController extends CommonController
{
	public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
        header('Access-Control-Allow-Headers:Origin,Content-Type,Accept,token,X-Requested-With,device');
    }
    public $columData = [
        'id','name','value'
    ];
	/**
     * 分页接口 GET
     * $page   当前页
     * $limit  每页记录的长度
     * $sort   排序字段
     * $order  升序（默认asc）或者降序（desc）
     * */
    public function page(){
        $token = $this->token();
        if (Cache::get($token) == false) return json(['code'=>500,'msg'=>"您还没有登陆。"]);
        $userid = Cache::get($token);
        $base = json_decode(base64_decode($token,true),true);
        $tabnames = $base['tablename'];
        $page = isset($_GET['page'])?input('get.page'):"1";
        $limt = isset($_GET['limit'])?input('get.limit'):"10";
        $sort = isset($_GET['sort'])?input('get.sort'):"id";
        $order = isset($_GET['order'])?input('get.order'):"asc";
        $where = [];//判断条件
        $count = Db::table('config')->where($where)->count();
        // 取整函数(ceil,floor,round)
        $page_count = ceil($count/$limt);//页数
        $result = Db::table('config')->where($where)->limit(($page-1)*10,$limt)->order($sort." ".$order)->select();
        return json([
            'code' => 0,
            'data' => [
                "total" => $count,
                "pageSize" => $limt,
                "totalPage" => $page_count,
                "currPage" => $page,
                "list" => $result
            ]
        ]);
    }
	 /**
     * 分页接口 GET
     * $page   当前页
     * $limit  每页记录的长度
     * $sort   排序字段
     * $order  升序（默认asc）或者降序（desc）
     * */
    public function lists(){
        $where = [];//判断条件
        $page = isset($_GET['page'])?input('get.page'):"1";
        $limt = isset($_GET['limit'])?input('get.limit'):"10";
        $sort = isset($_GET['sort'])?input('get.sort'):"id";
        $order = isset($_GET['order'])?input('get.order'):"asc";
        $count = Db::table('config')->where($where)->count();
        // 取整函数(ceil,floor,round)
        $page_count = ceil($count/$limt);//页数
        $result = Db::table('config')->where($where)->limit(($page-1)*10,$limt)->order($sort." ".$order)->select();
        return json([
            'code' => 0,
            'data' => [
                "total" => $count,
                "pageSize" => $limt,
                "totalPage" => $page_count,
                "currPage" => $page,
                "list" => $result
            ]
        ]);
    }
    /**
     * 保存接口 post
     *
     */
    public function save(){
        $token = $this->token();
        $session = Cache::get($token);
        if ($session == false) return json(['code'=>500,'msg'=>"您还没有登陆。"]);
        $postData = input('post.');
        if (!empty($postData)&&!is_array($postData)){
            $postData = json_decode($postData,true);
        }
        $v = array();
        foreach ($postData as $key => $value){
            if (in_array($key, $this->columData)){
                if (!empty($value) || $value === 0){
                    $v[$key] = $value;
                }
            }
        }
        $postData = $v;
        $result = Db::table('config')->insert($postData);
        if (!$result) return json(['code'=>500,'msg'=>"新增失败"]);
        return json(['code'=>0]);
    }

    /**
     * 更新接口 post
     * 包含主键
     */
    public function update(){
        $postData = input('post.');
        $token = $this->token();
        if (Cache::get($token)==false) return json(['code'=>500,'msg'=>"您还没有登陆。"]);
        $where = array();
        $v = array();
        foreach ($postData as $key => $value){
            if (in_array($key, $this->columData)){
                if ($key == "id"){
                    $where[$key] = $value;
                }
                $v[$key] = $value;
            }
        }
        $result = Db::table('config')->where($where)->update($v);

        if (!$result) return json(['code'=>500]);
        return json(['code'=>0]);
    }
    /**
     * 删除接口 post
     * $id id
     */
    public function delete(){
        $ids = input('post.');
        $result = Db::table('config')->delete($ids);
        return json(["code"=> 0]);
    }
    /**
     * 详情接口info ,后台接口
     * get
     * $id id
     * */
    public function info($id=false,$name=false){
        $token = $this->token();
        if (Cache::get($token)==false) return json(["code"=> 500,'msg'=>"您还没有登陆。"]);
        $where = ['id'=>$id];
        if($name!=false){
            $where = ['name'=>$name];
        }
        $result = Db::table('config')->where($where)->find();
        return json(["code"=> 0,'data' => $result]);
    }
}
