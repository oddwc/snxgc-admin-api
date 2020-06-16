<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Db;


class Attire extends Base
{

    //列表
    public function attire()
    {

        $page = input('page');
        $size = input('size');

        $total =Db::name('attire')->where('is_suit',0)->count();
        $list= Db::name('attire')->where('is_suit',0)->order('id desc')->page($page,$size)->select();

        foreach ($list as $key => $val) {
            $list[$key]['parent'] = Db::name('attire_cate')->where('id',$val['cate_id'])->value('name');
            $list[$key]['status'] = $val['status']?true:false;
            if($val['pid'] > 0){
                $list[$key]['suit'] = Db::name('attire')->where('id',$val['pid'])->value('title');
            }

            $default_attire = Db::name('default_attire')->where('attire_id', $val['id'])->where('status', 1)->find();
            if($default_attire){
                $list[$key]['default_attire'] = true;
            }else{
                $list[$key]['default_attire'] = false;
            }
            $attire_attribute = Db::name('attire_attribute')->where('attire_id', $val['id'])->count();
            $list[$key]['hasChildren'] = $attire_attribute > 0 ? true : false;
        }
        apiReturnList(200,'获取成功',$page,$size,$total,$list);
    }

    //排序
    public function sort()
    {
        $params = $this->request->put();
        try{
            Db::name('attire')->where('id',$params['id'])->setField('listorder',$params['listorder']);
            apiReturn(200,'排序修改成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }
    //获取分类优先级
    public function getLevel()
    {
        $params = $this->request->get();
        $level = Db::name('attire_cate')->where('id', $params['id'])->value('level');
        apiReturn(200,'获取成功',$level);
    }
//    //更改优先级
//    public function changeLevel()
//    {
//        $params = $this->request->put();
//        try{
//            Db::name('attire')->where('id',$params['id'])->setField('level',$params['level']);
//            apiReturn(200,'优先级修改成功');
//        }catch(\Exception $e){
//            apiReturn(202,$e->getMessage());
//        }
//    }

    //批量修改单件对应分类的层级
    public function setCateLevel()
    {
        $params = $this->request->put();

        try{
            if($params['cate_id'] == 0){
                //循环查出对应分类的层级，再修改
                $cates = Db::name('attire_cate')->where('pid',1)->field('id,level')->select();

                foreach ($cates as $key=>$val){
                    Db::name('attire')->where('cate_id',$val['id'])->setField('level',$val['level']);
                }

            }else{
                Db::name('attire')->where('cate_id',$params['cate_id'])->setField('level',$params['level']);
            }

            apiReturn(200,'批量修改成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }
    //搜索
    public function search()
    {

   

        $params = $this->request->post();
        $page = isset($params['page'])?$params['page']:1;
        $size = isset($params['size'])?$params['size']:10;


        $where = [];
        $where[] = ['is_suit','=',0];

        //搜索标题
        if(!empty($params['title'])){
            $where[] = ['id|title','like','%'.$params['title'].'%'];
        }

        //币种
        if(!empty($params['type'])){
            $where[] = ['type','=',$params['type']];
        }

        //分类
        if(!empty($params['cate_id'])){
            $where[] = ['cate_id','in',$params['cate_id']];

        }
        //套装组件
        if(!empty($params['is_suit'])){
            if($params['is_suit'] == 1){
                $where[] = ['pid','>',0];
            }elseif($params['is_suit'] == -1){
                $where[] = ['pid','=',0];
            }

        }

        //默认装饰
        if(!empty($params['default_attire'])){
            $all_default_attire = Db::name('default_attire')->where('status',1)->column('attire_id');
            if($params['default_attire'] == 1){
                $where[] = ['id','in',$all_default_attire];
            }elseif($params['default_attire'] == -1){
                $where[] = ['id','not in',$all_default_attire];

            }

        }

        //状态
        if(!empty($params['status'])){
            if($params['status'] == 1){
                $where[] = ['status','=',1];
            }else{
                $where[] = ['status','=',0];
            }

        }


        $total =Db::name('attire')->where($where)->count();
        $list= Db::name('attire')->where($where)->page($page,$size)->select();


        foreach ($list as $key => $val) {
            $list[$key]['parent'] = Db::name('attire_cate')->where('id',$val['cate_id'])->value('name');
            $list[$key]['status'] = $val['status']?true:false;
            if($val['pid'] > 0){
                $list[$key]['suit'] = Db::name('attire')->where('id',$val['pid'])->value('title');
            }

            $default_attire = Db::name('default_attire')->where('attire_id', $val['id'])->where('status', 1)->find();
            if($default_attire){
                $list[$key]['default_attire'] = true;
            }else{
                $list[$key]['default_attire'] = false;
            }
            $attire_attribute = Db::name('attire_attribute')->where('attire_id', $val['id'])->count();
            $list[$key]['hasChildren'] = $attire_attribute > 0 ? true : false;
        }
        apiReturnList(200,'获取成功',$page,$size,$total,$list);
    }

    //获取装饰分类
    public function getAttireCate()
    {
        $list = Db::name('attire_cate')->where('pid',1)->where('id','>',2)->where('status',1)->field('id,pid,name as label')->select();
        apiReturn(200,'获取成功',$list);
    }

    //获取套装
    public function getAllAttire()
    {
        $list = Db::name('attire')->where('is_suit', 1)->field('id,pid,title as label')->select();
        apiReturn(200,'获取成功',$list);
    }

    //添加
    public function add(){
        $params = $this->request->post();

        if(empty($params['title'])) { apiReturn(202,'请填写标题'); }
        if(empty($params['thumb'])) { apiReturn(202,'请上传缩略图'); }

        $params['is_suit'] = 0;
        $params['create_time'] = now();
        try{
            $insertId = Db::name('attire')->insertGetId($params);
            //同时移动文件到指定目录
            $update['thumb'] = move_file($params['thumb'],'/attire/'.$insertId);
            if(!empty($params['image'])){
                $update['image'] = move_file($params['image'],'/attire/'.$insertId);
            }

            Db::name('attire')->where('id',$insertId)->update($update);

            if($params['pid'] > 0){ //如果是套装，
                Db::name('attire')->where('id', $params['pid'])->setInc('cost', $params['cost']);
            }
            apiReturn(200,'添加成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //编辑
    public function edit(){
        $params = $this->request->put();
        $params['is_suit'] = 0;

        if(empty($params['title'])) { apiReturn(202,'请填写标题'); }

        if(empty($params['thumb'])) {
            apiReturn(202,'请上传缩略图');
        }else{
            $thumb = Db::name('attire')->where('id',$params['id'])->value('thumb');
            if($params['thumb'] == $thumb){
                unset($params['thumb']);
            }
        }
        if(empty($params['image'])) {
            apiReturn(202,'请上传图片');
        }else{
            $image = Db::name('attire')->where('id',$params['id'])->value('image');
            if($params['image'] == $image){
                unset($params['image']);
            }
        }

        try{
            if(isset($params['thumb'])){
                //更新并同时移动文件到指定目录
                $params['thumb'] = move_file($params['thumb'],'/attire/'.$params['id']);;
            }
            if(isset($params['image'])){
                //更新并同时移动文件到指定目录
                $params['image'] = move_file($params['image'],'/attire/'.$params['id']);;
            }

            Db::name('attire')->where('id',$params['id'])->update($params);
            if($params['pid'] > 0){
                $cost = Db::name('attire')->where('pid',$params['pid'])->sum('cost');
                Db::name('attire')->where('id', $params['pid'])->setField('cost', $cost);
            }
            apiReturn(200,'编辑成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //删除分类（带附件）
    public function del(){
        $id = $this->request->delete('id');
        $file= Db::name('attire')->where('id',$id)->field('thumb,image')->find();
        try{
            //删除分类
            Db::name('attire')->where('id',$id)->delete();
            //附件不为空则删除
            if(!empty($file['thumb'])){
                //文件是否存在
                $absfile = upload_url().$file['thumb'];
                if(file_exists($absfile)){
                    unlink($absfile);
                }else{
                    apiReturn(200,'文件不存在，无法删除相应文件');
                }
            }
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

    //更改状态
    public function changeStatus()
    {

        $parmas = $this->request->put();

        if ($parmas['status'] == true) {
            $display = 1;
        } else {
            $display = 0;
        }
        try {
            Db::name('attire')->where('id', $parmas['id'])->setField('status', $display);
        } catch (\Exception $e) {
            apiReturn(202, $e->getMessage());
        }
    }


    //设置默认的装饰
    public function setDefaultAttire()
    {

        $parmas = $this->request->put();

        $default_attire = Db::name('default_attire')->where('attire_id', $parmas['id'])->find();
        $suit_id = Db::name('attire')->where('id', $parmas['id'])->value('pid');

        try {
            if(empty($default_attire)){
                Db::name('default_attire')->insert(['suit_id'=>$suit_id,'attire_id'=>$parmas['id'],'status'=>1]);
            }elseif ($default_attire['status'] == 0){
                Db::name('default_attire')->where('attire_id',$parmas['id'])->setField('status',1);
            }else{
                Db::name('default_attire')->where('attire_id',$parmas['id'])->setField('status',0);
            }
            apiReturn(200,'设置成功');
        } catch (\Exception $e) {
            apiReturn(202, $e->getMessage());
        }
    }
    //清除所有已设置的默认单件装饰
    public function clearSingleAttire()
    {

        try {
            Db::name('default_attire')->where('suit_id',0)->setField('status',0);
            apiReturn(200,'设置成功');
        } catch (\Exception $e) {
            apiReturn(202, $e->getMessage());
        }
    }
    


}

