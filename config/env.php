<?php
/**
 * 环境相关常量配置，该配置不同步生产，需管理员手动设置，使用规则如下：
 *
 * 1、尽可能不使用常量定义，仅当开发/生产无法保持一致，允许两类定义：
 *     1) 和调试相关的定义，比如开启调试模式，开启缓存模式，便于本地开发
 *     2) redis相关配置
 * 2、其余的配置，如通信授权秘钥，路径，等生产/本地可保持一致的配置，配置到myconfig/目录，并用C()方法引用
 */

//define('DEBUG', 2); //调试用
define('CACHE_DATA', true); //启用DATA缓存模块

//自定义系统变量，MY_打头
define('MY_CACHE_OCP', true); //动态压缩图片，缓存压缩结果
define('MY_ENV', 'DEV'); //当前环境
define('MY_HOMEPAGE_URL', 'http://h5.duo.com:8080'); //后台首页
define('MY_STATIC_URL', 'http://www.duo.com'); //静态CDN域名
define('MY_STATIC_TIME', '20140715'); //静态资源时间戳
define('MY_DEFAULT_ERROR_URL', MY_HOMEPAGE_URL.'/error.html');
define('MY_DEBUG_PAY_SUCC', true); //调试模拟打款成功

//redis数据源配置，REDIS打头，格式：IP:PORT:DATABASE
define('REDIS_SESSION', '127.0.0.1:6379'); //session专用
define('REDIS_CACHE', '127.0.0.1:6379:1'); //默认redis数据源，仅作被动缓存，有可能丢失数据
define('REDIS_DATABASE', '127.0.0.1:6379:2'); //db级别redis，使用aof保存数据
?>