<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Db;


class AttireAttribute extends Base
{

    //列表
    public function attireAttribute()
    {
        $id = $this->request->get('id');
        $list= Db::name('attire_attribute')->where('attire_id',$id)->order('id desc')->select();

        apiReturn(200,'获取成功',$list);
    }



    //更改优先级
    public function changeLevel()
    {
        $params = $this->request->put();
        try{
            Db::name('attire_attribute')->where('id',$params['id'])->setField('level',$params['level']);
            apiReturn(200,'优先级修改成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //添加
    public function add(){
        $params = $this->request->post();

        if(empty($params['title'])) { apiReturn(202,'请填写标题'); }
        if(empty($params['image'])) { apiReturn(202,'请上传图片'); }
        $params['create_time'] = now();
        try{
            $insertId = Db::name('attire_attribute')->insertGetId($params);
            //同时移动文件到指定目录
            $update['image'] = move_file($params['image'],'/attire_attribute/'.$insertId);
            Db::name('attire_attribute')->where('id',$insertId)->update($update);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //编辑
    public function edit(){
        $params = $this->request->put();

        if(empty($params['title'])) { apiReturn(202,'请填写标题'); }

        if(empty($params['image'])) {
            apiReturn(202,'请上传图片');
        }else{
            $image = Db::name('attire_attribute')->where('id',$params['id'])->value('image');
            if($params['image'] == $image){
                unset($params['image']);
            }
        }

        try{

            if(isset($params['image'])){
                //更新并同时移动文件到指定目录
                $params['image'] = move_file($params['image'],'/attire_attribute/'.$params['id']);;
            }

            Db::name('attire_attribute')->where('id',$params['id'])->update($params);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //删除分类（带附件）
    public function del(){
        $id = $this->request->delete('id');
        $file= Db::name('attire_attribute')->where('id',$id)->field('image')->find();
        try{
            //删除分类
            Db::name('attire_attribute')->where('id',$id)->delete();
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



}

