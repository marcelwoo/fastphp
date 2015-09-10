<?php
/**
 * FastPHP核心框架
 */
class Fast {

    // 运行程序
    function run() {
        spl_autoload_register(array($this, 'loadClass'));
        $this->setReporting();
        $this->removeMagicQuotes();
        $this->unregisterGlobals();
        $this->callHook();
    }

    // 检测开发环境
    function setReporting() {
        if (APP_DEBUG == true) {
            error_reporting(E_ALL);
            ini_set('display_errors','On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors','Off');
            ini_set('log_errors', 'On');
            ini_set('error_log', RUNTIME_PATH. 'logs/error.log');
        }
    }
     
    // 删除敏感字符
    function stripSlashesDeep($value) {
        $value = is_array($value) ? array_map('stripSlashesDeep', $value) : stripslashes($value);
        return $value;
    }

    // 检测敏感字符并删除 
    function removeMagicQuotes() {
        if ( get_magic_quotes_gpc() ) {
            $_GET = stripSlashesDeep($_GET );
            $_POST = stripSlashesDeep($_POST );
            $_COOKIE = stripSlashesDeep($_COOKIE);
            $_SESSION = stripSlashesDeep($_SESSION);
        }
    }
     
    // 检测自定义全局变量（register globals）并移除
    function unregisterGlobals() {
        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
           foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }
     
    // 主请求方法，主要目的是拆分URL请求
    function callHook() {

        //获取 URL地址
        $url = isset($_GET['url']) ? $_GET['url'] : '';
     
        $urlArray = array();
        
        // 获取控制器名
        $urlArray = explode("/",$url);
        $controller = $urlArray[0];
        
        // 获取动作名
        array_shift($urlArray);
        if ( !empty($urlArray) && !isset($urlArray)){
            $action = $urlArray[0];
        } else {
            $action = 'index';
        }
 //       $action = $urlArray[0]=='' ? 'index': $urlArray[0];
        
        // 获取URL参数
        array_shift($urlArray);
        $queryString = $urlArray;
       
        // 自动转换成对应控制器名
        if ($controller){
            $controllerName = $controller;
        } else{
            $controller = 'index';
            $controllerName = 'index';
            $action = 'index';
        }
        $controller = ucwords($controller);
        $controller .= 'Controller';

        // 实例化控制器
        $int = new $controller($controllerName, $action);
    
        // 如果控制器存在 $action 动作，这调用并传入URL参数
        if ((int)method_exists($controller, $action)) {
            call_user_func_array(array($int, $action), $queryString);
        } else {
            /* 错误代码 */
        }
    }
     
    /** 自动加载控制器和模型类 **/
     
     static function loadClass($class) {
        $frameworks = ROOT . $class . EXT;
        $controllers = APP_PATH . 'controllers/' . $class . EXT;
        $models = APP_PATH . 'models/' . $class . EXT;

        if (file_exists($frameworks)) {
            // 加载框架核心类
            include $frameworks;
        } elseif (file_exists($controllers)) {
            // 加载应用控制器类
            include $controllers;
        } elseif (file_exists($models)) {
            //加载应用模型类
            include $models;
        } else {
            /* 错误代码 */
        }
    }

}
