<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Db;

class Roles extends Base
{

    //角色列表
    public function roles()
    {
        $page = input('page');
        $size = input('size');

        $total =Db::name('role')->count();
        $role = Db::name('role')->page($page,$size)->select();
        foreach ($role as $k=>$v){
            $role[$k]['menus'] = Db::name('role_menus')
                ->alias('a')
                ->where('role_id',$v['id'])
                ->leftJoin('menu b','b.id=a.menu_id')
                ->field('b.id,b.name')
                ->order('sort asc')
                ->select();
        }

        apiReturnList(200,'获取成功',$page,$size,$total,$role);
    }

    //角色添加
    public function rolesAdd()
    {
        $params = $this->request->post();
        $params['create_time'] = now();
        try{
            Db::name('role')->insert($params);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }

    }

    //角色编辑
    public function rolesEdit()
    {
        $params = $this->request->put();
        try{
            Db::name('role')->update($params);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
        
    }

    //角色删除
    public function rolesDel()
    {
        $id = $this->request->delete('id');
        $role_menus = Db::name('role_menus')->where('role_id',$id)->find();
        try{
            if($role_menus){
                Db::name('role_menus')->where('role_id',$id)->delete();
            }

            Db::name('role')->where('id',$id)->delete();
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }

    }

    //选择角色菜单
    public function menu()
    {
        $params = $this->request->put();

        $role_id = Db::name('role_menus')->where('role_id',$params['id'])->find();
        try{

            if($role_id){
                Db::name('role_menus')->where('role_id',$params['id'])->delete();
            }

            foreach ($params['menus'] as $k=>$v){
                $insert['role_id'] = $params['id'];
                $insert['menu_id'] = $v['id'];
                Db::name('role_menus')->insert($insert);
            }

        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }
    

}

