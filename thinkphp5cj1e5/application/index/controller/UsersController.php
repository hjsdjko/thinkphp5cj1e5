<?php
namespace app\index\controller;

use http\Params;
use think\Cache;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class UsersController extends CommonController
{
	public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
        header('Access-Control-Allow-Headers:Origin,Content-Type,Accept,token,X-Requested-With,device');
    }
	public $columData = [
        'id','addtime','username','password','role'
    ];
    /**
     * 登录接口
     * POST
     * */
    public function login(){
        $name = trim(input('param.username'));
        $password = trim(input('param.password'));
        $result = Db::table('users')->where(['username' => $name, 'password' => $password])->find();
        if ($result){
            $token_array = [
                "iat" => time(), //签发时间
                "exp" => time()+7200, //token 过期时间
				'id' => $result['id'],
				'isAdmin' => 1,
                'tablename'=> 'users',//表名
                "success" => $result, //记录的uid的信息，如果有其它信息，可以再添加数组的键值对
            ];
            $tokens = base64_encode(json_encode($token_array));
            $data = ['code' => 0, 'token' => $tokens];
            Cache::set($tokens,$result['id']);
            Cache::set(md5($result['id']),$result['username']);
            return json($data);
        }
        return json(['code' => 500,'msg'=>"登陆失败，账号或密码错误。"]);
    }
    /**
     * 退出
     * POST
     */
    public function logout(){
        $token = $this->token();
        Cache::pull($token);
        return json(['code'=>0,'msg'=>'退出成功']);
    }
    /**
     * 获取session的接口
     * GET
     */
    public function session(){
        if (Cache::get($this->token())==false) return json(["code"=> 500,'msg'=>"您还没有登陆。"]);
        $data = json_decode(base64_decode($this->token()),true);
        $arrayData = $data['success'];
        return json(['code'=>0,'data'=>$arrayData]);
    }
    /**
     * 找回密码 重置为123456
     **/
    public function resetPass(){
        $username = input('param.username');
        $count = DB::table('users')->where(['username' => $username])->count();
        if($count==0) return json(['code'=>500,'mas'=>"账号不存在"]);
        $result = Db::table('users')->where(['username' => $username])->update(['password' => '123456']);
        return json(['code'=>0,'mas'=>"密码已重置为：123456"]);
    }
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
        $count = Db::table('users')->where($where)->count();
        // 取整函数(ceil,floor,round)
        $page_count = ceil($count/$limt);//页数
        $result = Db::table('users')->where($where)->limit(($page-1)*10,$limt)->order($sort." ".$order)->select();
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
        $postData = input('param.');
        if (!empty($postData)&&!is_array($postData)){
             $postData = json_decode($postData,true);
        }
		$v = array();
        foreach ($postData as $key => $value){
            if (in_array($key, $this->columData)){
                $v[$key] = $value;
            }
        }
        $result = Db::table('users')->insert($v);
        if (!$result) return json(['code'=>500,'msg'=>"新增失败"]);
        return json(['code'=>0]);
    }
    /**
     * 更新接口 post
     * 包含主键
     */
    public function update(){
        $postData = input('param.');
        $token = $this->token();
        if (Cache::get($token)==false) return json(['code'=>500,'msg'=>"您还没有登陆。"]);
        $v = $where = array();
        foreach ($postData as $key => $value){
            if (in_array($key, $this->columData)){
                if ($key == "id"){
                    $where[$key] = $value;
                }
                $v[$key] = $value;
            }
        }
        $result = Db::table('users')->where($where)->update($v);
        if (!$result) return json(['code'=>500]);
        return json(['code'=>0]);
    }
    /**
     * 删除接口 post
     * $id id
     */
    public function delete(){
        $ids = input('param.');
        $result = Db::table('users')->delete($ids);
        return json(["code"=> 0]);
    }
    /**
     * 详情接口info ,后台接口
     * get
     * $id id
     * */
    public function info($id=false){
        $token = $this->token();
        if (Cache::get($token)==false) return json(["code"=> 500,'msg'=>"您还没有登陆。"]);
        $where = ['id'=>$id];
        $result = Db::table('users')->where($where)->find();
        return json(["code"=> 0,'data' => $result]);
    }
}
