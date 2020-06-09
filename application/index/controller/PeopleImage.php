<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Db;


class PeopleImage extends Base
{

    //列表
    public function peopleImage()
    {

        $page = input('page');
        $size = input('size');


        $total =Db::name('default_image')->count();
        $list= Db::name('default_image')->order('id desc')->page($page,$size)->select();

        foreach ($list as $key => $val) {
            $list[$key]['default'] = $val['default']?true:false;
            $list[$key]['status'] = $val['status']?true:false;
        }
        apiReturnList(200,'获取成功',$page,$size,$total,$list);
    }
    

    //添加
    public function add(){
        $params = $this->request->post();
        
        if(empty($params['title'])) { apiReturn(202,'请填写标题'); }
        if(empty($params['image'])) { apiReturn(202,'请上传图片'); }

        try{
            $insertId = Db::name('default_image')->insertGetId($params);
            //同时移动文件到指定目录
            $newpath = move_file($params['image'],'/default_image/'.$insertId);
            Db::name('default_image')->where('id',$insertId)->setField('image',$newpath);
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
            $thumb = Db::name('default_image')->where('id',$params['id'])->value('image');
            if($params['image'] == $thumb){
                unset($params['image']);
            }
        }


        try{

            if(isset($params['image'])){
                //更新并同时移动文件到指定目录
                $newpath = move_file($params['image'],'/default_image/'.$params['id']);
                $params['image'] = $newpath;
            }

            Db::name('default_image')->where('id',$params['id'])->update($params);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //删除分类（带附件）
    public function del(){
        $id = $this->request->delete('id');
        $file= Db::name('default_image')->where('id',$id)->value('image');
        try{
            //删除分类
            Db::name('default_image')->where('id',$id)->delete();
            //附件不为空则删除
            if(!empty($file)){
                //文件是否存在
                $absfile = upload_url().$file;
                if(file_exists($absfile)){
                    unlink($absfile);
                }else{
                    apiReturn(202,'文件不存在，无法删除');
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
            Db::name('default_image')->where('id', $parmas['id'])->setField('status', $display);
        } catch (\Exception $e) {
            apiReturn(202, $e->getMessage());
        }
    }

    //设置默认形象
    public function setDefault()
    {

        $parmas = $this->request->put();    

        if ($parmas['status'] == true) {
            $display = 1;
        } else {
            $display = 0;
        }
        try {
            Db::name('default_image')->where('id', $parmas['id'])->setField('default', $display);
        } catch (\Exception $e) {
            apiReturn(202, $e->getMessage());
        }
    }
//更改优先级
    public function changeLevel()
    {
        $params = $this->request->put();
        try{
            Db::name('default_image')->where('id',$params['id'])->setField('level',$params['level']);
            apiReturn(200,'优先级修改成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }
}

