<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Session;

class ApiController extends CommonController
{
	public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
        header('Access-Control-Allow-Headers:Origin,Content-Type,Accept,token,X-Requested-With,device');
    }
    /**
     * 文件上传接口
     * POST
     * $type=1 get参数,配置文件上传需要
     * */
    public function upload($type=false){
        header('Content-Type:application/json');
        $file = request()->file('file');
        if($file){
            $info = $file->rule('date')->move(ROOT_PATH . 'upload');
            if($info){
                if ($type==1){
                    $data = Db::table('config')->where(['name'=>'faceFile'])->update(['value'=>$info->getFilename()]);
                    if($data==0){
                    $data = Db::table('config')->insert(['name'=>'faceFile','value'=>$info->getFilename()]);
                    }
                    if (!$data){
                        return json(['code'=>500]);
                    }
                }
                return json(['code'=>0,'file'=> $info->getFilename(),'msg'=>"上传成功！"]);
            }else{
                // 上传失败获取错误信息
                return json(['code'=>500,'msg'=>$file->getError()]);
            }
        }
    }
    /**
     * 下载文件接口
     * get
     * */
    public function download(){
        $get = input('get.fileName');
        header('location:http://'.$_SERVER['HTTP_HOST'].'/upload/'.$get);
    }
    /**
     * 获取某表的某个字段列表接口
     * $tableName  表名
     * $columnName  列名
     * $level 等级 主用于联动查询
     * $parent 列值 主用于联动查询
     * */
    public function option($tableName=false,$columnName=false,$level=false,$parent=false){
        $where =[];
        if ($level!=false||$parent!=false){
            $where = ['level'=>$level,'parent'=>$parent];
        }
        $result = Db::table($tableName)->where($where)->column($columnName);
        return json(['code'=>0,'data'=>$result]);
    }
    /**
     * 根据option字段值获取某表的单行记录接口
     * 和查询相似
     * $tableName  表名
     * $columnName  列名
     * $columnValue  列值
     * */
    public function follow($tableName=false,$columnName=false,$columnValue=false){
        $result = Db::table($tableName)->where([$columnName => $columnValue])->find();
        return json(['code' => 0,'data' => $result]);
    }
    /**
     * 根据主键id修改table表的sfsh状态接口
     * $tableName  表名
     * $id  id
     * $sfsh  审核
     **/
    public function sh(){
        $tableName = input('post.tableName');
        $id = input('post.id');
        $sfsh = input('post.sfsh');
        if ($sfsh=="是"){$sh = "否";}
        if ($sfsh=="否"){$sh = "是";}
        $result = Db::table($tableName)->where(['id' => $id])->update(['sfsh' => $sh]);
    }
    /**
     * 获取需要提醒的记录数接口
     * $tableName  表名
     * $columnName  列名
     * $type  类型（1表示数字比较提醒，2表示日期比较提醒）
     * $remindStart  remindStart<=columnName 满足条件提醒,当比较日期时，该值表示天数
     * $remindEnd  columnName<=remindEnd 满足条件提醒,当比较日期时，该值表示天数
     **/
    public function remind($tableName,$columnName,$type,$remindstart = false,$remindend = false){
        if ($type==1){
            $remindstart ? ($map[$columnName] = ['>=', $remindstart]) : '';
            $remindend ? ($map[$columnName] = ['<=', $remindend]) :'';
            $result = Db::table($tableName)->where($map)->count();
        }else{
            $remindstart ? ($map[$columnName] = ['>=', date("Y-m-d",strtotime("+".$remindstart." day"))]) :'';
            $remindend ? ($map[$columnName] = ['<=', date("Y-m-d",strtotime("+".$remindend." day"))]) : '';
            $result = Db::table($tableName)->where($map)->count();
        }
        return json(['code'=>0,'count'=>$result]);
    }
    /**
     * 计算规则接口
     * $tableName  表名
     * $columnName  列名
     * */
    public function cal($tableName,$columnName){
        $sum = Db::table($tableName)->sum($columnName);
        $max = Db::table($tableName)->max($columnName);
        $min = Db::table($tableName)->min($columnName);
        $avg = Db::table($tableName)->avg($columnName);
        return json(['code' => 0,'data' => ["min" => $min, "avg" => $avg, "max" => $max, "sum" => $sum]]);
    }
    /**
     * 类别统计接口
     * $tableName  表名
     * $columnName  列名
     */
    public function group($tableName,$columnName){
        $list = Db::table($tableName)->field($columnName.',count('.$columnName.') as total')->group($columnName)->order('total desc')->select();
        return json(['code'=>0,'data'=>$list]);
    }
    /**
     * 按值统计接口
     * tableName	String	表名
     * xColumnName String  列名
     * yColumnName String  列名
     **/
    public function value($tableName,$xColumnName,$yColumnName){
        //$data = Db::table($tableName)->field($xColumnName,$yColumnName.' total')->order($yColumnName.' desc')->select();
        $data = Db::query("select ".$xColumnName.", sum(".$yColumnName.") total from ".$tableName." group by ".$xColumnName);
        return json(['code'=>0,'data'=>$data]);
    }

        
}
