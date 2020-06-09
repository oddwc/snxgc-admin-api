<?php
namespace app\common\controller;

use think\App;
use think\cache\driver\Redis;
use think\Controller;
use think\Db;

class Base extends Controller
{
    protected $noNeedToken = ['login','logout','createVerify'];

    protected $auth = [];

    protected $token = '';

    public function __construct(App $app = null)
    {
        parent::__construct($app);

        if(!in_array($this->request->path(),$this->noNeedToken)){
            $this->token = self::check_token();
            $this->auth = self::getAuth($this->token);
        }


    }

    private static function check_token()
    {
        $redis = new Redis();
        $token =$redis->get('token');
        if(empty($token)){ apiReturn(401,'token已失效'); }

        return $token;
    }

    private static function getAuth($token)
    {
        $admin_id = Db::name('admin')->where('token',$token)->value('id');
        $role_id = Db::name('admin_roles')->where('user_id',$admin_id)->value('role_id');
        $auth = Db::name('role_menus')
            ->alias('a')
            ->leftJoin('tp_menu b','a.menu_id=b.id')
            ->where('a.role_id',$role_id)
            ->where('b.type','>',0)
            ->where('b.hidden',0)
            ->column('b.permission');
        if($admin_id == 1){
            array_unshift($auth,"admin");
        }

        return $auth;
    }



    //空操作
    public function _empty(){
        return $this->error('空操作，返回上次访问页面中...');
    }

}
