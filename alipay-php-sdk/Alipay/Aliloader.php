<?php
namespace Alipay;

use Alipay\build\Cache;
/**
 * 支付宝SDK加载器
 */
class Aliloader {
    static protected $config = array();//配置参数
    static protected $cache = array();//对象缓存
    /**
     * 获取支付宝SDK接口对象
     * @param string $type 接口类型
     * @param array $config SDK配置
     */
    static public function & get($type, $config = array()) {
        $index = md5(strtolower($type) . md5(json_encode(self::$config)));
        if (!isset(self::$cache[$index])) {
            $basicName =  ucfirst(strtolower($type));
            $className = "\\Alipay\\{$basicName}";
            self::$cache[$index] = new $className(self::config($config));
        }
        return self::$cache[$index];
    }
    /**
     * 设置配置参数
     * @param array $config
     * @return array
     */
    static public function config($config = array()) {
        !empty($config) && self::$config = array_merge(self::$config, $config);
        if (!empty(self::$config['cachepath'])) {
            Cache::$cachepath = self::$config['cachepath'];
        }
        return self::$config;
    }

}
