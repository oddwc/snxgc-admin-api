<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Db;
use think\facade\Request;


class System extends Base
{
    public function systemInfo(){
        $version = Db::query('SELECT VERSION() AS ver');
        $config  = [
            'url'             => $_SERVER['HTTP_HOST'],
            'document_root'   => $_SERVER['DOCUMENT_ROOT'],
            'server_os'       => PHP_OS,
            'server_port'     => $_SERVER['SERVER_PORT'],
            'server_ip'       => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'],
            'server_soft'     => $_SERVER['SERVER_SOFTWARE'],
            'php_version'     => PHP_VERSION,
            'mysql_version'   => $version[0]['ver'],
            'max_upload_size' => ini_get('upload_max_filesize'),
        ];

        apiReturn(200,'获取成功',$config);
    }

    public function rechargeStatistics()
    {
        $pay = Db::name('orders')->where('status', 1)->count();
        $all = Db::name('orders')->count();
        $nopay = $all-$pay;

        $data['pay'] =$pay;
        $data['nopay'] =$nopay;
        apiReturn(200,'获取成功',$data);
    }

    public function statistics(){
        $data['users'] = Db::name('users')->count();
        $data['orders'] = Db::name('orders')->count();
        $data['pay_users'] = Db::name('orders')->where('status',1)->distinct(true)->count();
        $data['all_pay_orders'] = Db::name('orders')->where('status',1)->sum('money');
        apiReturn(200,'获取成功',$data);
    }
    
    public function config()
    {
       $config = Db::name('config')->where('config_type','web_config')->value('config_content');
       $config = json_decode($config,true);
       $config['logo'] = $config['logo']?get_file($config['logo']):'';
       apiReturn(200,'获取成功',$config);
    }

    public function editConfig(){
        $params = $this->request->put();

        try{
            if(file_exists(upload_url().$params['logo'])){
                $newpath = move_file($params['logo'],'/admin/logo');
                $params['logo'] = $newpath;
                Db::name('config')->where('config_type','web_config')->setField('config_content',json_encode($params,JSON_UNESCAPED_SLASHES));
            }else{
                $params['logo'] = str_replace($params['img_url'],'',$params['logo']);
                Db::name('config')->where('config_type','web_config')->setField('config_content',json_encode($params,JSON_UNESCAPED_SLASHES));
            }
            apiReturn(200,'编辑成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }


    }

    public function pay()
    {
        $config = Db::name('config')->where('config_type','pay_config')->value('config_content');
        $config = json_decode($config,true);
        apiReturn(200,'获取成功',$config);
    }

    public function editPay()
    {
        $params = $this->request->put();
        try{
            Db::name('config')->where('config_type','pay_config')->setField('config_content',json_encode($params,JSON_UNESCAPED_SLASHES));
            apiReturn(200,'编辑成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }

    }

    public function recharge()
    {
        $list = Db::name('charge_config')->select();
        foreach ($list as $key=>$val){
            $list[$key]['is_test'] = $val['is_test']?true:false;
            $list[$key]['is_show'] = $val['is_show']?true:false;
        }
        apiReturn(200,'获取成功',$list);
    }

    public function rechargeAdd()
    {
        $params = $this->request->post();
        if(empty($params['image'])){ apiReturn(202,'请上传图片'); }

        try{
            $insertId = Db::name('charge_config')->insertGetId($params);
            if(!empty($params['image'])){
                //同时移动文件到指定目录
                $newpath = move_file($params['image'],'/recharge_config/'.$insertId);
                Db::name('charge_config')->where('id',$insertId)->setField('image',$newpath);
            }
            apiReturn(200,'添加成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    public function rechargeEdit()
    {
        $params = Request::put();


        if(empty($params['image'])) {
            apiReturn(202,'请上传图片');
        }else{
            $image = Db::name('charge_config')->where('id',$params['id'])->value('image');
            if($params['image'] == $image){
                unset($params['image']);
            }
        }

        try{

            if(isset($params['image'])){
                //更新并同时移动文件到指定目录
                $params['image'] = move_file($params['image'],'/recharge_config/'.$params['id']);;
            }

            Db::name('charge_config')->where('id',$params['id'])->update($params);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    public function rechargeDel()
    {
        $id = input('id');
        $file= Db::name('charge_config')->where('id',$id)->field('image')->find();
        try{
            //删除
            Db::name('charge_config')->where('id',$id)->delete();
            //附件不为空则删除

            if(!empty($file['image'])){
                //文件是否存在
                $absfile = upload_url().$file['image'];
                if(file_exists($absfile)){
                    unlink($absfile);
                }else{
                    apiReturn(200,'文件不存在，无法删除相应文件');
                }
            }

        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //切换测试档位
    public function changeTestStatus()
    {

        $parmas = Request::put();

        if ($parmas['is_test'] == true) {
            $display = 1;
        } else {
            $display = 0;
        }

        try {
            Db::name('charge_config')->where('id', $parmas['id'])->setField('is_test', $display);
        } catch (\Exception $e) {
            apiReturn(202, $e->getMessage());
        }
    }

    //更改充值配置状态
    public function changeRechargeConfigStatus()
    {

        $parmas = Request::put();

        if ($parmas['is_show'] == true) {
            $display = 1;
        } else {
            $display = 0;
        }
        try {
            Db::name('charge_config')->where('id', $parmas['id'])->setField('is_show', $display);
        } catch (\Exception $e) {
            apiReturn(202, $e->getMessage());
        }
    }


    public function exchange()
    {
        $list = Db::name('exchange_config')->select();

        apiReturn(200,'获取成功',$list);
    }

    public function exchangeAdd()
    {
        $params = Request::post();

        try{
            Db::name('exchange_config')->insert($params);
            apiReturn(200,'添加成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    public function exchangeEdit()
    {
        $params = Request::put();


        try{

            Db::name('exchange_config')->where('id',$params['id'])->update($params);
            apiReturn(200,'编辑成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    public function exchangeDel()
    {
        $id = input('id');
        try{
            //删除
            Db::name('exchange_config')->where('id',$id)->delete();
            apiReturn(200,'删除成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    public function sign()
    {
        $list = Db::name('sign_config')->select();

        apiReturn(200,'获取成功',$list);
    }

    public function signEdit()
    {
        $params = Request::put();


        try{

            Db::name('sign_config')->where('id',$params['id'])->update($params);
            apiReturn(200,'编辑成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

}

