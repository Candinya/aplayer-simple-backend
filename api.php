<?php
/****************选项设置部分开始****************/
// 网易云音乐需要使用的cookie，为防止公开cookie被
// 封锁导致的请求异常，请您自行采集需要使用的cookie
$nmCookie = 'info=please_set_your_own_cookies;';

// 是否开启白名单模式
$whiteListMode = true;

// 白名单模式下允许的跨域来源，请设置你的来源，或者关闭白名单模式
$allowedOrigins = array('https://candinya.com', 'http://localhost:4000');

/****************选项设置部分结束****************/

/****************环境准备部分开始****************/
// 默认全部放行，以便于消息传递
header('Access-Control-Allow-Origin: *');

// 白名单模式时，未进入白名单的直接拒绝
if ($whiteListMode && isset($_SERVER['HTTP_ORIGIN']) && !in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    http_response_code(403);
    // 直接die掉，避免资源浪费
	die('Not in the whitelist');
}

// 获取加载环境
require 'vendor/autoload.php';
// 使用Meting框架
use Metowolf\Meting;

// 定义一个aplayer使用的曲目对象
class AplayerSong {
    public $name;
    public $artist;
    public $url;
    public $cover;
    public $lrc;
}
/****************环境准备部分结束****************/

/****************参数传递部分开始****************/
// 读取来源平台
$platform = $_GET['p'];
switch ($platform) {
    case 'netease':
    case 'n':
        $api = new Meting('netease');
        // 网易云音乐需要特殊设置cookie
        $api->cookie($nmCookie);
    break;

    case 'tencent':
    case 't':
        $api = new Meting('tencent');
    break;

    case 'xiami':
    case 'x':
        $api = new Meting('xiami');
    break;

    case 'kugou':
    case 'k':
        $api = new Meting('kugou');
    break;

    case 'baidu':
    case 'b':
        $api = new Meting('baidu');
    break;

    default:
        http_response_code(404);
        die('错误的平台代号，请参阅文档检查是否存在设置错误');
}

// 确保输出的内容格式化
$api->format(true);

// 要用来做什么呢
$method = $_GET['m'];
switch ($method) {
    // 为Aplayer提供一个歌单/专辑里所有的曲目信息
    case 'ap': 
        $data = dataForAplayer($api, $_GET['t'], $_GET['id']);
        $retType = 'application/javascript';   // 返回数据的类型
    break;

    case 'lrc':     // 获取歌词文件
        $data = json_decode($api->lyric($_GET['sid']))->lyric;
        $retType = 'text/plain';
    break;

    case 'pic':     // 获取专辑图片
        $data = json_decode($api->pic($_GET['sid']))->url;
        $retType = '302';
    break;

    case 'url':     // 获取歌曲文件链接
        $data = json_decode($api->url($_GET['sid']))->url;
        $retType = '302';
    break;

    default:
        http_response_code(403);
        die('错误的操作，请参阅文档检查是否存在设置错误');
    
}

// 定义一个用于处理Aplayer参数的函数
function dataForAplayer($api, $idType, $idVal) {
    switch($idType) {
        case 'album':   // 获取一份专辑的详细信息
        case 'a':
            $data = $api->album($idVal);  // Album ID
        break;

        case 'playlist':   // 获取一个歌单的详细信息
        case 'plist':
        case 'p':
            $data = $api->playlist($idVal);  // Play List ID
        break;

        default:
            return '错误的操作，请参阅文档检查是否存在设置错误';
    }

    // 获取了信息之后进行解码
    $songsData = json_decode($data);
    $songsCount = count($songsData);
    $songsArray = array($songsCount);
    $reqUri = dirname((isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI']);
    for ($i = 0; $i < $songsCount; $i++) {
        $songsArray[$i] = new AplayerSong;
        $songsArray[$i]->name = $songsData[$i]->name;
        $songsArray[$i]->artist = $songsData[$i]->artist[0];
        {
            $dynamicPath = '/'.basename($_SERVER['SCRIPT_NAME']).'?p='.$songsData[$i]->source;
            $songsArray[$i]->url = $reqUri.$dynamicPath.'&m=url&sid='.$songsData[$i]->url_id;
            $songsArray[$i]->cover = $reqUri.$dynamicPath.'&m=pic&sid='.$songsData[$i]->pic_id;
            $songsArray[$i]->lrc = $reqUri.$dynamicPath.'&m=lrc&sid='.$songsData[$i]->lyric_id;
        }
    }
    return json_encode($songsArray);
}
/****************参数传递部分结束****************/

/****************结果返回部分开始****************/
if ($retType === '302') {
    header('Location: '.$data, true, 302);
} else {
    header('Content-Type:'.$retType.'; charset=utf-8');
    echo $data;
}
/****************结果返回部分结束****************/

exit();

?>
