<?php
namespace Alipay;

/**
 *阿里大于SDK
 */
class Aliyunsend {

    /** 支付接口基础地址 */
    public $gateUrl = "http://gw.api.taobao.com/router/rest?";
    public $config;
    /** 执行错误消息及代码 */
    public $errMsg;
    public $errCode;
	
    public function __construct($config = array()) {
        $this->config = $config;
    }
    /**
     * 发送短信
	 *$mobile  手机号
	 *$option  短信模板变量
	 *$template  短信模板ID
     */
    public function send($mobile, $option, $template) {
             $data['method']            = 'alibaba.aliqin.fc.sms.num.send';//接口名称
	$data['app_key']           = $this->config['app_key'];//分配给应用的AppKey
	$data['sms_free_sign_name'] =$this->config['sign_name'];////你的短信签名
	$data["timestamp"]         = date("Y-m-d H:i:s",time());
	$data['format']            ='json';//
	$data['v']                 ='2.0';//
	$data["sign_method"]       = "md5";
             $data['sms_type']          = 'normal';         //短信类型，传入值请填写normal
             $data['sms_param']         = json_encode($option);//短信模板变量
             $data['rec_num']           = $mobile;  //短信接收号码。支持单个或多个手机号码。群发短信需传入多个号码，以英文逗号分隔
             $data['sms_template_code'] = $template;//短信模板ID
	$data["sign"] = $this->getPaySign($data);
	$urldata=http_build_query($data);
        $result = httpGetResponse($this->gateUrl . $urldata);
        $result = json_decode($result, true);
        if (empty($result)) {
            $this->errMsg = '解析返回结果失败';
            return false;
        }
       if (isset($result['alibaba_aliqin_fc_sms_num_send_response']['result']['success']) && $result['alibaba_aliqin_fc_sms_num_send_response']['result']['success'] == 'true') {
            return true;
        }else{
            $this->errMsg     = $result['error_response']['sub_code'];
            $this->$errCode =$result['error_response']['sub_msg'];
           return false;
      }
    }
       /**
     * 短信记录
     *$mobile  手机号
     *$query_date  //短信发送日期，支持近30天记录查询，格式yyyyMMdd
     *$current_page  分页参数,页码
     *$page_size  分页参数，每页数量。最大值50
     */
    public function sendquery($mobile, $query_date, $current_page=1,$page_size=50) {
            $data['method']            = 'alibaba.aliqin.fc.sms.num.query';//接口名称
            $data['app_key']           = $this->config['app_key'];//分配给应用的AppKey
            $data["sign_method"]   = "md5";
            $data["timestamp"]         = date("Y-m-d H:i:s",time());
            $data['format']            ='json';//
            $data['v']                 ='2.0';//
            $data['rec_num']          = $mobile;  //短信接收号码。支持单个或多个手机号码。群发短信需传入多个号码，以英文逗号分隔
            $data['query_date']       = $query_date;//短信发送日期，支持近30天记录查询，格式yyyyMMdd
            $data['current_page']   = $current_page;//分页参数,页码
            $data['page_size']        =$page_size;//分页参数，每页数量。最大值50
            $data["sign"] = $this->getPaySign($data);
            $urldata=http_build_query($data);
    $result = httpGetResponse($this->gateUrl . $urldata);
    $result = json_decode($result, true);
        if (empty($result)) {
            $this->errMsg = '解析返回结果失败';
            return false;
        }
        if (isset($result['alibaba_aliqin_fc_sms_num_send_response']['result']['success']) && $result['alibaba_aliqin_fc_sms_num_send_response']['result']['success'] == 'true') {
            return true;
        }else{
            $this->errMsg     = $result['error_response']['sub_code'];
            $this->$errCode =$result['error_response']['sub_msg'];
           return false;
      }
    }
	    /**
     * 生成签名
     * @param array $option
     * @param string $partnerKey
     * @return string
     */
    public function getPaySign($option) {
        ksort($option);
        $arr = [];
        foreach ($option as $k => $v) {
           $arr[] = $k . $v;
        }
        return strtoupper(md5($this->config['secretKey'] . implode('', $arr) . $this->config['secretKey']));
    }
}
