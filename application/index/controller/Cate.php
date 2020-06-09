<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Db;


class Cate extends Base
{

    //分类列表
    public function cate()
    {

        $cate = Db::name('attire_cate')->where('pid',1)->select();
        foreach ($cate as $key => $val) {
            $cate[$key]['status'] = $val['status']==1?true:false;
        }
        apiReturn(200,'获取成功',$cate);
    }

    //添加分类
    public function add(){
        $params = $this->request->post();
        if(empty($params['name'])) { apiReturn(202,'请填写分类标题'); }
        $params['pid'] = 1;

        try{
            $insertId = Db::name('attire_cate')->insertGetId($params);
            if(!empty($params['image'])){
                //同时移动文件到指定目录
                $newpath = move_file($params['image'],'/category/'.$insertId);
                Db::name('attire_cate')->where('id',$insertId)->setField('image',$newpath);
            }
            apiReturn(200,'添加成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //编辑分类
    public function edit(){
        $params = $this->request->put();
        if(empty($params['name'])) { apiReturn(202,'请填写分类标题'); }

        if(!empty($params['image'])) {
            $image = Db::name('attire_cate')->where('id',$params['id'])->value('image');
            if($params['image'] == $image){
                unset($params['image']);
            }
        }

        try{

            if(isset($params['image']) && !empty($params['image'])){
                //更新并同时移动文件到指定目录
                $newpath = move_file($params['image'],'/category/'.$params['id']);
                $params['image'] = $newpath;
            }

            Db::name('attire_cate')->where('id',$params['id'])->update($params);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    //删除分类（带附件）
    public function del(){
        $id = $this->request->delete('id');
        $file= Db::name('attire_cate')->where('id',$id)->value('image');
        try{
            //删除分类
            Db::name('attire_cate')->where('id',$id)->delete();
            //附件不为空则删除
            if(!empty($file)){
                //文件是否存在
                $absfile = upload_url().$file;
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

    //排序
    public function sort()
    {
        $params = $this->request->put();
        try{
            Db::name('attire_cate')->where('id',$params['id'])->setField('listorder',$params['listorder']);
            apiReturn(200,'排序修改成功');
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }
    //更改优先级
    public function changeLevel()
    {
        $params = $this->request->put();
        try{
            Db::name('attire_cate')->where('id',$params['id'])->setField('level',$params['level']);
            apiReturn(200,'修改成功');
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
            Db::name('attire_cate')->where('id', $parmas['id'])->setField('status', $display);
        } catch (\Exception $e) {
            apiReturn(202, $e->getMessage());
        }
    }

}

