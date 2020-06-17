<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Db;


class Auth extends Base
{
//    public function getRolesAuth($uid){
//        //查询当前用户的角色
//        $role_id = Db::name('admin_roles')->where('user_id',$uid)->value('role_id');
//        $premission = Db::name('role_menus')
//            ->where('role_id',$role_id)
//            ->column('menu_id');
//
//        foreach ($premission as $key=>$item){
//           $premission[$key] = Db::name('menu')->where('id',$item)->value('permission');
//        }
//        return $premission;
//    }

    public function admin()
    {

        $page = input('page');
        $size = input('size');

        $total =Db::name('admin')->count();

        $admin = Db::name('admin')->page($page,$size)->select();
        foreach ($admin as $k=>$v){
            $roles = Db::name('admin_roles')->where('user_id',$v['id'])->find();
            $role_name = Db::name('role')->where('id',$roles['role_id'])->value('name');
            $admin[$k]['avatar'] = $v['avatar']?get_file($v['avatar']):'';
            $admin[$k]['status'] = $v['status'] == 1?true:false;
            $admin[$k]['role_id'] = $roles['role_id'];
            $admin[$k]['role_name'] = $role_name;
        }

        apiReturnList(200,'获取成功',$page,$size,$total,$admin);
    }

    public function adminInfo()
    {

        $info = Db::name('admin')->where('token',$this->token)->where('status',1)->field('id,username,logintime,loginip,status,nickname,avatar')->find();
        if(empty($info)) { apiReturn(202,'当前用户信息不存在或者禁用'); }
        $info['roles'] = $this->auth;
        apiReturn(200,'获取成功',$info);
    }

    public function modifyProfile()
    {

        $parmas = $this->request->post();
        if(empty($parmas['nickname'])) { apiReturn(202,'昵称不能为空'); }

        try{
            Db::name('admin')->where('token',$this->token)->setField('nickname',$parmas['nickname']);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }

    }

    public function updatePass()
    {

        $parmas = $this->request->put();

        $admin = Db::name('admin')->where('token',$this->token)->where('status',1)->field('id,username,password')->find();

        if(empty($admin)) { apiReturn(202,'账号信息不存在，密码修改失败'); }


        if(empty($parmas['newPass']) || empty($parmas['oldPass'])){ apiReturn(202,'请输入正确的旧密码和新密码'); }

        if(passCrypt($parmas['oldPass']) != $admin['password']){ apiReturn(202,'原密码错误');}

        $update['password'] = passCrypt($parmas['newPass']);
        $update['salt'] = salt($parmas['newPass']);
        $res = Db::name('admin')->where('token',$this->token)->update($update);

        if($res){
            apiReturn(200,'修改成功');
        }else{
            apiReturn(202,'修改失败');
        }

    }

    public function updateAvatar()
    {

        $uid = Db::name('admin')->where('token',$this->token)->value('id');
        $file = $this->request->file('file');

        $avatar = upload($file,'admin','avatar',$uid,true);
        $res = Db::name('admin')->where('token',$this->token)->setField('avatar',$avatar);
        if($res){
            apiReturn(200,'修改成功');
        }else{
            apiReturn(202,'修改失败');
        }
    }

    public function changeStatus()
    {

        $uid = Db::name('admin')->where('token',$this->token)->value('id');
        $parmas = $this->request->put();

        if($parmas['id'] == $uid){ apiReturn(202,'当前用户正在使用，无法更改状态'); }
        if($parmas['status'] == true){
            $status = 1;
        }else{
            $status = 0;
        }
        try{
            Db::name('admin')->where('id',$parmas['id'])->setField('status',$status);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }



    }

    public function getAllRole()
    {
        $role = Db::name('role')->field('id,name')->select();
        apiReturn(200,'获取成功',$role);
    }

    public function adminAdd()
    {
        $params = $this->request->post();
        if(empty($params['role_id'])){ apiReturn(202,'请选择角色'); }
        if(empty($params['password'])){
            $pass= passCrypt('123456');
        }else{
            $pass = passCrypt($params['password']);
        }
        $insert['username'] = $params['username'];
        $insert['nickname'] = $params['nickname'];
        $insert['password'] = $pass;
        $insert['salt'] = salt($pass);
        $insert['logintime'] = now();
        $insert['loginip'] = $_SERVER['REMOTE_ADDR'];
        $insert['create_time'] = now();
        $insert['status'] = 1;
        $insert['token'] = createToken();
        try{
            $admin_id = Db::name('admin')->insertGetId($insert);
            Db::name('admin_roles')->insert(['user_id'=>$admin_id,'role_id'=>$params['role_id']]);
        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }
    }

    public function adminEdit()
    {
        $params = $this->request->put();
       if(empty($params['password'])){
           unset($params['password']);
       }else{
           $params['password'] = passCrypt($params['password']);
           $params['salt'] = salt($params['password']);
       }

       $params['update_time'] = now();

        $role = Db::name('admin_roles')->where('user_id',$params['id'])->find();
        try{
            if($role){
                Db::name('admin_roles')->where('user_id',$params['id'])->setField('role_id',$params['role_id']);
            }else{
                Db::name('admin_roles')->insert(['role_id'=>$params['role_id'],'user_id'=>$params['id']]);
            }
            unset($params['role_id']);
            Db::name('admin')->update($params);

        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }

    }

    public function adminDel()
    {
        $id = $this->request->delete('id');
        if($id == 1){ apiReturn(202,'此账号拥有至高无上的权利，无法删除'); }
        $admin_role = Db::name('admin_roles')->where('user_id',$id)->find();
        try{
            Db::name('admin')->where('id',$id)->delete();
            if($admin_role){
                Db::name('admin_roles')->where('user_id',$id)->delete();
            }

        }catch(\Exception $e){
            apiReturn(202,$e->getMessage());
        }

    }

    public function logs()
    {

        $page = input('page');
        $size = input('size');

        $total =Db::name('logs')->count();

        $logs = Db::name('logs')->page($page,$size)->select();
        foreach ($logs as $k=>$v){
           switch ($v['method']){
               case 'GET':
                   $logs[$k]['desc'] = '查看、获取列表';
                   break;
               case 'POST':
                   $logs[$k]['desc'] = '新增内容';
                   break;
               case 'PUT':
                   $logs[$k]['desc'] = '修改内容';
                   break;
               case 'DELETE':
                   $logs[$k]['desc'] = '删除内容';
                   break;
           }
        }

        apiReturnList(200,'获取成功',$page,$size,$total,$logs);
    }

}

