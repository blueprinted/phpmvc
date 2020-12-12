<?php

if (!defined('APP_LOG')) {
    define('APP_LOG', '/search/odin/devdirs/applogs/logs');
}

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

/** logLevel
调试/DEBUG (100): 详细的调试信息。
信息/INFO (200): 有意义的事件，比如用户登录、SQL日志。
提示/NOTICE (250): 正常但是值得注意的事件。
警告/WARNING (300): 异常事件，但是并不是错误。比如使用了废弃了的API，错误地使用了一个API，以及其他不希望发生但是并非必要的错误。
错误/ERROR (400): 运行时的错误，不需要立即注意到，但是需要被专门记录并监控到。
严重/CRITICAL (500): 边界条件/危笃场景。比如应用组件不可用了，未预料到的异常。
警报/ALERT (550): 必须立即采取行动。比如整个网站都挂了，数据库不可用了等。这种情况应该发送短信警报，并把你叫醒。
紧急/EMERGENCY (600): 紧急请求：系统不可用了。
*/
/** referer https://www.jianshu.com/p/b99dc5c3b760
 * 使用 monolog 打印业务日志 需要提前引入monolog 建议不要直接使用这个函数可以在各自的业务内将这函数进行封装
 * 日志示例: [2020-03-24 15:28:17 UTC] AImoji.DEBUG: apicode=12 grpc_timeused=0.008188009262085 total_timeused=0.0096249580383301 tplid=54 headnum=1 userImageSize=9614 userImageSizeFormat=9.39KB resizeCounter=0 code=2 msg=grpc response code=14, details=Name resolution failure File /search/odin/nginx/html/dt_pinyin/ac/AImoji/merge.php Line 318 [] []
 * @param $logMsg String 日志的内容
 * @param $logLevel String 日志的等级 不区分大小写 DEBUG / INFO / NOTICE / WARNING / ERROR / CRITICAL / ALERT / EMERGENCY
 * @param $channel String 日志的通道名称 用于区分日志是来自什么应用
 * @param $context Array 日志的上下文
 * @param $extra Aray / Function(Closure) 日志的额外信息 如果是 Closure 函数的定义为 function ($record) { $record['extra'] = ...; return $record; }
 */
function monolog_appendlog($logMsg, $logLevel, $channel, $context = array(), $extra = array()) {
    $logLevel = strtolower($logLevel);
    $logDir = APP_LOG;
    $logFile = 'app_daily.log';
    // 定义日期格式
    $dateFormat = "Y-m-d H:i:s";
    // 定义输出格式
    $output = "[%datetime% " . date_default_timezone_get() . "] %channel%.%level_name%: %message% %context% %extra%" . PHP_EOL;
    // 创建一个格式化器
    $formatter = new LineFormatter($output, $dateFormat);

    // 创建日志服务
    $logger = new Logger($channel);

    // 添加一些处理器
    $stream = new StreamHandler($logDir.'/'.$logFile, Logger::INFO);
    $stream->setFormatter($formatter);
    $logger->pushHandler($stream);

    if ($extra instanceof Closure) {
        $logger->pushProcessor($extra);
    }

    // 现在就可以用日志服务了
    if (mkdir_recursive($logDir, 0775)) {
        if ($logLevel == 'debug') {
            $result = $logger->debug($logMsg);
        } elseif ($logLevel == 'info') {
            $result = $logger->info($logMsg);
        } elseif ($logLevel == 'notice') {
            $result = $logger->notice($logMsg);
        } elseif ($logLevel == 'warning') {
            $result = $logger->warning($logMsg);
        } elseif ($logLevel == 'error') {
            $result = $logger->error($logMsg);
        } elseif ($logLevel == 'critical') {
            $result = $logger->critical($logMsg);
        } elseif ($logLevel == 'alert') {
            $result = $logger->alert($logMsg);
        } elseif ($logLevel == 'emergency') {
            $result = $logger->emergency($logMsg);
        } else {
            $result = $logger->debug($logMsg);
        }
        return $result;
    }

    return false;
}
