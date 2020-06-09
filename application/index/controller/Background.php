<?php


namespace app\index\controller;

use app\common\controller\Base;
use think\Db;

class Background extends Base
{

    //列表
    public function background()
    {

        $page = input('page');
        $size = input('size');

        $total =Db::name('background')->count();
        $list= Db::name('background')->order('id desc')->page($page,$size)->select();

        foreach ($list as $key => $val) {
            $list[$key]['status'] = $val['status']?true:false;

        }
        apiReturnList(200,'获取成功',$page,$size,$total,$list);
    }

    //排序
    public function sort()
    {
        $params = $this->request->put();
        try{
            Db::name('background')->where('id',$params['id'])->setField('listorder',$params['listorder']);
            apiReturn(200,'排序修改成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //添加
    public function add(){
        $params = $this->request->post();

        if(empty($params['title'])) { apiReturn(202,'请填写标题'); }
        if(empty($params['thumb'])) { apiReturn(202,'请上传缩略图'); }
        if(empty($params['image'])) { apiReturn(202,'请上传图片'); }
        $params['create_time'] = now();
        try{
            $insertId = Db::name('background')->insertGetId($params);
            //同时移动文件到指定目录
            $update['thumb'] = move_file($params['thumb'],'/background/'.$insertId);
            $update['image'] = move_file($params['image'],'/background/'.$insertId);
            Db::name('background')->where('id',$insertId)->update($update);

            apiReturn(200,'添加成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //编辑
    public function edit(){
        $params = $this->request->put();

        if(empty($params['title'])) { apiReturn(202,'请填写标题'); }

        if(empty($params['thumb'])) {
            apiReturn(202,'请上传缩略图');
        }else{
            $thumb = Db::name('background')->where('id',$params['id'])->value('thumb');
            if($params['thumb'] == $thumb){
                unset($params['thumb']);
            }
        }
        if(empty($params['image'])) {
            apiReturn(202,'请上传图片');
        }else{
            $image = Db::name('background')->where('id',$params['id'])->value('image');
            if($params['image'] == $image){
                unset($params['image']);
            }
        }

        try{
            if(isset($params['thumb'])){
                //更新并同时移动文件到指定目录
                $params['thumb'] = move_file($params['thumb'],'/background/'.$params['id']);;
            }
            if(isset($params['image'])){
                //更新并同时移动文件到指定目录
                $params['image'] = move_file($params['image'],'/background/'.$params['id']);;
            }

            Db::name('background')->where('id',$params['id'])->update($params);

            apiReturn(200,'编辑成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //删除分类（带附件）
    public function del(){
        $id = $this->request->delete('id');
        $file= Db::name('background')->where('id',$id)->field('thumb,image')->find();
        try{
            //删除分类
            Db::name('background')->where('id',$id)->delete();
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
            Db::name('background')->where('id', $parmas['id'])->setField('status', $display);
        } catch (\Exception $e) {
            apiReturn(202, $e->getMessage());
        }
    }

}