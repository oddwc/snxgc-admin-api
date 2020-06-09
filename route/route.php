<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    'createVerify' => 'login/createVerify',//生成验证码
    'login' => 'login/checkLogin',//登录
    'logout' => 'login/logout',//退出登录

    'admin'=>'auth/admin', //管理员列表
    'admin/add'=>'auth/adminAdd', //添加管理员
    'admin/edit'=>'auth/adminEdit', //编辑管理员
    'admin/del'=>'auth/adminDel', //编辑管理员
    'admin/changeStatus'=>'auth/changeStatus', //更改状态
    'info'=>'auth/adminInfo', //管理员信息
    'modifyProfile' => 'auth/modifyProfile', // 修改个人资料
    'updatePass' => 'auth/updatePass', // 修改个登录密码
    'updateAvatar' => 'auth/updateAvatar', // 修改头像
    'getAllRole' => 'auth/getAllRole', // 获取所有角色


    'menus/build' =>'menus/buildMenus', //动态菜单路由
    'menus/add' =>'menus/menusAdd', //添加菜单
    'menus/edit' =>'menus/menusEdit', //编辑菜单-action
    'menus/del' =>'menus/menusDel', //删除菜单
    'menus/tree' =>'menus/tree', //菜单编辑view-获取菜单树
    'menus' =>'menus/menus', //菜单

    'roles' =>'roles/roles',//角色列表
    'roles/add' =>'roles/rolesAdd',//角色添加
    'roles/edit' =>'roles/rolesEdit',//角色编辑
    'roles/del' =>'roles/rolesDel',//角色删除
    'roles/menu' =>'roles/menu',//角色删除


    'upload' => 'index/upload', //上传图片


    /**
     * 配置管理
     */
    'system/systemInfo' => 'system/systemInfo',//获取系统信息
    'system/config' => 'system/config',//网站配置
    'system/config/edit' => 'system/editConfig', // 编辑网站配置
    'system/pay' => 'system/pay', // 支付配置
    'system/pay/edit' => 'system/editPay', // 编辑支付配置
    'system/recharge' => 'system/recharge', // 充值配置
    'system/recharge/add' => 'system/rechargeAdd', // 添加充值配置
    'system/recharge/edit' => 'system/rechargeEdit', // 编辑充值配置
    'system/recharge/del' => 'system/rechargeDel', // 删除充值配置
    'system/recharge/changeTestStatus' => 'system/changeTestStatus',//切换测试档位
    'system/recharge/changeRechargeConfigStatus' => 'system/changeRechargeConfigStatus',//切换测试档位
    'system/exchange' => 'system/exchange', // 兑换配置
    'system/exchange/add' => 'system/exchangeAdd', // 添加兑换配置
    'system/exchange/edit' => 'system/exchangeEdit', // 编辑兑换配置
    'system/exchange/del' => 'system/exchangeDel', // 删除兑换配置
    'system/sign' => 'system/sign', // 签到配置
    'system/sign/edit' => 'system/signEdit', // 编辑兑换配置

    'system/statistics' => 'system/statistics', // 统计
    'system/rechargeStatistics' => 'system/rechargeStatistics', // 统计




    /**
     * 装饰分类
     */
    'cate' => 'cate/cate',//列表
    'cate/sort' => 'cate/sort',//列表
    'cate/tree' => 'cate/tree',//分类树
    'cate/add' => 'cate/add',//分类添加
    'cate/edit' => 'cate/edit',//分类编辑
    'cate/del' => 'cate/del',//分类删除
    'cate/changeStatus' => 'cate/changeStatus',//更改状态
    'cate/changeLevel'=>'cate/changeLevel', //更改优先级


    /**
     * 装饰
     */
    'attire' => 'attire/attire', //列表
    'attire/sort' => 'attire/sort', //排序
    'attire/search' => 'attire/search', //搜索
    'attire/add' => 'attire/add', //添加
    'attire/edit' => 'attire/edit', //编辑
    'attire/del' => 'attire/del', //删除
    'attire/changeStatus'=>'attire/changeStatus', //更改状态
    'attire/getAttireCate'=>'attire/getAttireCate', //分类
    'attire/getLevel'=>'attire/getLevel', //获取分类优先级
    'attire/getAllAttire'=>'attire/getAllAttire', //所有套装
//    'attire/changeLevel'=>'attire/changeLevel', //更改优先级
    'attire/setDefaultAttire'=>'attire/setDefaultAttire', //设置默认的装饰
    'attire/clearSingleAttire'=>'attire/clearSingleAttire', //清除所有已设置的默认单件装饰
    'attire/setCateLevel'=>'attire/setCateLevel', //批量修改单件对应分类的层级


    /**
     * 装饰子组件
     */
    'attireAttribute' => 'attireAttribute/attireAttribute', //列表
    'attireAttribute/add' => 'attireAttribute/add', //添加
    'attireAttribute/edit' => 'attireAttribute/edit', //编辑
    'attireAttribute/del' => 'attireAttribute/del', //删除
    'attireAttribute/changeLevel'=>'attireAttribute/changeLevel', //更改优先级

    /**
     * 套装
     */
    'suit' => 'suit/suit', //列表
    'suit/sort' => 'suit/sort', //排序
    'suit/search' => 'suit/search', //搜索
    'suit/add' => 'suit/add', //添加
    'suit/edit' => 'suit/edit', //编辑
    'suit/del' => 'suit/del', //删除
    'suit/changeStatus'=>'suit/changeStatus', //更改状态
    'suit/setDefaultAttire'=>'suit/setDefaultAttire', //设置默认的套装为初始装饰
    'suit/clearSuitAttire'=>'suit/clearSuitAttire', //清除默认的套装

    /**
     * 人物形象配置
     */
    'peopleImage' => 'peopleImage/peopleImage', //列表
    'peopleImage/add' => 'peopleImage/add', //添加
    'peopleImage/edit' => 'peopleImage/edit', //编辑
    'peopleImage/del' => 'peopleImage/del', //删除
    'peopleImage/changeStatus'=>'peopleImage/changeStatus', //更改状态
    'peopleImage/setDefault'=>'peopleImage/setDefault', //设置默认形象
    'peopleImage/changeLevel'=>'peopleImage/changeLevel', //更改优先级

    /**
     * 摄影棚
     */
    'background' => 'background/background', //列表
    'background/sort' => 'background/sort', //排序
    'background/add' => 'background/add', //添加
    'background/edit' => 'background/edit', //编辑
    'background/del' => 'background/del', //删除
    'background/changeStatus'=>'background/changeStatus', //更改状态



    /**
     * 订单管理
     */
    'orders' => 'orders/orders', //列表
    'orders/search' => 'orders/search', //搜索
    'orders/export' => 'orders/export', //导出
//    'background/edit' => 'background/edit', //编辑
//    'background/del' => 'background/del', //删除
//    'background/changeStatus'=>'background/changeStatus', //更改状态
    'orders/allRechargeConfig'=>'orders/allRechargeConfig', //所有充值配置列表



    'users' => 'users/index', //users列表
    'users/search' => 'users/search', //搜索
    'users/clearAttires' => 'users/clearAttires', //清除用户装扮为默认



    'users/feedback' => 'users/feedback', //意见反馈
    'users/feedback/del' => 'users/feedbackDel', //删除意见反馈



];
