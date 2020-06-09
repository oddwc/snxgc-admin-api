<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Db;


class Users extends Base
{

    public function index(){

        $page = input('page');
        $size = input('size');

        $total =Db::name('users')->count();
        $list= Db::name('users')->order('id desc')->page($page,$size)->select();

        foreach ($list as $key=>$val){
            //钱包
            $wallet = Db::name('users_wallet')->where('uid', $val['id'])->field('coin,diamond')->find();
            $list[$key]['wallet']['coin'] = $wallet['coin'];
            $list[$key]['wallet']['diamond'] = $wallet['diamond'];
            if (!empty($val['qq_id']) && empty($val['wx_id'])){
                $list[$key]['login_type'] = 1;//qq
            }elseif (empty($val['qq_id']) && !empty($val['wx_id'])){
                $list[$key]['login_type'] = 2;//wx
            }elseif (!empty($val['qq_id']) && !empty($val['wx_id'])){
                $list[$key]['login_type'] = 3;//wx and qq
            }else{
                $list[$key]['login_type'] = 4;//空无
            }
        }

        apiReturnList(200,'获取成功',$page,$size,$total,$list);
    }


    public function edit(){
        if($this->request->isPost()){
            $params = $this->request->post();
            if(empty($params['title'])) { apiReturn(202,'标题不能为空'); }
            if(empty($params['image'])) { apiReturn(202,'图片不能为空'); }
            if(empty($params['url'])) { apiReturn(202,'跳转地址不能为空'); }
            $params['image'] = substr($params['image'],strlen($this->web_config['upload_url']));

            $res = Db::name('users')->update($params);
            if ($res){
                apiReturn(200,'编辑成功');
            }else{
                apiReturn(204,'编辑失败');
            }
        }else{
            apiReturn(405,'非法请求');
        }
    }


    public function search(){

        $page = input('page')?:1;
        $size = input('size')?:10;

        if(input('title')){
            $where[] = ['id|nickname|idnumber','like','%'.input('title').'%'];
        }


        $total =Db::name('users')->where($where)->count();
        $list= Db::name('users')->where($where)->page($page,$size)->select();

        foreach ($list as $key=>$val){
            if (!empty($val['qq_id']) && empty($val['wx_id'])){
                $list[$key]['login_type'] = 1;//qq
            }elseif (empty($val['qq_id']) && !empty($val['wx_id'])){
                $list[$key]['login_type'] = 2;//wx
            }elseif (!empty($val['qq_id']) && !empty($val['wx_id'])){
                $list[$key]['login_type'] = 3;//wx and qq
            }else{
                $list[$key]['login_type'] = 4;//空无
            }
        }

        apiReturnList(200,'获取成功',$page,$size,$total,$list);
    }


    public function clearAttires(){
        $id = $this->request->put('id');

        try {
            $defualt_attire = Db::name('default_attire')->where('status', 1)->column('id');
            $defualt_attire = implode(',', $defualt_attire);
            Db::name('users_make_up')->where('uid', $id)->setField('attires', $defualt_attire);
            apiReturn(200,'清除装扮成功');
        }catch (\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    public function feedback(){

        $page = input('page');
        $size = input('size');

        $total =Db::name('feedback')->count();
        $list= Db::name('feedback')->page($page,$size)->select();

        apiReturnList(200,'获取成功',$page,$size,$total,$list);
    }

    public function feedbackDel(){
        $id = $this->request->delete('id');

        try{
            $res = Db::name('feedback')->delete($id);
            if ($res){
                apiReturn(200,'删除成功');
            }else{
                apiReturn(204,'删除失败');
            }
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }



}

