<?php


namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\Cache;
class CommonController extends Controller
{
	public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
        header('Access-Control-Allow-Headers:Origin,Content-Type,Accept,token,X-Requested-With,device');
    }
    /**
     * 获取头部token
     * 需要用才调用
     **/
    public function token(){
        $token = Request::instance()->header('token');
        if (empty($token) || !isset($token) || Cache::get($token) == false) return false;
        return $token;
    }
    /**
     * 验证token,并获取保存的session值
     * 需要用才调用
     */
    public function checkToken(){
        $token = $this->token();
        $uid = Cache::get($token);
        if ($uid == false){
            return 500;
        }
        return $uid;
    }
}