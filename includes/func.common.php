<?php

/** 获取$_GET,$_POST,$_COOKIE变量
 *  @param $ket String/Integer 键名
 *  @param $type String 获取的类型 缺省'GP'
 *  @teturn String/NULL
 */
function getVar($key, $type = 'GP')
{
    $type = strtoupper($type);
    switch ($type) {
        case 'G':
            $var = &$_GET;
            break;
        case 'P':
            $var = &$_POST;
            break;
        case 'C':
            $var = &$_COOKIE;
            break;
        default:
            if (isset($_GET[$key])) {
                $var = &$_GET;
            } else {
                $var = &$_POST;
            }
            break;
    }
    return isset($var[$key]) ? $var[$key] : null;
}

/**
 * @param $code Integer 状态码
 * @param $msg String   提示消息
 * @param $data String/Array 传递数据
 * @return void
 */
function apimessage($code = 0, $msg = 'succ', $data = array())
{
    $code = intval($code);
    if (is_array($data) && empty($data)) {
        $data = new stdclass();
    }
    $info = array('code'=>$code,'msg'=>$msg,'data'=>$data);
    @header("Content-type:text/html;charset=utf-8");
    @ob_clean();
    echo json_encode($info, defined("JSON_UNESCAPED_UNICODE") ? JSON_UNESCAPED_UNICODE : 0);
    exit();
}

/** 功能 调试变量(针对浏览器界面友好输出)
 *  @param $var Mixed 要调试的变量
 *  @param $vardump Boolean 是否使用vardump函数输出变量信息 [false:使用print_r输出,true:使用var_dump输出]
 *  @param $exit Boolean 输出完是否终止运行
 *  @teturn void
 */
function sdebug($var = null, $vardump = false, $exit = false)
{
    $sapi_prefix = substr(php_sapi_name(), 0, 3);
    echo $sapi_prefix == 'cli' ? '' : '<pre>';
    if ($vardump) {
        var_dump($var);
    } else {
        print_r($var);
    }
    echo $sapi_prefix == 'cli' ? '' : '</pre>';
    if ($exit) {
        exit();
    }
}

function tname($table, $prefixed = true) {
    return ($prefixed ? TABLE_PREFIX : '') . $table;
}

/** 载入数据
 *  @param void
 *  @return Array array() / array(id1=>array(..), id2=>array(..), ..)
 */
function load_data($name, $refresh = false, $prefix = 'data_')
{
    static $datas = array();
    $arrkey = APP_DATA."/{$prefix}{$name}.php";
    if (!$refresh && isset($datas[$arrkey])) {
        return $datas[$arrkey];
    }
    $array = @cache_read("{$prefix}{$name}.php", APP_DATA);
    $array = is_array($array) ? $array : array();
    $datas[$arrkey] = $array;

    return $array;
}

/** 载入配置
 *  @param void
 *  @return Array array() / array(id1=>array(..), id2=>array(..), ..)
 */
function load_config($name, $refresh = false, $prefix = 'config_')
{
    static $configs = array();
    $arrkey = APP_CONFIG."/{$prefix}{$name}.php";
    if (!$refresh && isset($configs[$arrkey])) {
        return $configs[$arrkey];
    }
    $array = @cache_read("{$prefix}{$name}.php", APP_CONFIG);
    $array = is_array($array) ? $array : array();
    $configs[$arrkey] = $array;

    return $array;
}

if (!function_exists('cache_read')) {
    function cache_read($file, $dir)
    {
        $cachefile = ($dir == '' ? '' : "{$dir}/") . $file;
        return @include $cachefile;
    }
}

/** 写数据
 *  @param $data Array
 *  @param $name String
 *  @param $prefix String
 *  @return boolean(false) / Integer
 */
function write_data ($data, $name, $prefix = 'data_') {
    return file_put_contents(APP_DATA."/{$prefix}{$name}.php", "<?php".PHP_EOL.PHP_EOL."//".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL."return ".var_export($data, true).";".PHP_EOL);
}

/** 写配置
 *  @param $data Array
 *  @param $name String
 *  @param $prefix String
 *  @return boolean(false) / Integer
 */
function write_config ($data, $name, $prefix = 'config_') {
    return file_put_contents(APP_CONFIG."/{$prefix}{$name}.php", "<?php".PHP_EOL.PHP_EOL."//".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL."return ".var_export($data, true).";".PHP_EOL);
}

/** 随机获取数组中指定数量的元素,保留数组的索引关联
 *  @param $arr Array 要获取元素的原始数组
 *  @param $num Integer 随机获取的元素个数
 *  @teturn Array
 */
function sarray_rand($arr, $num = 1)
{
    $r_values = array();
    if ($arr && count($arr) > $num) {
        if ($num > 1) {
            $r_keys = array_rand($arr, $num);
            foreach ($r_keys as $key) {
                $r_values[$key] = $arr[$key];
            }
        } else {
            $r_key = array_rand($arr, 1);
            $r_values[$r_key] = $arr[$r_key];
        }
    } else {
        $r_values = $arr;
    }
    return $r_values;
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}


/**
 *  获取convert命令的二进制路径
 *  因为线上的机器被op装好后没有在 include_path (如 /usr/bin) 目录下建立软连接 所以需要获取convert的二进制路径
 */
function get_convert_binpath()
{
    static $convertBinPath = null;
    if ($convertBinPath !== null) {
        return $convertBinPath;
    }
    $out_arr = array();
    $cmd = "convert -version 2>&1";
    exec($cmd, $out_arr, $out_var);
    if ($out_var === 0) {
        $convertBinPath = "convert";
        return $convertBinPath;
    }
    $convertBinPath = "/usr/local/imagemagick/bin/convert";
    return $convertBinPath;
}

function mkdir_recursive($pathname, $mode = 0755)
{
    is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname), $mode);
    return is_dir($pathname) || @mkdir($pathname, $mode);
}

/** 功能 获取文件名后缀
 *  @param $filename String 带后缀的文件名 必须 如 'data.txt','data.sql'
 *  @return String ''或'sql','txt',...
 */
function fileext($filename)
{
    return strtolower(substr(strrchr($filename, '.'), 1));
}

/** 功能 生成随机字符串
 *  @param $length Integer 生成的随机串的字符长度
 *  @param $numeric Boolean 是否纯数字串 缺省为false [true:是, false:否]
 *  @return String
 */
function random($length, $numeric = false)
{
    $seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}

/** 功能 上传文件至第三方api检测人头数量及坐标并接收检测结果数据
 *  @param $files 文件参数 必须
 *  array(
 *    0 => array(
 *        'fkey' => '', //$_FILES 的key
 *        'fpath' => '',//文件的绝对路径
 *    ),
 *    ...
 *  )
    @return Mixed(Array/Integer)
        Array : 上传完成(不一定成功，需要查看array['code'])
        0 : 文件不存在
        -1 : fopen打开文件失败
        -2 : 初始化curl失败
        -3 : curl_exec失败
        -4 : 返回数据格式不正确(json解码失败)
 */
function upload_img2detector($files = array())
{
    $resu = array(
        'code' => 0,
        'msg' => 'succ',
        'data' => array(),
    );
    if (empty($files)) {
        return array_replace_recursive($resu, array(
            'code' => 1,
            'msg' => "文件为空",
        ));
    }
    $postArgs = array();
    foreach ($files as $key => $file) {
        if (!file_exists($file['fpath'])) {
            return array_replace_recursive($resu, array(
                'code' => 2,
                'msg' => "文件不存在[idx={$key},file={$file['fpath']}]",
            ));
        }
        $postArgs[$file['fkey']] = curl_file_create($file['fpath']);
    }
    $key = VENDOR_API_KEY;//接口key(请求方与接收方一致)
    $postArgs['sign'] = md5('save_file'.$key);
    $postArgs['dosubmit'] = 'true';

    if (false === ($ch = curl_init($url))) {
        return array_replace_recursive($resu, array(
            'code' => 3,
            'msg' => "初始化curl失败",
        ));
    }

    curl_setopt($ch, CURLOPT_URL, 'http://10.141.8.80/AImoji/vendorapi/detector.php');
    //curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/AImoji/src/vendorapi/detector.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ($postArgs));
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); //连接超时
    curl_setopt($ch, CURLOPT_TIMEOUT, 120); //执行超时
    $resp = curl_exec($ch);
    $errno = 0;
    $error = '';
    if (0 !== ($errno = curl_errno($ch))) {
        $error = curl_error($ch);
        curl_close($ch);
        return array_replace_recursive($resu, array(
            'code' => 4,
            'msg' => "curl_exec fail code={$errno}, msg={$error}",
        ));
    }
    curl_close($ch);
    $copy = trim($resp);
    $resp = @json_decode($resp, true);
    if (!is_array($resp) || !isset($resp['code'])) {
        return array_replace_recursive($resu, array(
            'code' => 5,
            'msg' => "响应数据不正确[resp={$copy}]",
        ));
    }
    unset($copy);
    if ($resp['code'] != 0) {
        return array_replace_recursive($resu, array(
            'code' => 6,
            'msg' => "检测图片失败(code={$resp['code']},msg={$resp['msg']})",
            'data' => $resp['data'],
        ));
    }
    return array_replace_recursive($resu, array(
        'code' => 0,
        'msg' => "succ",
        'data' => $resp['data'],
    ));
}

/**
 * [将Base64图片转换为本地图片并保存]
 * @param  [Base64] $base64_image_content [要保存的Base64]
 * @param  [目录] $file [要保存的绝对路径 含文件名及后缀]
 */
function base64_image_content($base64_image_content, $file)
{
    //匹配出图片的格式
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
        $type = strtolower($result[2]);
        $ext = fileext($file);
        if (strlen($ext) > 0) {
            $new_file = $type == $ext ? $file : (str_relpace(".{$ext}", '', $file) . ".{$type}");
        } else {
            $new_file = "{$file}.{$type}";
        }
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
            return $new_file;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function format_sec($sec)
{
    return sprintf('%02d', (int)($sec/3600)).':'.sprintf('%02d', (int)(($sec%3600)/60)).':'.sprintf('%02d', ($sec%60));
}

function format_msec($msec)
{
    $sec = intval($msec);
    $msec = round(1000*$msec) - 1000*$sec;
    return sprintf('%02d', (int)($sec/3600)).':'.sprintf('%02d', (int)(($sec%3600)/60)).':'.sprintf('%02d', ($sec%60)).':'.sprintf('%03d', $msec);
}

/**
 *  功能 目录文件列表(含子目录)
 *  @param $dir String 要list的目录
 *  @param $allpath Boolead 是否返回绝对路径 缺省true [tue:是,false:否(返回相对路径,相对于$dir)]
 *  @param $list Array 引用传值 存放结果列表
 *  @param $ext String 只读取扩展名为$ext的文件 缺省空字符串(读取所有文件)
 *  @param $pdir String 父级目录 缺省空字符串 该参数无需传值
 *  @return Array array() / array(0=>xxx.php,0=>xxx/xxx.txt,...)
 */
function dir_file_list($dir, $allpath = true, $ext = '', $pdir = '')
{
    static $list = array();
    if ($pdir === '') {//用到了静态变量存储目录文件列表,如果不加这一段,外部显式先后多次调用该函数时,后一次调用返回的结果总会包含前一次调用的结果。
        $list = array();
    }
    if ($pdir !== '' && $pdir != './') {
        $pdir .= DIRECTORY_SEPARATOR;
    }
    static $rootpath = '';
    if (@is_dir($dir)) {
        $dir = realpath($dir);
        if (!$allpath && $pdir === '') {
            $rootpath = $dir;
        }
        if (false !== ($handle = @opendir($dir))) {
            while (false !== ($file = readdir($handle))) {
                if (substr($file, 0, 1) != '.') {
                    dir_file_list($dir.DIRECTORY_SEPARATOR.$file, $allpath, $ext, $dir);
                }
            }
            @closedir($handle);
        }
    } else {
        if ($ext === '' || $ext === '*' || ($ext !== '' && fileext($dir) == $ext)) {
            $list[] = ($allpath ? $pdir:str_replace(($rootpath.DIRECTORY_SEPARATOR), '', $pdir)).str_replace($pdir, '', $dir);
        }
    }
    return $list;
}

/**
 *  格式化秒数
 *  @param $sec Integer 格式化的秒数
 *  @return String 如 '00:00:05'
 */
if (!function_exists('format_sec')) {
    function format_sec($sec)
    {
        return sprintf('%02d', (int)($sec/3600)).':'.sprintf('%02d', (int)(($sec%3600)/60)).':'.sprintf('%02d', ($sec%60));
    }
}

/**    功能 上传图片文件至云图
 * @param $filename 图片文件名(全路径) 必须
 * @param $sign 上传后的图片名 必须
 * @param $url 上传请求的url地址 可选 缺省使用 http://innerupload01.picupload.djt.sogou-op.org/http_upload
 * @return Mixed(Array/Integer)
 * Array : 上传完成(不一定成功，需要查看array['status'])
 * 0 : 文件不存在
 * -1 : fopen打开文件失败
 * -2 : 初始化curl失败
 * -3 : curl_exec失败
 * -4 : 返回数据格式不正确(json解码失败)
 */
function upload_cloud($filename, $sign, $url = '')
{
    if (empty($url)) {
        $url = 'http://innerupload.sogou/http_upload?appid=100540022';
    }

    if (!@file_exists($filename)) {
        return 0;
    }
    if (!$fp = @fopen($filename, 'r')) {
        return -1;
    }
    //$fields['f1'] = '@' . $filename;
    //Message: curl_setopt(): The usage of the @filename API for file uploading is deprecated. Please use the CURLFile class instead
    $fields['f1'] = new CURLFile(realpath($filename));
    $fields['sign_f1'] = $sign;
    $size = @filesize($filename);
    if (!$ch = @curl_init()) {
        return -2;
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, $size);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        fclose($fp);
        return -3;
    }
    curl_close($ch);
    fclose($fp);
    $result = @json_decode($result, true);

    if ($result === false || $result === null) {
        return -4;
    }
    return $result;
}

/** 功能 格式化字节大小
 *  @param $size Integer 文件字节数
 *  @return String 如:10.1KB, 0.99MB, ...
 */
function formatsize($size)
{
    $prec=3;
    $size = round(abs($size));
    $units = array(0=>"B", 1=>"KB", 2=>"MB", 3=>"GB", 4=>"TB");
    if ($size==0) {
        return str_repeat(" ", $prec)."0$units[0]";
    }
    $unit = min(4, floor(log($size)/log(2)/10));
    $size = $size * pow(2, -10*$unit);
    $digi = $prec - 1 - floor(log($size)/log(10));
    $size = round($size * pow(10, $digi)) * pow(10, -$digi);
    return $size.$units[$unit];
}

/**
 *  将带单位的字节转换成整数字节
 *  @param $formatsize String 如 1024, 1GB, 2MB 等等
 *  @return integer
 */
function Byte2number($formatsize)
{
    $formatsize = preg_replace('/\s+/', '', trim($formatsize));
    if (is_numeric($formatsize)) {
        return intval($formatsize);
    }
    // 为了简单，这里只将B识别为Byte，不做 bit 处理
    if (0 < preg_match('/^(\d+)(B|M|G|T|Byte|Bytes|KB|MB|GB|TB)/i', $formatsize, $match)) {
        $val = intval($match[1]);
        $unit = strtolower($match[2]);
        $beishu = 1;
        if (in_array($unit, array('b', 'byte', 'bytes'), true)) {
            $beishu = 1;
        } elseif (in_array($unit, array('k', 'kb'), true)) {
            $beishu = 1024;
        } elseif (in_array($unit, array('m', 'mb'), true)) {
            $beishu = 1024 * 1024;
        } elseif (in_array($unit, array('g', 'gb'), true)) {
            $beishu = 1024 * 1024 * 1024;
        } elseif (in_array($unit, array('t', 'tb'), true)) {
            $beishu = 1024 * 1024 * 1024 * 1024;
        }
        return $val * $beishu;
    }
    return intval($formatsize);
}

/** 功能 是否ajax请求
 *  @return Boolean
 */
function isajax()
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        if ('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return true;
        }
    }
    if (!empty($_POST['inajax']) || !empty($_GET['inajax'])) {
        // 判断Ajax方式提交
        return true;
    }
    return false;
}

function __get_http_body($resp)
{
    $sepor = "\r\n\r\n";
    $pos = strpos($resp, $sepor);
    if ($pos === false) {
        return false;
    }
    return substr($resp, $pos + strlen($sepor));
}

function __socket_http_request($url)
{
    $resu = array(
        'code' => 0,
        'msg' => 'succ',
        'data' => array(),
    );
    $urlInfo = parse_url($url);
    $host = $urlInfo['host'];
    $path = $urlInfo['path'];
    $scheme = isset($urlInfo['scheme']) ? strtolower($urlInfo['scheme']) : 'http';
    $port = isset($urlInfo['port']) ? $urlInfo['port'] : ($scheme == 'https' ? 443 : 80);
    $protocol = 'https' == $scheme ? 'ssl://' : '';

    $fp = fsockopen($protocol.$host, $port, $errno, $errstr, 12);//连接超时
    if (!$fp) {
        $resu['code'] = 1;
        $resu['msg'] = "fsockopen fail (errno={$errno}, errstr={$errstr})";
        return $resu;
    }
    stream_set_timeout($fp, 5);//读取超时
    stream_set_blocking($fp, 1);// 0:非阻塞 1:阻塞
    //在非阻塞模式下http请求未closed时，feof($fp)始终为false；
    //在阻塞模式下http请求未closed时，当页面内容没传输完时feof($fp)为false，传输完后feof($fp)为true
    
    $http = "GET {$path} HTTP/1.1\r\n";
    $http .= "Host: {$host}\r\n";
    $http .= "User-Agent: okhttp/3.10.0\r\n";
    //$http .= "Connection: Keep-Alive\r\n";
    $http .= "Connection: Closed\r\n";
    $http .= "\r\n";
    print_r($http);
    if (false === fwrite($fp, $http)) {
        $resu['code'] = 2;
        $resu['msg'] = "fwrite fail";
        return $resu;
    }
    $resp = '';
    while (!feof($fp)) {
        $resp .= fgets($fp, 128);
    }
    $info = stream_get_meta_data($fp);
    fclose($fp);
    
    $resu['code'] = 0;
    $resu['msg'] = 'succ';
    $resu['data']['resp'] = $resp;
    $resu['data']['info'] = $info;
    return $resu;
}

/** 版本格式化 如 当seg=4 bits=4 时，将 8.11.3 格式化为 8.0011.0003.0; 当seg=3 bits=4时，将 8.11.3 格式化为 8.0011.0003
 *  @param $num Integer/String 整型数字或数字型字符串
 *  @param $seg Integer 区段数
 *  @param $bits 每个区段的位数
 *  @return String
 */
function version_format($ver, $seg = 4, $bits = 4)
{
    if (empty($ver)) {
        return '0.0000.0000.0';
    }
    $ver = trim($ver, '.');
    $arr = array();
    if (false !== strpos($ver, '.')) {
        $arr = explode('.', $ver);
    } else {
        $arr[] = $ver;
    }
    $count = count($arr);
    if ($count < $seg) {
        for ($idx = 0; $idx < $seg - $count; $idx++) {
            $arr[] = '0';
        }
    } else {
        $arr = array_slice($arr, 0, $seg);
    }
    $str = $comma = '';
    foreach ($arr as $idx => $val) {
        $str .= "{$comma}".number_buwei($val, $bits, $idx==0?true:false, $idx>=$seg-1?true:false);
        $comma = '.';
    }
    return $str;
}

/** 数字左补位 如 当bits=4 ishead=false istail=false时 0 格式化为 0000, 1 格式化为 0001 12 格式化为 0012
 *  @param $num Integer/String 整型数字或数字型字符串
 *  @param $bits Integer 补位数
 *  @param $ishead Boolean 是否放在段位头部
 *  @param $istail Boolean 是否放在段位尾部
 *  @return String
 */
function number_buwei($num, $bits = 4, $ishead = false, $istail = false)
{
    $strlen = strlen($num);
    if ($strlen > $bits) {
        return substr($num, 0, $bits);
    } else {
        if ($ishead || ($num == 0 && $istail)) {
            return "{$num}";
        } else {
            return str_repeat('0', $bits-$strlen)."{$num}";
        }
    }
}

/** 过滤id字符串
 *  @param $idstr String 要过滤的字符串
 *  @param $return_arr Boolean 是否返回数组 缺省false
 *  @param $int_id Boolean 是否为整型id 缺省true
 *  @param $no_repeat Boolean 是否去重 缺省true
 *  @param $sep String 要过滤的字符串的分割符 缺省为英文半角逗号
 *  @teturn String/Array
 */
function filter_idstr($idstr, $return_arr = false, $int_id = true, $no_repeat = true, $sep = ',')
{
    $idstr = preg_replace("/\r|\n|\s/", '', trim($idstr, $sep));//处理为规范的格式
    $idstr = preg_replace("/[，,]+/i", ',', $idstr);//处理英文半角逗号及中文全角逗号
    if ($idstr === '') {
        return $return_arr ? array() : $idstr;
    }
    //过滤无效
    $filter = array();//去重
    $idstr = explode($sep, $idstr);
    foreach ($idstr as $key => $id) {
        if ($no_repeat) {
            if (in_array($id, $filter, !0)) {
                unset($idstr[$key]);
                continue;
            }
            $filter[] = $id;
        }
        if ($int_id) {
            $idstr[$key] = intval($id);
            if ($idstr[$key] < 1) {
                unset($idstr[$key]);
                continue;
            }
        } else {
            $idstr[$key] = trim($id);
        }
    }
    return $return_arr ? $idstr : implode($sep, $idstr);
}

/** 功能 转换时间戳,将 $daytime 转换为 时间戳(整数)或者完整的时间字符串:2014-08-22 17:50
 *  @param $daytime String '2014-08-22' 或 '2014-08-22 17:50' 或 '2014-08-22 17:50:00'
 *  @param $return_timestamp Boolean 是否返回时间戳 [true:返回时间戳,false:返回完整的时间字符串]
 *  @return Integer/String
 */
function daytime($daytime, $return_timestamp = false)
{
    $ret = $return_timestamp ? 0 : '';
    $daytime = trim($daytime);
    if (empty($daytime)) {
        return $ret;
    }
    $preg_daytime = "/^[1-9]\d{3}-\d{1,2}-\d{1,2}( \d{1,2}:\d{1,2}(:\d{1,2})?)?$/";
    if (!preg_match($preg_daytime, $daytime)) {
        return $ret;
    }
    $daytime = strtotime($daytime);
    if ($daytime === false) {
        return $ret;
    }
    return $return_timestamp ? $daytime : date('Y-m-d H:i:s', $daytime);
}

/** 功能 交换时间戳,将 $daytime1与$daytime2按大小互换,结果是 $daytime1<=$daytime2
 *  @param $daytime1 String/Integer '2014-08-22' 或 '2014-08-22 17:50' 或 '2014-08-22 17:50:00' 或 1345878951
 *  @param $daytime2 String/Integer 同$daytime1
 *  @return Boolean(true)
 */
function daytime_swap(&$daytime1, &$daytime2)
{
    if ($daytime1 > $daytime2) {
        $tmp = $daytime1;
        $daytime1 = $daytime2;
        $daytime2 = $tmp;
    }
    return true;
}

/**
 * @param $timestamp Integer 13位整型时间戳
 */
function format_daytime_milli($timestamp)
{
    return date(TIME_SHOW_FORMAT, intval($timestamp/1000)) . ':' . sprintf("%03d", $timestamp%1000);
}

/** 功能 检测字符串是否存在
 *  @param $haystack String 被查找的字符串
 *  @param $needle String 要查找的字符串
 *  @param $case Boolean 大小写敏感 缺省true(大小写敏感)
 *  @return Boolean 存在则返回true 不存在则返回false
 */
function strexists($haystack, $needle, $case = true)
{
    return $case ?  !(strpos($haystack, $needle) === false) : !(stripos($haystack, $needle)===false);
}

/** 获取php命令行参数的day(统计脚本内部用)
 *  @return Array array() / array('20160808') / array('20160808','20160809') ..
 *  useage:
        php xxx.php 3 取今天开始倒数3天
        php xxx.php 20160824 20160825 取这2天
        php xxx.php 20160824,20160825 20160826 取这3天
        php xxx.php 20160824,20160825 20160826,20160827 取这4天
 */
if (!function_exists('get_argv_days')) {
    function get_argv_days()
    {
        global $argv;

        $days = array();

        if (count($argv) > 1) {
            if (strlen($argv[1]) < 8 && preg_match('/^\d+$/', $argv[1])) {
                for ($i=intval($argv[1]); $i>0; $i--) {
                    $days[] = date('Ymd', time()-86400*$i);
                }
                return $days;
            }

            foreach ($argv as $key => $arg) {
                if ($key == 0) {
                    continue;
                }
                if (strpos($arg, ',') !== false) {
                    $arg = explode(',', $arg);
                    foreach ($arg as $k => $val) {
                        if (strlen($val) != 8 || strtotime($val) === false) {
                            unset($arg[$k]);
                        }
                    }
                } else {
                    if (strlen($arg) != 8 || strtotime($arg) === false) {
                        $arg = array();
                    } else {
                        $arg = array($arg);
                    }
                }
                $days = array_merge($days, $arg);
            }
        }

        return empty($days) ? array(date('Ymd', time()-86400)) : $days;
    }
}

/** 获取php命令行参数的dayhour(统计脚本内部用)
 *  @return Array array() / array('2016080808') / array('2016080808','2016080908') ..
 *  useage:
        php xxx.php 2016082400
        php xxx.php 2016082400,2016082401 2016082403 取这3个小时
 */
if (!function_exists('get_argv_dayhours')) {
    function get_argv_dayhours()
    {
        global $argv;

        $dayhours = array();

        if (count($argv) > 1) {
            foreach ($argv as $key => $arg) {
                if ($key == 0) {
                    continue;
                }
                if (strpos($arg, ',') !== false) {
                    $arg = explode(',', $arg);
                    foreach ($arg as $k => $val) {
                        if (strlen($val) != 10 || strtotime(substr($val, 0, 8)) === false) {
                            unset($arg[$k]);
                        }
                    }
                } else {
                    if (strlen($arg) != 10 || strtotime(substr($arg, 0, 8)) === false) {
                        $arg = array();
                    } else {
                        $arg = array($arg);
                    }
                }
                $dayhours = array_merge($dayhours, $arg);
            }
        }

        if (empty($dayhours)) {
            $dayhours[] = date('YmdH', time()-3600);
        }

        return $dayhours;
    }
}

function get_tag_map($tagconf)
{
    $tagmap = array();
    foreach ($tagconf as $item) {
        $tagmap[$item['name']] = $item;
    }
    return $tagmap;
}

/**	由数组元素生成连接字符串
 *	@param $arr Array 数组
 *	@param $sep String 用于连接数组元素的连接符
 *	@param $wrap String 数组元素修饰符 可能的值[', ", `, ...]
 *	@teturn String/Integer(0)
 */
if(!function_exists('simplode')) {
function simplode($arr, $sep = ',', $wrap = '\'') {
    $arr = is_array($arr) ? $arr : (array)$arr;
    if(!empty($arr)) {
        return $wrap.implode($arr, $wrap.$sep.$wrap).$wrap;
    } else {
        return 0;
    }
}
}

/**	功能 处理搜索关键字
 *	@param $string String 要搜索的关键字
 *	@return String
 */
function stripsearchkey($string) {
	$string = trim($string);
	$string = str_replace('*', '%', addcslashes($string, '%_'));
	$string = str_replace('_', '\_', $string);
	return $string;
}

/**    功能 检查$email是否为邮箱地址
 * @param $email String 要检查的邮箱名
 * @return Boolean
 */
function isemail($email)
{
    return strlen($email) > 6 && strlen($email) <= 32 && preg_match("/^([A-Za-z0-9\-_.+]+)@([A-Za-z0-9\-]+[.][A-Za-z0-9\-.]+)$/", $email);
}

function isip($ip)
{
    return preg_match('/\d{1,3}(\.\d{1,3}){3}/', $ip) ? true : false;
}

/** 功能 加密解密相关
 *  @param $string String 要加密或解密的串
 *  @param $operation 操作类型(加密/解密) 可选 缺省'DECODE' ['DECODE':解密, 'ENCODE':加密]
 *  @param $key 加密或解密的密钥(该值为''时,使用系统内置的密钥进行加密或解密) 可选 缺省''
 *  @param $expiry 明文密钥有效期 单位:秒 可选 缺省0(密钥永久有效) $expiry>0时,密钥的有效期为$expiry秒,超过$expiry秒后,解密函数返回空字符串(即解密不出结果)
 *  @return String
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;
    $key = md5($key != '' ? $key : APP_AUTHKEY);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}

/** 功能 设置站点cookie
 *  @param $var String cookie变量名
 *  @param $value String cookie变量值 可选 缺省''
 *  @param $life Integer cookie生存时间 单位:秒 可选 缺省0
 *  @param $httponly Boolean 设定是否禁止客户端操作该cookie 可选 缺省false [true:是, false:否]
 *  @return void
 */
function ssetcookie($var, $value = '', $life = 0, $path = '/', $domain = COOKIE_DOMAIN, $httponly = false)
{
    $prefix = '';
    if (defined('COOKIE_PREFIX')) {
          $prefix = COOKIE_PREFIX;
    }
    $var = $prefix.$var;
    $_COOKIE[$var] = $value;

    if (strlen($value) == 0 || $life < 0) {
        $value = '';
        $life = -1;
    }

    $life = $life > 0 ? APP_START_TIME + $life : ($life < 0 ? APP_START_TIME - 31536000 : 0);
    $path = empty($path) || $path == '/' ? ($httponly && PHP_VERSION < '5.2.0' ? '/; HttpOnly' : '/') : $path;

    $secure = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
    if (PHP_VERSION < '5.2.0') {
        return setcookie($var, $value, $life, $path, $domain, $secure);
    } else {
        return setcookie($var, $value, $life, $path, $domain, $secure, $httponly);
    }
}

/** 功能 获取站点cookie
 *  @param $var String/NULL cookie变量名
 *  @return mixed cookie的var不存在时返回null
 */
function sgetcookie($var = null)
{
    if ($var === null) {
        return $_COOKIE;
    }
    $prefix = '';
    if (defined('COOKIE_PREFIX')) {
          $prefix = COOKIE_PREFIX;
    }
    $var = $prefix.$var;
    if (isset($_COOKIE[$var])) {
          return $_COOKIE[$var];
    }
    return null;
}

/** 功能 获取client ip
 *  @param $only_clientip Boolean 缺省false
    该参数的作用 用户使用代理访问时,取到的可能是多个ip: ip1,ip2,... 当 only_clientip=ture时仅仅返回ip1(clientip), 否则返回全部ip
 *  @return String
 */
function get_clientip($only_clientip = false)
{
    $onlineip = '';
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $onlineip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $onlineip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $onlineip = $_SERVER['REMOTE_ADDR'];
    }
    if ($onlineip && $only_clientip && strpos($onlineip, ',') !== false) {
        $onlineip = substr($onlineip, 0, strpos($onlineip, ','));
    }
    //检查是否为ip
    if (!preg_match('/^\d{1,3}(\.\d{1,3}){3}(\s*,\s*\d{1,3}(\.\d{1,3}){3})*$/i', $onlineip)) {
        $onlineip = '';
    }
    return $onlineip;
}

/*
 * 由于 一般在PHP5.4中对json中中文转码直接用了json_encode($data,JSON_UNESCAPED_UNICODE)
 * 而 php5.3中 JSON_UNESCAPED_UNICODE 这个值是不存在的 所以要自己写一个兼容函数
 * 对变量进行 JSON 编码
 * @param mixed value 待编码的 value ，除了resource 类型之外，可以为任何数据类型，该函数只能接受 UTF-8 编码的数据
 * @return string 返回 value 值的 JSON 形式
 * */
function json_encode_ex( $value)
{
    // version_compare(PHP_VERSION,'5.4.0','<')
    if (!defined("JSON_UNESCAPED_UNICODE"))
    {
        $str = json_encode($value);
        $str =  preg_replace_callback(
            "#\\\u([0-9a-f]{4})#i",
            function( $matchs)
            {
                return  iconv('UCS-2BE', 'UTF-8',  pack('H4',  $matchs[1]));
            },
            $str
        );
        return  $str;
    } else {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}

function generate_table_conf($tname, $prefixed = true) {
    $table_conf = load_config('tables');
    $fields = get_table_conf($tname, $prefixed);
    $table_conf[tname($tname, $prefixed)] = $fields;
    return write_config($table_conf, 'tables');
}

function get_table_conf($tname, $prefixed = true) {
    $fields = array();
    $sql = "DESC " . tname($tname, $prefixed);
    $db = mysqliUtil::getInstance('master_db');
    $query = $db->query($sql);
    while ($line = $db->fetch_array($query)) {
        $fields[$line['Field']] = get_mysql_field_type($line['Type']);
    }
    return $fields;
}
/**
 * @param $type mysql desc table 的 Type 字段的值
数值类型
TINYINT
SMALLINT
MEDIUMINT
INT / INTEGER
BIGINT
FLOAT
DOUBLE
DECIMAL

日期和时间类型
DATE
TIME
YEAR
DATETIME
TIMESTAMP

字符串类型
CHAR
VARCHAR
TINYBLOB
TINYTEXT
BLOB
TEXT
MEDIUMBLOB
MEDIUMTEXT
LONGBLOB
LONGTEXT
 * @return String i / d / s / b
 */
function get_mysql_field_type ($type) {
    $types = array(
        'TINYINT' => 'i',
        'SMALLINT' => 'i',
        'MEDIUMINT' => 'i',
        'INT' => 'i',
        'BIGINT' => 'i',
        'FLOAT' => 'd',
        'DOUBLE' => 'd',
        'DECIMAL' => 'd',

        'DATE' => 's',
        'TIME' => 's',
        'YEAR' => 's',
        'DATETIME' => 's',
        'TIMESTAMP' => 's',

        'CHAR' => 's',
        'VARCHAR' => 's',
        'TINYBLOB' => 'b',
        'TINYTEXT' => 's',
        'BLOB' => 'b',
        'TEXT' => 's',
        'MEDIUMBLOB' => 'b',
        'MEDIUMTEXT' => 's',
        'LONGBLOB' => 'b',
        'LONGTEXT' => 's',
    );
    $tp = '';
    foreach ($types as $key => $val) {
        $len = strlen($key);
        if (strtolower($key) == strtolower(substr($type, 0, $len))) {
            $tp = $val;
            break;
        }
    }
    if (strlen($tp) < 1) {
        if (false !== stripos($type, 'blob')) {
            $tp = 'b';
        }
        if (false !== stripos($type, 'int')) {
            $tp = 'i';
        }
    }
    if (strlen($tp) < 1) {
        echo __FUNCTION__ . " return err not match mysql field type" . APP_BR;
        exit;
    }
    return $tp;
}
