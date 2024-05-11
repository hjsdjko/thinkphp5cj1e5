<?php
namespace app\index\controller;

use http\Params;
use think\Cache;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class YonghuController extends CommonController
{
	public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
        header('Access-Control-Allow-Headers:Origin,Content-Type,Accept,token,X-Requested-With,device');
    }
	public $columData = [
        'id','addtime'
                ,'zhanghao'
                ,'mima'
                ,'yonghu'
                ,'xingbie'
                ,'shouji'
                ,'youxiang'
                ,'touxiang'
                ,'money'
            ];
        /**
     * 登录接口
     * POST
     * */
    public function login(){
        $name = trim(input('param.username'));
        $password = trim(input('param.password'));
                        $result = Db::table('yonghu')->where(['zhanghao' => $name, 'mima' => $password])->find();
                                                                                                                                                        if ($result){
            $uName = "zhanghao";
            $token_array = [
                "iat" => time(), //签发时间
                "exp" => time()+7200, //token 过期时间
				'id' => $result['id'],
                'tablename'=> 'yonghu',//表名
                'columData' => $this->columData,
								'isAdmin' => 0,
				                "success" => $result, //记录的uid的信息，如果有其它信息，可以再添加数组的键值对
                "loginUserColumn" => "zhanghao",
                "uName" => $result[$uName]
            ];
            $tokens = base64_encode(json_encode($token_array));
            $data = ['code' => 0, 'token' => $tokens];
            Cache::set($tokens,$result['id']);
                        			$columnNames = "zhanghao";
            Cache::set(md5($result['id']."+10086"),$result[$columnNames]);
                                                                                                                                                                                                            return json($data);
        }
        return json(['code' => 500,'msg'=>"登录失败，账号或密码错误。"]);
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
        if (Cache::get($this->token())==false) return json(["code"=> 500,'msg'=>"您还没有登录。"]);
        $data = json_decode(base64_decode($this->token()),true);
		$dbname = $data['tablename'];
		$uid = $data['id'];
		$arrayData = Db::table($dbname)->where(['id' => $uid])->find();
        return json(['code'=>0,'data'=>$arrayData]);
    }
    /**
     * 注册 post
     **/
    public function register(){
        $postData = input('param.');
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
                			$columnNames = "zhanghao";
        $count = DB::table('yonghu')->where(['zhanghao'=>$postData[$columnNames]])->count();
                                                                                                                                		        if($count>0) return json(['code'=>500,'msg'=>'用户名已存在']);
                $postData['id'] = time();
        $result = Db::table('yonghu')->insert($postData);
                if (!$result) return json(['code'=>500,'msg'=>'注册失败']);
        return json(['code'=>0]);
    }
    /**
     * 找回密码 重置为123456
     **/
    public function resetPass(){
        $username = input('param.username');
                        $count = DB::table('yonghu')->where(['zhanghao'=>$username])->count();
        if($count==0) return json(['code'=>500,'mas'=>"账号不存在"]);
        $result = Db::table('yonghu')->where(['zhanghao'=>$username])->update(['mima'=>'123456']);
                                                                                                                                        		        return json(['code'=>0,'mas'=>"密码已重置为：123456"]);
    }
    
    /**
     * 分页接口 GET
     * $page   当前页
     * 否  每页记录的长度
     * $sort   排序字段
     * $order  升序（默认asc）或者降序（desc）
     * */
    public function page(){
        $token = $this->token();
        if (Cache::get($token) == false) return json(['code'=>500,'msg'=>"您还没有登录。"]);
        $userid = Cache::get($token);
        $base = json_decode(base64_decode($token,true),true);
        $tabnames = $base['tablename'];
        $getData = input('get.');
        $where = array();
        $betweenColumn = '';
        $start = array();
        $end = array();
        foreach ($getData as $k => $val){
			if(in_array($k, $this->columData)){
                if ($val != ''){
                    $where[$k] = ['like',$val];
                }
			}
            if(in_array(substr($k, 0, strlen($k) - 5), $this->columData)){
                if ($val != ''){
                    $betweenColumn = substr($k, 0, strlen($k) - 5);
                    $start = ['egt', $val];
                }
			} else if(in_array(substr($k, 0, strlen($k) - 3), $this->columData)){
                if ($val != ''){
                    $betweenColumn = substr($k, 0, strlen($k) - 3);
                    $end = ['elt', $val];
                }
			}
        }
        if (!empty($start) && !empty($end)) {
            $where[$betweenColumn] = array($start, $end);
        }
        $page = isset($_GET['page'])?input('get.page'):"1";
        $limt = isset($_GET['limit'])?input('get.limit'):"10";
        $sort = isset($_GET['sort'])?input('get.sort'):"id";
        $order = isset($_GET['order'])?input('get.order'):"asc";
                                $data = json_decode(base64_decode($token),true);
        if ($data['isAdmin']!=1){
            $colum = "zhanghao";
            $columData = $data['columData'] ?? [];
            if (in_array($colum, $columData)){
                $where[$colum] = Cache::get(md5($userid."+10086"));//判断条件
            }
        }
                                                                                                                                        		                                                $count = Db::table('yonghu')->where($where)->count();
        // 取整函数(ceil,floor,round)
        $page_count = ceil($count/$limt);//页数

                $result = Db::table('yonghu')->where($where)->limit(($page-1)*$limt,$limt)->order($sort." ".$order)->select();
        
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
     * 否  每页记录的长度
     * $sort   排序字段
     * $order  升序（默认asc）或者降序（desc）
     * */
    public function lists(){
        $where = array();//判断条件
                        $getData = input('get.');
        foreach ($getData as $k => $val){
            if(in_array($k, $this->columData)){
                $where[$k] = ['like',$val];
            }
        }
                        $token = $this->token();
        $data = json_decode(base64_decode($token),true);
        if ($data['isAdmin']!=1){
            $colum = "zhanghao";
            $columData = $data['columData'] ?? [];
            if (in_array($colum, $columData)){
                $where = [$colum => Cache::get(md5($userid."+10086"))];//判断条件
            }
        }
                                                                                                                                        		        $page = isset($_GET['page'])?input('get.page'):"1";
        $limt = isset($_GET['limit'])?input('get.limit'):"10";
        $sort = isset($_GET['sort'])?input('get.sort'):"id";
        $order = isset($_GET['order'])?input('get.order'):"asc";

                                		        $count = Db::table('yonghu')->where($where)->count();
        // 取整函数(ceil,floor,round)
        $page_count = ceil($count/$limt);//页数
        $result = Db::table('yonghu')->where($where)->limit(($page-1)*$limt,$limt)->order($sort." ".$order)->select();
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
        if ($session == false) return json(['code'=>401,'msg'=>"您还没有登录。"]);
                $postData = input('post.');
        if (!empty($postData)&&!is_array($postData)){
             $postData = json_decode($postData,true);
        }
                        		$v = array();
        foreach ($postData as $key => $value){
            if (in_array($key, $this->columData)){
                if (!empty($value) || $value == 0){ 
                    if($key == 'clicktime') {
                        $v[$key] = date('Y-m-d h:i:s', time());
                    } else {
                        $v[$key] = $value;
                    }
                }
            }
        }
		$postData = $v;
                $postData['id'] = time();
                $result = Db::table('yonghu')->insert($postData);
                return json(['code'=>0]);
    }
    /**
     * 保存接口 post
     *
     */
    public function add(){
		                        $postData = input('post.');
        if (!empty($postData)&&!is_array($postData)){
             $postData = json_decode($postData,true);
        }
                		$v = array();
        foreach ($postData as $key => $value){
            if (in_array($key, $this->columData)){
                if (!empty($value) || $value == 0){
                    $v[$key] = $value;
                }
            }
        }
		$postData = $v;
        $result = Db::table('yonghu')->insert($postData);
        return json(['code'=>0]);
    }
    /**
     * 更新接口 post
     * 包含主键
     */
    public function update(){
        $postData = input('post.');
        $token = $this->token();
        if (Cache::get($token)==false) return json(['code'=>500,'msg'=>"您还没有登录。"]);
        $where = array();
                $v = array();
        foreach ($postData as $key => $value){
            if (in_array($key, $this->columData)){
                if ($value == '') {
                    $v[$key] = null;
                    continue;
                }
                if ($key == "id"){
                    $where[$key] = $value;
                }
                $v[$key] = $value;
            }
        }
        Db::table('yonghu')->where($where)->update($v);
        return json(['code'=>0]);
    }
    /**
     * 删除接口 post
     * $id id
     */
    public function delete(){
        $ids = input('post.');
        $result = Db::table('yonghu')->delete($ids);
        return json(["code"=> 0]);
    }
    /**
     * 详情接口info ,后台接口
     * get
     * $id id
     * */
    public function info($id=false,$name=false){
        $token = $this->token();
        if (Cache::get($token)==false) return json(["code"=> 500,'msg'=>"您还没有登录。"]);
        $where = ['id'=>$id];
                        if($name!=false){
            $where = ['name'=>$name];
        }
        $result = Db::table('yonghu')->where($where)->find();
        return json(["code"=> 0,'data' => $result]);
    }
    /**
     * 详情接口detail ,后台接口
     * get
     * $id id
     * */
    public function detail($id=false,$name=false){
                $where = ['id'=>$id];
                        if($name!=false){
            $where = ['name'=>$name];
        }
        $result = Db::table('yonghu')->where($where)->find();
        return json(["code"=> 0,'data' => $result]);
    }
                
    
    /**
     * 获取需要提醒的记录数接口
     * $columnName  列名
     * $type  类型（1表示数字比较提醒，2表示日期比较提醒）
     * $remindStart  remindStart<=columnName 满足条件提醒,当比较日期时，该值表示天数
     * $remindEnd  columnName<=remindEnd 满足条件提醒,当比较日期时，该值表示天数
     **/
    public function remind($columnName,$type,$remindstart = false,$remindend = false){
        if ($type==1){
            $remindstart ? ($map[$columnName] = ['>=', $remindstart]) : '';
            $remindend ? ($map[$columnName] = ['<=', $remindend]) :'';
            $result = Db::table('yonghu')->where($map)->count();
        }else{
            $remindstart ? ($map[$columnName] = ['>=', date("Y-m-d",strtotime("+".$remindstart." day"))]) :'';
            $remindend ? ($map[$columnName] = ['<=', date("Y-m-d",strtotime("+".$remindend." day"))]) : '';
            $result = Db::table('yonghu')->where($map)->count();
        }
        return json(['code'=>0,'count'=>$result]);
    }
}
