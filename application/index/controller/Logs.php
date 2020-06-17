<?php


namespace app\index\controller;


use app\common\controller\Base;
use think\Db;

class Logs extends Base
{

    public function logs()
    {

        $page = input('page');
        $size = input('size');

        $total =Db::name('logs')->count();
        $list= Db::name('logs')->order('id desc')->page($page,$size)->select();
        foreach ($list as $key=>$val){
            $list[$key]['params'] = unserialize($val['params']);
            $list[$key]['req_time'] = date('Y-m-d H:i:s',$val['req_time']);
        }

        apiReturnList(200,'获取成功',$page,$size,$total,$list);
    }



    public function del()
    {

        try {
            Db::name('logs')->delete(true);
            apiReturn(200,'清除成功');
        }catch (\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }


}