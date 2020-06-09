<?php
namespace app\index\logic;

use think\Db;

class Logic
{


    /**
     * 添加分类
     * @param $params
     * @return bool
     */
    public static function addCate($params){
        $insert['title'] = $params['title'];
        $insert['pid'] = $params['pid'];
        $insert['listorder'] = $params['listorder'];
        $insert['display'] = $params['display'];
        $insert['create_time'] = time();
        $res = Db::name('cate')->insert($insert);

        if ($res){
            return true;
        }else{
            return false;
        }
    }


}
