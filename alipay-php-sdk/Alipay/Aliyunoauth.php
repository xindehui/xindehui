<?php
namespace Alipay;

/**
 * 支付宝
 * Class AliPay
 */
class Aliyunoauth {
    public $config = array();
        /** 执行错误消息及代码 */
    public $errMsg;
    public $errCode;
   const gateUrl  = 'https://openapi.alipay.com/gateway.do';
    /**
     * 构造方法
     * @param array $options
     */
    public function __construct($config = array()) {
        $this->config = $config;
    }
        /**
     * Oauth请求Authorize访问地址 二维码登录
     * @param string $callback 授权回跳地址
     * @param string $state为重定向后会带上state参数（填写a-zA-Z0-9的参数值，最多128字节）
     * @param string $scopes 授权类类型接口权限值，目前只支持auth_user和auth_base两个值。
     * @return string
     */
    public function getOauth($callback, $state = '', $scopes='auth_base') {
        //Oauth 标准参数
        // $data['scopes']=$scopes;
        // $data['state']=$state;
        $redirect_url = urlencode($callback);
        // $params['app_id']     = $this->config['app_id'];  //支付宝分配给开发者的应用ID
        // $params['method']     = 'alipay.user.info.auth';//接口名称
        // $params['format']     = $this->config['format']; //仅支持JSON
        // $params['timestamp']  = date("Y-m-d H:i:s");
        // $params['redirect_uri']  = $redirect_url;
        // $params['charset']    = $this->config['input_charset'];//商户网站使用的编码格式，如UTF-8、GBK、GB2312等。
        // $params['version']    ='1.0';
        // $params['biz_content'] =json_encode($data,JSON_UNESCAPED_UNICODE);
        // $params['sign_type']  = $this->config['sign_type'];//DSA、RSA、MD5三个值可选，必须大写
        // $params['sign'] = sign(getSignContent($params),$this->config['sign_type'],$this->config['PrivateKeyFilePath']);
        // return self::gateUrl. '?' . http_build_query($params);
       return "https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id={$this->config['app_id']}&scope={$scopes}&state={$state}&redirect_uri={$redirect_url}";
    }
    	   /**
     * 通过 auth_code 获取 AccessToken 和 openid
     * * 刷新access token并续期
     * *@param string $grant_type 值为authorization_code时，代表用code换取；值为refresh_token时，代表用refresh_token换取
     * @return bool|array
     */
    public function getOauthAccessToken($grant_type='authorization_code',$refresh_token='') {
        $auth_code = isset($_GET['auth_code']) ? $_GET['auth_code'] : '';
        if (empty($auth_code)) {
            return false;
        }
        $params['app_id']     = $this->config['app_id'];  //支付宝分配给开发者的应用ID
        $params['method']     = 'alipay.system.oauth.token';//接口名称
        $params['format']     = $this->config['format']; //仅支持JSON
        $params['timestamp']  = date("Y-m-d H:i:s");
        $params['charset']    = $this->config['input_charset'];//商户网站使用的编码格式，如UTF-8、GBK、GB2312等。
        $params['version']    ='1.0';
        $params['grant_type'] = $grant_type;
        $params['code'] =$auth_code;
        $params['refresh_token'] =$refresh_token;
        $params['sign_type']  = $this->config['sign_type'];//DSA、RSA、MD5三个值可选，必须大写
        $params['sign'] = sign(getSignContent($params),$this->config['sign_type'],$this->config['PrivateKeyFilePath']);

        $result = httpGetResponse(self::gateUrl. '?' . http_build_query($params));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['error_response'])) {
                $this->errCode = $json['error_response']['code'];
                $this->errMsg = $json['error_response']['sub_msg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 获取授权后的用户资料
     * @param string $access_token
     * @param string $openid
     * @return bool|array {openid,nickname,sex,province,city,country,headimgurl,privilege,[unionid]}
     */
    public function getOauthUserInfo($auth_token) {
        $params['app_id']     = $this->config['app_id'];  //支付宝分配给开发者的应用ID
        $params['method']     = 'alipay.user.info.share';//接口名称
        $params['format']     = $this->config['format']; //仅支持JSON
        $params['timestamp']  = date("Y-m-d H:i:s");
        $params['charset']    = $this->config['input_charset'];//商户网站使用的编码格式，如UTF-8、GBK、GB2312等。
        $params['version']    ='1.0';
        $params['auth_token'] = $auth_token;
        $params['sign_type']  = $this->config['sign_type'];//DSA、RSA、MD5三个值可选，必须大写
        $params['sign'] = sign(getSignContent($params),$this->config['sign_type'],$this->config['PrivateKeyFilePath']);
        $result = httpGetResponse(self::gateUrl. '?' . http_build_query($params));
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['error_response'])) {
                $this->errCode = $json['error_response']['code'];
                $this->errMsg = $json['error_response']['sub_msg'];
                return false;
            }
            return $json;
        }
        return false;   
     }
}