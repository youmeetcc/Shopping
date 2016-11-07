<?php
return array(

    'URL_MODEL' => 1,
    'SHOW_PAGE_TRACE' => true,

    'ACTION_SUFFIX' => '_action',
    'URL_HTML_SUFFIX' => '',

    'DEFAULT_MODULE'        =>  'Shopping',      // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Shopping',      // 默认控制器名称
    'DEFAULT_ACTION'        =>  'shopping',      // 默认操作名称

    'DB_TYPE' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_USER' => 'root',
    'DB_PWD' => '664198',
    'DB_NAME' => 'Shopping',
    'DB_PORT' => '3306',
    'DB_PREFIX' => 'db_',

    'LANG_SWITCH_ON'   => true,
    'LANG_AUTO_DETECT' => true, 		 // 自动侦测语言 开启多语言功能后有效
	'LANG_LIST'        => 'zh-cn,en-us', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE'     => 'l', 			 // 默认语言切换变量

    'DEFAULT_UPLOADS'        =>  './Uploads/',      // 默认文件上传根目录

);