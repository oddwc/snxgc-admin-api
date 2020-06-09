<?php
// 应用公共文件
use think\Db;

function now(){
    return date('Y-m-d H:i:s',time());
}

//获取音频/视频文件的播放时长
function getMusicTimes($file){
    require_once env('extend_path').'/getID3-master/getid3/getid3.php';
    $getid3 = new \getID3();
    $thisFileInfo = @$getid3->analyze(upload_url().$file);

    $fileInfo = $thisFileInfo['playtime_seconds'];
    return secToTime($fileInfo);
}

/**
 *      把秒数转换为时分秒的格式
 *      @param Int $times 时间，单位 秒
 *      @return String
 */
function secToTime($times){
    $result = '00:00:00';
    if ($times>0) {
        $hour = floor($times/3600);
        $minute = floor(($times-3600 * $hour)/60);
        $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);


        $hour = strlen($hour) == 1 ? '0' . $hour : $hour;
        $minute = strlen($minute) == 1 ? '0' . $minute : $minute;
        $second = strlen($second) == 1 ? '0' . $second : $second;
        $result = $hour.':'.$minute.':'.$second;
    }
    return $result;
}

/**
 * 居中裁剪图片
 * @param string $source [原图路径]
 * @param int $width [设置宽度]
 * @param int $height [设置高度]
 * @param string $target [目标路径]
 * @return bool [裁剪结果]
 */
function image_center_crop($source, $width, $height, $target)
{
    if (!file_exists($source)) return false;
    /* 根据类型载入图像 */
    switch (exif_imagetype($source)) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($source);
            break;
    }
    if (!isset($image)) return false;
    /* 获取图像尺寸信息 */
    $target_w = $width;
    $target_h = $height;
    $source_w = imagesx($image);
    $source_h = imagesy($image);
    /* 计算裁剪宽度和高度 */
    $judge = (($source_w / $source_h) > ($target_w / $target_h));
    $resize_w = $judge ? ($source_w * $target_h) / $source_h : $target_w;
    $resize_h = !$judge ? ($source_h * $target_w) / $source_w : $target_h;
    $start_x = $judge ? ($resize_w - $target_w) / 2 : 0;
    $start_y = !$judge ? ($resize_h - $target_h) / 2 : 0;
    /* 绘制居中缩放图像 */
    $resize_img = imagecreatetruecolor($resize_w, $resize_h);
    imagecopyresampled($resize_img, $image, 0, 0, 0, 0, $resize_w, $resize_h, $source_w, $source_h);
    $target_img = imagecreatetruecolor($target_w, $target_h);
    imagecopy($target_img, $resize_img, 0, 0, $start_x, $start_y, $resize_w, $resize_h);
    /* 将图片保存至文件 */
    if (!file_exists(dirname($target))) mkdir(dirname($target), 0777, true);
    switch (exif_imagetype($source)) {
        case IMAGETYPE_JPEG:
            imagejpeg($target_img, $target);
            break;
        case IMAGETYPE_PNG:
            imagepng($target_img, $target);
            break;
        case IMAGETYPE_GIF:
            imagegif($target_img, $target);
            break;
    }
    return boolval(file_exists($target));
}

/**
 * 无限分类-权限
 * 核心函数, 将列表数据转化树形结构
 * 使用前提必须是先有父后有子, 即儿子的id必须小于父亲id
 * 列表数据必须安装id从小到大排序
 * @param $lists 原始列表数据
 * @param string $childKey 字段名
 * @return array 返回树形数据
 */
function listToTree($lists, $childKey = 'children'){
    $map = [];
    $res = [];
    foreach($lists as $id => &$item){
        // 获取出每一条数据的父id
        $pid = &$item['pid'];
        // 将每一个item的引用保存到$map中
        $map[$item['id']] = &$item;
        // 如果在map中没有设置过他的pid, 说明是根节点, pid为0,
        if($pid == 0){
            // 将pid为0的item的引用保存到$res中
            $res[$id] = &$item;
        }else{
            // 如果在map中没有设置过他的pid, 则将该item加入到他父亲的叶子节点中
            $pItem = &$map[$pid];
            $pItem[$childKey][] = &$item;
        }
    }
    return array_values($res);
}

//获得视频文件的总长度时间和创建时间
function getTime($file)
{
    $vtime = exec("ffmpeg -i " . $file . " 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//");//总长度
    $ctime = date("Y-m-d H:i:s", filectime($file));//创建时间
    //$duration = explode(":",$time);
    // $duration_in_seconds = $duration[0]*3600 + $duration[1]*60+ round($duration[2]);//转化为秒
    return array('vtime' => $vtime,
        'ctime' => $ctime
    );
}

//获得视频文件的缩略图
function getVideoCover($file, $time, $name)
{
    if (empty($time)) $time = '1';//默认截取第一秒第一帧
    $strlen = strlen($file);
    // $videoCover = substr($file,0,$strlen-4);
    // $videoCoverName = $videoCover.'.jpg';//缩略图命名
    //exec("ffmpeg -i ".$file." -y -f mjpeg -ss ".$time." -t 0.001 -s 320x240 ".$name."",$out,$status);
    $str = "ffmpeg -i " . $file . " -y -f mjpeg -ss 3 -t " . $time . " -s 320x240 " . $name;
    //echo $str."</br>";
    $result = system($str);
}

function img_url()
{
    $config = Db::name('config')->where('config_type', 'web_config')->value('config_content');
    $config = json_decode($config, true);
    return $config['img_url'];
}

/**
 * 获取文件完整路径
 * @param $file
 * @return string
 */
function get_file($file)
{
    $config = Db::name('config')->where('config_type', 'web_config')->value('config_content');
    $config = json_decode($config, true);
    return $config['img_url'] . $file;
}

/**
 * @Title: create_randomstr
 * @Description: 获取随机字符串
 * @param @param number $lenth  字符长度
 */
function create_randomstr($lenth = 6)
{
    $str = '';
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol) - 1;
    for ($i = 0; $i < $lenth; $i++) {
        $str .= $strPol[rand(0, $max)];
    }
    return $str;
}

//返回当前的毫秒时间戳
function mstime() {
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);

    return $msectime;
}

//上传图片
function upload($file, $p1, $p2, $p3,$unique=false)
{
    $path = '/' . $p1 . '/' . $p2 . '/' . file_rule($p3) . '/' . $p3;
    if($unique){
        if (is_dir(upload_url() . $path)) {
            deldir(upload_url() . $path);
        }
        $filename = $path . '/' . mstime().mt_rand(1111111111,9999999999); //存储的文件名
    }else{
        $filename = $path . '/' .date('Ymd',time()).'/'. mstime().mt_rand(1111111111,9999999999); //存储的文件名
    }
    $info = $file->validate(['size' => 20971520, 'ext' => 'jpg,jpeg,png,gif,mp3,mp4'])->move(upload_url(),$filename);
    if ($info) {
        $url = $info->getSaveName();
        $url = str_replace("\\", "/", $url);
        return $url;
    }
}

//上传图片到临时目录
function uploadsFile($file){

    $filename= '/temporary/'.date('Ymd',time()).'/'.mstime().mt_rand(1111111111,9999999999); //存储的文件名

    $info = $file->validate(['size'=>20971520,'ext'=>'jpg,jpeg,png,gif,mp3,mp4'])->move(upload_url(),$filename);

    if($info){

        $url = $info->getSaveName();

        $url = str_replace("\\","/",$url);
        return $url;

    }
}
//移动文件到指定目录
function move_file($file,$path){
    $file_arr = explode('/',$file);
    $length = count($file_arr);
    $filename = $file_arr[$length-1];

    $file_path = upload_url().$file;
    $new_path = upload_url() .$path.'/'.$filename;
    if(file_exists($file_path)){
        if(!is_dir(upload_url() .$path)){
            mkdir(upload_url() .$path,0777,true);
        }

        try{
            copy($file_path,$new_path);
            unlink($file_path);
        }catch(\Exception $e){
            apiReturn(202,'文件移动失败');
        }

        return $path.'/'.$filename;

    }else{
        apiReturn(202,'未找到文件');
    }
}

//上传附件地址
function upload_url(){
    $conf = Db::name('config')->where(['config_type'=>'web_config'])->value('config_content');
    $host = json_decode($conf,true)['upload_url'];
    return $host;
}

//远程下载m3u格式文件
function downloadFile($url, $savePath, $filename = '')
{
    $source_url = explode('?', $url);
    $replace_url = substr($source_url[0], 0, strrpos($source_url[0], '/'));

    $config = Db::name('config')->where('config_type', 'web_config')->value('config_content');
    $config = json_decode($config, true);

    $basePath = $config['upload_url'];
    if (!$url) return false;


    if (!$filename) {
        $filename = create_randomstr(16) . '.m3u8';
    }

    if (!is_dir($basePath . $savePath)) mkdir($basePath . $savePath, 0777, true);
    if (!is_readable($basePath . $savePath)) chmod($basePath . $savePath, 0777, true);

    $filenames = $basePath . $savePath . $filename;
    $file = file_get_contents($url);

    $str = preg_replace('/,\s+/', ',' . PHP_EOL . $replace_url . '/', $file);

    file_put_contents($filenames, $str);


    return $savePath . $filename;
}

/**
 * 自定义日志
 * @param $log_name
 * @param $log_data
 */
function mylog($log_name, $log_data)
{
    $log_path = env('runtime_path') . '/mylogs/';
    $log_file_name = env('runtime_path') . '/mylogs/' . date('Ym-d') . '.txt';
    if (!is_dir($log_path)) {
        mkdir($log_path, 0777, true);
    }

    if (is_array($log_data)) {
        $text = '【result】=' . var_export($log_data, true) . ';';
    } else {
        $text = '【 结 果 】：' . $log_data;
    }
    $contents = '[' . date('Y-m-d H:i:s', time()) . ']' . '---[变量名:' . $log_name . ']' . PHP_EOL . $text;
    file_put_contents($log_file_name, PHP_EOL . $contents, FILE_APPEND);

}

/**
 *jsonreturn
 */
function apiReturn($code, $msg, $data = '')
{
    header('Content-Type: application/json; charset=utf-8'); //网页编码
    $result['code'] = $code;
    $result['msg'] = $msg;
    $result['data'] = $data;

    echo json_encode($result);
    exit();
}

/**
 *jsonreturn 列表
 */
function apiReturnList($code, $msg, $page, $size, $total, $data = [])
{
    header('Content-Type: application/json; charset=utf-8'); //网页编码
    $result['code'] = $code;
    $result['msg'] = $msg;
    $result['data']['page'] = intval($page);
    $result['data']['size'] = intval($size);
    $result['data']['total'] = intval($total);
    $result['data']['data'] = $data;

    echo json_encode($result);
    exit();
}

/**
 * 密码加密
 * @param $password
 * @return string|null
 */
function passCrypt($password)
{
    $salt = salt($password);
    $password = crypt($password, $salt);
    return $password;
}

/**
 * 密码盐加密
 * @param $password
 * @return bool|string
 *
 */
function salt($password)
{
    $password = md5($password);
    $salt = substr($password, 0, 5);
    return $salt;
}

/**
 * 文件夹生成规则
 * @param $id
 * @return int
 */
function file_rule($id)
{

    if ($id > 0 && $id <= 999) return $pid = 0;
    for ($i = 0; $i <= 1000000; $i++) {
        $start = $i . '000';
        $end = $i . '999';
        if ($id >= $start && $id <= $end) return $i;
    }

}

/**
 * 删除目录及其文件
 * @param $dir
 * @return bool
 */
function deldir($dir)
{
    //先删除目录下的文件：
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {
            $fullpath = $dir . "/" . $file;
            if (!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }

    closedir($dh);
    //删除当前文件夹：
    if (rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 获取指定日期段内每一天的日期和天数
 * @param Date $startdate 开始日期 格式化时间 Y-m-d H:i:s
 * @param Date $enddate 结束日期 格式化时间 Y-m-d H:i:s
 * @return Array
 */
function getDateFromRange($startdate, $enddate, $format = 'Y-m-d')
{
    $startdate = date($format . ' 00:00:00', strtotime($startdate));
    $enddate = date($format . ' 23:59:59', strtotime($enddate));
    $stimestamp = strtotime($startdate);
    $etimestamp = strtotime($enddate);
    if ($etimestamp < $stimestamp) return [];
    // 计算日期段内有多少天
    $days = (int)ceil(($etimestamp - $stimestamp) / 86400);
    // 保存每天日期
    $date = array();
    for ($i = 0; $i < $days; $i++) {
        $date[] = date($format, $stimestamp + (86400 * $i));
    }
    $data = [
        'dates' => $date,
        'days' => $days,
    ];
    return $data;
}

/**
 * 获取最近7天日期
 * @param string $time
 * @param string $format
 * @return array
 */
function get7day($time = '', $format = 'Y-m-d')
{
    $time = $time != '' ? $time : time();
    //组合数据
    $date = [];
    for ($i = 0; $i <= 6; $i++) {
        $date[$i] = date($format, strtotime('+' . $i - 7 . ' days', $time));
    }
    return $date;
}

/**
 * @title  二维数组根据某值 排序
 * @param  [type] $arr  [数组]
 * @param  [type] $keys [键名]
 * @param string $type [排序类型]
 * @return [type]       [description]
 */
function array_sort($arr, $keys, $type = 'ASC')
{
    $keysvalue = array();
    $new_array = array();

    foreach ($arr as $k => $v) {
        $keysvalue[$k] = $v[$keys];
    }

    if ($type == 'ASC') {
        asort($keysvalue);
    } else {
        arsort($keysvalue);
    }

    reset($keysvalue);

    foreach ($keysvalue as $k => $v) {
        $new_array[$k] = $arr[$k];
    }

    return $new_array;
}

/**
 * curl get方式
 * @param $url
 * @return bool|string
 */
function http_get($url)
{
    $ch = curl_init();//初始化
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); //允许 cURL 函数执行的最长秒数。
    $SSL = substr($url, 0, 8) == "https://" ? true : false;
    if ($SSL) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名
    }
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

/**
 * curl post方式
 * @param $url
 * @param array $params
 * @return mixed
 */
function http_post($url, $params = [])
{
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, 1);

    //设置post数据
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据

    return json_decode($data, true);
}

/**
 * 生成唯一不重复的TOKEN
 * @return string
 */
function createToken()
{
    //strtoupper转换成全大写的
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    return substr($charid, 0, 8) . substr($charid, 8, 4) . substr($charid, 12, 4) . substr($charid, 16, 4) . substr($charid, 20, 12);
}

/**
 * 多维数组重置索引
 * @param $array
 * @return array
 *
 */
function reform_keys($array)
{
    if (!is_array($array)) {
        return $array;
    }
    $keys = implode('', array_keys($array));
    if (is_numeric($keys)) {
        $array = array_values($array);
    }
    $array = array_map('reform_keys', $array);
    return $array;
}

;
