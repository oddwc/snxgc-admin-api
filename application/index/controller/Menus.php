<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Db;

class Menus extends Base
{
    //构建动态路由
    public function buildMenus2(){
        $menus = Db::name('menu')
            ->where('pid',0)
            ->order('sort asc')
            ->field('id,pid,name,path,icon,component')
            ->select();

        foreach ($menus as $key => $menu) {
            $menus[$key]['meta']['title'] = $menu['name'];
            $menus[$key]['meta']['icon'] = $menu['icon'];
            $menus[$key]['component'] = 'Layout';
            $menus[$key]['path'] = '/'.$menu['path'];

            $menus[$key]['children'] = Db::name('menu')->where('pid',$menu['id']) ->field('id,pid,name,path,icon,component')->select();

            foreach ($menus[$key]['children'] as $key2 => $child) {

                $menus[$key]['children'][$key2]['meta']['title'] =$child['name'];
                $menus[$key]['children'][$key2]['meta']['icon'] =$child['icon'];

            }
        }
        
        apiReturn(200,'菜单获取成功',$menus);
    }

    //权限菜单
    public function buildMenus(){

        $admin_id = Db::name('admin')->where('token',$this->token)->value('id');
        $role_id = Db::name('admin_roles')->where('user_id',$admin_id)->value('role_id');
        $role_menus = Db::name('role_menus')
            ->alias('a')
            ->leftJoin('tp_menu b','a.menu_id=b.id')
            ->field('b.id,b.sort,b.pid,b.name,b.path,b.icon,b.component,b.hidden')
            ->where('a.role_id',$role_id)
            ->where('b.type','<',2)
            ->where('b.hidden',0)
            ->order('b.sort asc')
            ->select();
        foreach ($role_menus as $k=>$v){
            $role_menus[$k]['path'] = $v['pid'] == 0?'/'.$v['path']:$v['path'];
            $role_menus[$k]['alwaysShow'] = $v['pid'] == 0?true:false; //二级以上显示下级
            $role_menus[$k]['component'] = empty($v['component'])?'Layout':$v['component'];
            $role_menus[$k]['meta']['title'] = $v['name'];
            $role_menus[$k]['meta']['icon'] = $v['icon'];
        }
        $role_menus_list = listToTree($role_menus);


        apiReturn(200,'菜单获取成功',$role_menus_list);

    }

    //菜单树
    public function tree()
    {
        $menu = Db::name('menu')->field('id,pid,name as label')->order('sort asc')->select();

        apiReturn(200,'获取成功',listToTree($menu));
    }


    //菜单列表
    public function menus()
    {
        $list = [];
        $menu = Db::name('menu')->field('id,type,permission,name,sort,path,component,pid,cache,hidden,component_name,icon,create_time')->order('sort asc')->select();

        $menu = listToTree($menu);

        $list['content'] = $menu;
        $list['totalElements'] = Db::name('menu')->count();
        apiReturn(200,'获取成功',$list);
    }

    //菜单添加
    public function menusAdd()
    {
        $params = $this->request->post();
        $params['create_time'] = now();
        try{
            Db::name('menu')->insert($params);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }

    }

    //菜单编辑
    public function menusEdit()
    {
        $params = $this->request->put();
        if(isset($params['children'])){
            unset($params['children']);
        }
        try{
            Db::name('menu')->update($params);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
        
    }

    //菜单删除
    public function menusDel()
    {
        $id = $this->request->delete('id');
        try{
            Db::name('menu')->where('id',$id)->delete();
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }

    }
    

}

