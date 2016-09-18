<?php
return array(
	"DB_HOST"        =>    "127.0.0.1",     //数据库地址
	"DB_NAME"        =>    "weibo",         //数据库名
	"DB_USER"        =>    "root",          //数据库用户名
	"DB_PWD"         =>    "root",          //数据库密码
	"DB_PREFIX"      =>    "wb_",           //表前缀
	"DB_TYPE"        =>    "mysql",         //指定数据库类型
	"DEFAULT_THEME"  =>    "default",       //默认模板

	"ENCTYPTION_KEY" =>    "286833973",      //用于Cookie加密的字符串
	"AUTO_LOGIN_TIME"=>    time()+3600*24*7, //自等登录的时间
	'VAR_SESSION_ID' =>    'session_id',

    //图片上传配置
	"UPLOAD_MAX_SIZE"=>    2000000,          //最大上传大小
	"UPLOAD_PATH"    =>    './Uploads/',      //上传目录


	//URL路由功能
	'URL_ROUTER_ON'     =>    true,
	//定义路由规则
	'URL_ROUTE_RULES'   =>    array(
		':id\d'         =>    'User/index',
		'follow/:uid\d' =>   array('User/followList','type=1'),
		'fans/:uid\d'   =>   array('User/followList','type=2'),
	),

	//自定义标签
	'APP_AUTOLOAD_PATH' => '@.TagLib',
    'TAGLIB_BUILD_IN'   => 'Cx,Home\TagLib\TagLibMytag' ,

	//开启缓存目录哈希子目录
//	'DATA_CACHE_SUBDIR' => true,
//	'DATA_PATH_LEVEL'   => 2,      //目录层次

    //开启memcache缓存
	'DATA_CACHE_TYPE'   => 'Memcache',
	'MEMCACHE_HOST'     => '127.0.0.1',
	'MEMCACHE_PORT'     =>  11211,


	'LOAD_EXT_CONFIG' =>'system,filtrate',       //加载配置文件


	'TMPL_ACTION_ERROR'     =>  MODULE_PATH.'View/error/error.htm', // 默认错误跳转对应的模板文件
	'TMPL_ACTION_SUCCESS'   =>  MODULE_PATH.'View/error/success.htm' // 默认成功跳转对应的模板文件

);