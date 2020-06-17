<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Db;


class Suit extends Base
{

    //列表
    public function suit()
    {

        $page = input('page')?:1;
        $size = input('size')?:10;

        $field = 'id,pid,title,thumb,image,type,cost,listorder,status,create_time';
        $total =Db::name('attire')->where('is_suit',1)->count();
        $list= Db::name('attire')->where('is_suit',1)->field($field)->order('id desc')->page($page,$size)->select();

        foreach ($list as $key => $val) {
            $list[$key]['status'] = $val['status']?true:false;
            $children = Db::name('attire')->where('pid',$val['id'])->where('status',1)->field('id,title,thumb,type,cost')->select();
            $list[$key]['childern'] = $children;

            $children_count = count($children);

            $default_attire_count = 0;
            foreach ($children as $k=>$v){
                $default_attire = Db::name('default_attire')->where('attire_id', $v['id'])->where('status', 1)->find();
                if($default_attire){
                    $default_attire_count += 1;
                }
            }
            if(!empty($children) && $children_count == $default_attire_count){
                $default_attire_status = true;
            }else{
                $default_attire_status = false;
            }

            $list[$key]['default_attire'] = $default_attire_status;


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

    //搜索
    public function search()
    {


        $page = input('page')?:1;
        $size = input('size')?:10;

        $where[] = ['id|title','like','%'.input('title').'%'];
        $where[] = ['is_suit','=',1];

        $total =Db::name('attire')->where($where)->count();
        $list= Db::name('attire')->where($where)->page($page,$size)->select();

        foreach ($list as $key => $val) {
            $list[$key]['status'] = $val['status']?true:false;
            $children = Db::name('attire')->where('pid',$val['id'])->where('status',1)->field('id,title,thumb,type,cost')->select();
            $list[$key]['childern'] = $children;

            $children_count = count($children);

            $default_attire_count = 0;
            foreach ($children as $k=>$v){
                $default_attire = Db::name('default_attire')->where('attire_id', $v['id'])->where('status', 1)->find();
                if($default_attire){
                    $default_attire_count += 1;
                }
            }
            if(!empty($children) && $children_count == $default_attire_count){
                $default_attire_status = true;
            }else{
                $default_attire_status = false;
            }

            $list[$key]['default_attire'] = $default_attire_status;


        }
        apiReturnList(200,'获取成功',$page,$size,$total,$list);
    }


    //添加
    public function add(){
        $params = $this->request->post();
        
        if(empty($params['title'])) { apiReturn(202,'请填写标题'); }
        if(empty($params['thumb'])) { apiReturn(202,'请上传格子图'); }
        if(empty($params['image'])) { apiReturn(202,'请上传选中图'); }
        $params['cate_id'] = 2;
        $params['is_suit'] = 1;
        $params['create_time'] = now();
        try{
            $insertId = Db::name('attire')->insertGetId($params);
            //同时移动文件到指定目录
            $update['thumb'] = move_file($params['thumb'],'/suit/'.$insertId);
            $update['image'] = move_file($params['image'],'/suit/'.$insertId);
            Db::name('attire')->where('id',$insertId)->update($update);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //编辑
    public function edit(){
        $params = $this->request->put();

        if(empty($params['title'])) { apiReturn(202,'请填写标题'); }

        if(empty($params['thumb'])) {
            apiReturn(202,'请上传格子图');
        }else{
            $thumb = Db::name('attire')->where('id',$params['id'])->value('thumb');
            if($params['thumb'] == $thumb){
                unset($params['thumb']);
            }
        }

        if(empty($params['image'])) {
            apiReturn(202,'请上传选中图');
        }else{
            $image = Db::name('attire')->where('id',$params['id'])->value('image');
            if($params['image'] == $image){
                unset($params['image']);
            }
        }


        try{

            if(isset($params['thumb'])){
                //更新并同时移动文件到指定目录
                $newpath = move_file($params['thumb'],'/suit/'.$params['id']);
                $params['thumb'] = $newpath;
            }

            if(isset($params['image'])){
                //更新并同时移动文件到指定目录
                $params['image'] = move_file($params['image'],'/suit/'.$params['id']);
            }

            Db::name('attire')->where('id',$params['id'])->update($params);
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

    //设置默认的套装为初始装饰
    public function setDefaultAttire()
    {

        $parmas = $this->request->put();

        $exit_suit = Db::name('default_attire')->where('suit_id', $parmas['id'])->find();

        if($exit_suit){ //先设置过套装的部分单件
            $default_suit_arrire_id = Db::name('default_attire')->where('suit_id',$parmas['id'])->column('attire_id');
            $attire_id = Db::name('attire')->where('pid', $parmas['id'])->column('id');

            $list = array_diff($attire_id,$default_suit_arrire_id);

            if(empty($list)){
                $default_attire = Db::name('default_attire')->where('suit_id', $parmas['id'])->field('id,status')->select();
                foreach ($default_attire as $key=>$val){
                    if($val['status'] == 1){
                        Db::name('default_attire')->where('id',$val['id'])->setField('status',0);
                    }else{
                        Db::name('default_attire')->where('id',$val['id'])->setField('status',1);
                    }
                }
            }else{
                foreach ($list as $k=>$val){
                    Db::name('default_attire')->insert(['suit_id'=>$parmas['id'],'attire_id'=>$val,'status'=>1]);
                }
                Db::name('default_attire')->where('suit_id', $parmas['id'])->setField('status', 1);
            }

        }else{ //没有设置套装

            $attires = Db::name('attire')->where('pid', $parmas['id'])->column('id');
            foreach ($attires as $key=>$val){
                Db::name('default_attire')->insert(['suit_id'=>$parmas['id'],'attire_id'=>$val,'status'=>1]);

            }

        }
        apiReturn(200,'设置成功');
    }

    //清除所有已设置的默认套装
    public function clearSuitAttire()
    {

        try {
            Db::name('default_attire')->where('suit_id','>',0)->setField('status',0);
            apiReturn(200,'设置成功');
        } catch (\Exception $e) {
            apiReturn(202, $e->getMessage());
        }
    }

}

