<?php

namespace app\index\controller;

use think\Db;
use think\facade\Request;

class Index
{
    public function index()
    {
        echo '搭建成功';
    }

    //公共上传方法 切勿删除
    public function upload()
    {
        $conf = Db::name('config')->where(['config_type'=>'web_config'])->value('config_content');
        $file_url = json_decode($conf,true)['img_url'];

        $file = Request::file('file');
        if(!empty($file)){
            $filename = uploadsFile($file);

            $res['file_url'] =$file_url;
            $res['filename'] =$filename;
            apiReturn(20,'上传成功',$res);
        }
        apiReturn(202,'上传文件失败');
    }


}
