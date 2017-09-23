<?php
namespace Alipay;
/* *
 * 类名：AlipayNotify
 */
class Alipaynotify
{
    /**
     * HTTPS形式消息验证地址
     */
   public $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    public $config;

    public function __construct($config = array()) {
        $this->config = $config;
    }
    /**
     *支付宝交易验证
     */
    function verifyNotify($params)
    {
       if (empty($params)) {//判断数组是否为空
            return false;
        } else {
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'false';
            if (!empty($params["notify_id"])) $responseTxt = $this->getResponse($params["notify_id"]);
            if (preg_match("/true$/i", $responseTxt)) return true;
            else return false;
        }
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     */
    function getResponse($notify_id)
    {
        $partner = trim($this->alipay_config['partner']);
        $veryfy_url = '';
        $veryfy_url = $this->https_verify_url;
        $veryfy_url = $veryfy_url . "partner=" . $partner . "&notify_id=" . $notify_id;
        $responseTxt = httpGetResponse($veryfy_url);
        return $responseTxt;
    }
}

