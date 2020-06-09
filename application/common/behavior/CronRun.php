<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/16
 * Time: 16:37
 */

namespace app\common\behavior;


class CronRun
{
    public function appInit(){
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: token, Origin, X-Requested-With, Content-Type, Accept, Authorization");
        header('Access-Control-Allow-Methods: POST,GET,PUT,DELETE');

        if(request()->isOptions()){
            exit();
        }
    }
}