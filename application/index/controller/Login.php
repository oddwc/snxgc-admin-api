<?php

namespace app\index\controller;

use app\common\controller\Base;
use think\cache\driver\Redis;
use think\Db;

class Login extends Base
{
    public function checkLogin()
    {
            $params = $this->request->post();
            $redis = new Redis();
            if ($redis->get('code') != strtoupper($params['code'])) { apiReturn(202,'验证码错误'); }
            $user = Db::name('admin')
                ->where('username',$params['username'])
                ->where('password',passCrypt($params['password']))
                ->field('id,username,logintime,loginip,status,nickname,avatar')
                ->find();
            if(empty($user)){ apiReturn(202,'账号密码错误'); }
            if($user['status'] == 0) { apiReturn(202,'账号已被禁用'); }

            $list = [];
            try{
                $token = createToken();
                $list['token']=$token;

                Db::name('admin')->where('id',$user['id'])->update(['token'=>$token,'logintime'=>now(),'loginip'=>$_SERVER['REMOTE_ADDR']]);

                $user['roles'] = $this->auth;
                $list['user']=$user;

                $redis->set('token',$token,21600);
                apiReturn(200,'登录成功',$list);

            }catch (\Exception $e){
                apiReturn(202,$e->getMessage());
            }

    }

    public function logout(){
        $redis = new Redis();
        $redis->clear('token');
    }

    public function createVerify()
    {

        header ('Content-Type: image/png');

        $image=imagecreatetruecolor(100, 30);
        //背景颜色为白色
        $color=imagecolorallocate($image, 240, 249, 235);
        imagefill($image, 20, 20, $color);

        $code='';
        for($i=0;$i<4;$i++){
            $fontSize=8;
            $x=rand(5,10)+$i*100/4;
            $y=rand(5, 15);
            // $data='abcdefghijklmnpqrstuvwxyz123456789';
            $data='ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
            $string=substr($data,rand(0, strlen($data)),1);
            $code.=$string;
            $color=imagecolorallocate($image,rand(0,120), rand(0,120), rand(0,120));
            imagestring($image, $fontSize, $x, $y, $string, $color);
        }
        $redis = new Redis();
        $redis->set('code',$code,30);//存储在缓存里

        for($i=0;$i<200;$i++){
            $pointColor=imagecolorallocate($image, rand(100, 255), rand(100, 255), rand(100, 255));
            imagesetpixel($image, rand(0, 100), rand(0, 30), $pointColor);
        }
        for($i=0;$i<2;$i++){
            $linePoint=imagecolorallocate($image, rand(150, 255), rand(150, 255), rand(150, 255));
            imageline($image, rand(10, 50), rand(10, 20), rand(80,90), rand(15, 25), $linePoint);
        }
        imagepng($image);
        imagedestroy($image);

    }



}

