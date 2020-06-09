<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Db;


class Orders extends Base
{

    public function orders(){

        $page = input('page');
        $size = input('size');

        $total =Db::name('orders')->count();
        $list= Db::name('orders')->order('id desc')->page($page,$size)->select();

        foreach ($list as $key=>$val){
            $user = Db::name('users')->where('id', $val['uid'])->field('nickname,idnumber,avatar')->find();
            $list[$key]['users']['nickname'] = $user['nickname'];
            $list[$key]['users']['avatar'] = $user['avatar'];
            $list[$key]['users']['idnumber'] = $user['idnumber'];
            $list[$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
        }

        apiReturnList(200,'获取成功',$page,$size,$total,$list);
    }

    public function allRechargeConfig()
    {
        $list = Db::name('charge_config')->field('id,money as label')->select();
        apiReturn(200,'获取成功',$list);
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


    //搜索
    public function search()
    {



        $params = $this->request->post();

        $page = isset($params['page'])?$params['page']:1;
        $size = isset($params['size'])?$params['size']:10;


        $where = [];

        //搜索标题
        if(!empty($params['title'])){
            $where[] = ['id|order_sn|uid|transaction_no','like','%'.$params['title'].'%'];
        }

        //充值金额
        if(!empty($params['recharge_id'])){
            $where[] = ['recharge_id','in',$params['recharge_id']];
        }

        //支付方式
        if(!empty($params['pay_type'])){
            $where[] = ['pay_type','=',$params['pay_type']];

        }

        //状态
        if(!empty($params['status'])){
            if($params['status'] == 1){
                $where[] = ['status','=',1];
            }else{
                $where[] = ['status','=',0];
            }

        }

        //套装组件
        if(!empty($params['search_time'])){
            $where[] = ['create_time','between',[strtotime($params['search_time'][0]),strtotime($params['search_time'][1])]];

        }
        

        $total =Db::name('orders')->where($where)->count();
        $list= Db::name('orders')->where($where)->page($page,$size)->select();


        foreach ($list as $key=>$val){
            $user = Db::name('users')->where('id', $val['uid'])->field('nickname,idnumber,avatar')->find();
            $list[$key]['users']['nickname'] = $user['nickname'];
            $list[$key]['users']['avatar'] = $user['avatar'];
            $list[$key]['users']['idnumber'] = $user['idnumber'];
            $list[$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
        }
        apiReturnList(200,'获取成功',$page,$size,$total,$list);
    }

    //导出
    public function export()
    {



        $params = $this->request->post();


        $where = [];

        //搜索标题
        if(!empty($params['title'])){
            $where[] = ['id|order_sn|uid|transaction_no','like','%'.$params['title'].'%'];
        }

        //充值金额
        if(!empty($params['recharge_id'])){
            $where[] = ['recharge_id','in',$params['recharge_id']];
        }

        //支付方式
        if(!empty($params['pay_type'])){
            $where[] = ['pay_type','=',$params['pay_type']];

        }

        //状态
        if(!empty($params['status'])){
            if($params['status'] == 1){
                $where[] = ['status','=',1];
            }else{
                $where[] = ['status','=',0];
            }

        }

        //套装组件
        if(!empty($params['search_time'])){
            $where[] = ['create_time','between',[strtotime($params['search_time'][0]),strtotime($params['search_time'][1])]];

        }

        $field = 'id,order_sn,transaction_no,money,pay_type,status,overtime,uid';
        $list= Db::name('orders')->where($where)->field($field)->select();


        foreach ($list as $key=>$val){
            $list[$key]['transaction_no'] = !empty($val['transaction_no']) ? $val['transaction_no'] : '';
            $list[$key]['overtime'] = !empty($val['overtime']) ? $val['transaction_no'] : '---';
            if($val['pay_type'] == 1){
                $list[$key]['pay_type'] = '微信';
            }else{
                $list[$key]['pay_type'] = '支付宝';
            }
            if($val['status'] == 1){
                $list[$key]['status'] = '已支付';
            }else{
                $list[$key]['status'] = '未支付';
            }
        }
        apiReturn(200,'获取成功',$list);
    }



}

