<?php


namespace app\index\controller;


use app\common\controller\Base;
use think\Db;

class Level extends Base
{

    public function level()
    {

        $page = input('page');
        $size = input('size');

        $total =Db::name('level')->count();
        $list= Db::name('level')->order('id desc')->page($page,$size)->select();

        apiReturnList(200,'获取成功',$page,$size,$total,$list);
    }

    public function add()
    {
        $parmas = $this->request->post();

        if(empty($parmas['title'])){ apiReturn(202,'名称不能为空'); }

        try {
            Db::name('level')->insert($parmas);
            apiReturn(200,'添加成功');
        }catch (\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    public function edit()
    {
        $parmas = $this->request->put();
        if(empty($parmas['title'])){ apiReturn(202,'名称不能为空'); }

        try {
            Db::name('level')->where('id',$parmas['id'])->update($parmas);
            apiReturn(200,'修改成功');
        }catch (\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    public function del()
    {
        $id = $this->request->delete('id');

        try {
            Db::name('level')->where('id',$id)->delete();
            apiReturn(200,'删除成功');
        }catch (\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    public function getLevelList()
    {
        $list = Db::name('level')->field('id,title as label,level as value')->select();
        apiReturn(200,'获取成功',$list);
    }
}