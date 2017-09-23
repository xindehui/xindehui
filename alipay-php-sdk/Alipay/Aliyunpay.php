<?php
namespace Alipay;
/**
 * 支付宝
 * Class AliPay
 */
class Aliyunpay {
	public $config;
	    /** 执行错误消息及代码 */
    public $errMsg;
	//旧版网关
	public $gateUrl = "https://mapi.alipay.com/gateway.do?";
	//新版网关
	public $gatewayUrl = "https://openapi.alipay.com/gateway.do?";
	
    public function __construct($config = array()) {
        $this->config = $config;
    }
	/*网站开始支付
     *$body           描述
     *$out_trade_no   订单号
     *$subject        标题
     *$total_amount   价格
     * @return string返回查询接口的结果
     */
public function pay($body,$out_trade_no,$subject,$total_amount,$return_url,$notify_url,$extra_common_param) {
	$params['body']          =  $body;
	$params['out_trade_no']  =  $out_trade_no;
	$params['subject']       =  $subject;
	$params['total_fee']     =  $total_amount;
	$params['extra_common_param']= $extra_common_param;//如果用户请求时传递了该参数，则返回给商户时会回传该参数。
	$params['service']       =  'create_direct_pay_by_user';
	$params['partner']       = $this->config['partner'];//签约的支付宝账号对应的支付宝唯一用户号
	$params['seller_id']     = $this->config['seller_id'];//seller_id是以2088开头的纯16位数字
	$params['payment_type']  = $this->config['payment_type'];//只支持取值为1（商品购买）。
	$params['notify_url']    = $notify_url;//支付宝服务器主动通知商户网站里指定的页面http路径
	$params['return_url']    = $return_url;//支付宝处理完请求后，当前页面自动跳转到商户网站里指定页面的http路径。
	$params['_input_charset']= $this->config['input_charset'];//商户网站使用的编码格式，如UTF-8、GBK、GB2312等。
	$params['it_b_pay']      = $this->config['it_b_pay'];//设置未付款交易的超时时间，一旦超时，该笔交易就会自动被关闭。
	$params['sign']          =sign(getSignContent($params),$this->config['sign_type'],$this->config['PrivateKeyFilePath']);
	$params['sign_type']     = $this->config['sign_type'];//DSA、RSA、MD5三个值可选，必须大写
	$url=$this->gateUrl . http_build_query($params);
                     header("location:$url");
	exit;
}
     /*手机网站开始支付
     *$body           描述
     *$out_trade_no   订单号
     *$subject        标题
     *$total_amount   价格
     * @return string返回查询接口的结果
     */
  public function mobilepay($body,$out_trade_no,$subject,$total_amount,$return_url,$notify_url,$passback_params,$goods_type=1) {

	$data['body']         = $body;
            $data['out_trade_no'] = $out_trade_no;
	$data['goods_type']    = $goods_type;
	$data['subject']      = $subject;
	$data['total_amount'] = $total_amount;
	$data['passback_params'] = urlencode($passback_params);//如果用户请求时传递了该参数，则返回给商户时会回传该参数。
	$data['seller_id']    = $this->config['seller_id'];//收款支付宝用户ID。 如果该值为空，则默认为商户签约账号对应的支付宝用户ID
	$data['product_code'] = "QUICK_WAP_PAY";//销售产品码，商家和支付宝签约的产品码
	$data['timeout_express'] = $this->config['it_b_pay'];//该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，
	$params['app_id']     = $this->config['app_id'];  //支付宝分配给开发者的应用ID
	$params['method']     = 'alipay.trade.wap.pay';//接口名称
	$params['format']     = $this->config['format']; //仅支持JSON
	$params['sign_type']  = $this->config['sign_type'];
	$params['timestamp']  = date("Y-m-d H:i:s");
	$params['alipay_sdk'] = 'alipay-sdk-php-20161101';
	$params['charset']    = $this->config['input_charset'];
	$params['version']    ='1.0';
	$params['return_url'] = $return_url;//支付宝服务器主动通知商户网站里指定的页面http路径
	$params['notify_url'] = $notify_url;//支付宝服务器主动通知商户网站里指定的页面http路径
	$params['biz_content'] = json_encode($data,JSON_UNESCAPED_UNICODE);
	$params['sign'] = sign(getSignContent($params),$this->config['sign_type'],$this->config['PrivateKeyFilePath']);
             $url=$this->gatewayUrl . http_build_query($params);
             header("location:$url");
	exit;
     }
    /**
     *提交刷卡支付，并且确认结果，接口比较慢
     * @param string $body           描述
     * @param string $out_trade_no   订单号
     * @param string $auth_code      支付授权码
     * @param string $subject        标题
     * @param price $total_amount   该金额不能大于订单金额,单位为元，支持两位小数
     * @return string返回查询接口的结果
     */
public function Micropay($body,$out_trade_no,$auth_code,$subject,$total_amount,$passback_params) {
	//构造要请求的参数数组，无需改动
	$data['body']         = $body;
	$data['out_trade_no'] = $out_trade_no;
	$data['auth_code']    = $auth_code;
	$data['subject']      = $subject;
	$data['total_amount'] = $total_amount;
	$data['passback_params'] = urlencode($passback_params);//如果用户请求时传递了该参数，则返回给商户时会回传该参数。
	$data['scene']        = 'bar_code';//支付场景 条码支付，取值：bar_code 声波支付，取值：wave_code
	$data['seller_id']    = $this->config['seller_id'];//收款支付宝用户ID。 如果该值为空，则默认为商户签约账号对应的支付宝用户ID
	$data['timeout_express'] = $this->config['it_b_pay'];//该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，
	$params['app_id']     = $this->config['app_id'];  //支付宝分配给开发者的应用ID
	$params['method']     = 'alipay.trade.pay';//接口名称
	$params['format']     = $this->config['format']; //仅支持JSON
	$params['timestamp']  = date("Y-m-d H:i:s");
	$params['charset']    = $this->config['input_charset'];//商户网站使用的编码格式，如UTF-8、GBK、GB2312等。
	$params['version']    ='1.0';
	$params['biz_content'] = json_encode($data,JSON_UNESCAPED_UNICODE);
	$params['sign_type']  = $this->config['sign_type'];//DSA、RSA、MD5三个值可选，必须大写
	$params['sign'] = sign(getSignContent($params),$this->config['sign_type'],$this->config['PrivateKeyFilePath']);
             $url=$this->gatewayUrl . http_build_query($params);
	$result=httpGetResponse($url);
	return json_decode($result, true);
}
    /**
     *支付交易查询
     * @param string $out_trade_no订单支付时传入的商户订单号,不能和 trade_no同时为空。
     * @param string $trade_no	支付宝交易号，和商户订单号不能同时为空
     * @return string返回查询接口的结果
     */
  public function queryOrder($out_trade_no,$trade_no='') {
	$data['out_trade_no'] = $out_trade_no;
	$data['trade_no']     = $trade_no;
	$params['app_id']     = $this->config['app_id'];  //支付宝分配给开发者的应用ID
	$params['method']     = 'alipay.trade.query';//接口名称
	$params['format']     = $this->config['format']; //仅支持JSON
	$params['timestamp']  = date("Y-m-d H:i:s");
	$params['charset']    = $this->config['input_charset'];//商户网站使用的编码格式，如UTF-8、GBK、GB2312等。
	$params['version']    ='1.0';
	$params['biz_content'] = json_encode($data,JSON_UNESCAPED_UNICODE);
	$params['sign_type']  = $this->config['sign_type'];//DSA、RSA、MD5三个值可选，必须大写
	$params['sign'] = sign(getSignContent($params),$this->config['sign_type'],$this->config['PrivateKeyFilePath']);
             $url=$this->gatewayUrl . http_build_query($params);
	$result=httpGetResponse($url);
        return json_decode($result, true);
    } 
    /**
     * 撤销订单
     * @param string $out_trade_no订单支付时传入的商户订单号,不能和 trade_no同时为空。
     * @param string $trade_no	支付宝交易号，和商户订单号不能同时为空
     * @return bool
     */
    public function reverse($out_trade_no,$trade_no='') {
             $data['out_trade_no'] = $out_trade_no;
	  $data['trade_no']     = $trade_no;
	  $params['app_id']     = $this->config['app_id'];  //支付宝分配给开发者的应用ID
	  $params['method']     = 'alipay.trade.cancel';//接口名称
	  $params['format']     = $this->config['format']; //仅支持JSON
	  $params['timestamp']  = date("Y-m-d H:i:s");
	  $params['charset']    = $this->config['input_charset'];//商户网站使用的编码格式，如UTF-8、GBK、GB2312等。
	  $params['version']    ='1.0';
	  $params['biz_content'] = json_encode($data,JSON_UNESCAPED_UNICODE);
	  $params['sign_type']  = $this->config['sign_type'];//DSA、RSA、MD5三个值可选，必须大写
	  $params['sign'] = sign(getSignContent($params),$this->config['sign_type'],$this->config['PrivateKeyFilePath']);
               $url=$this->gatewayUrl . http_build_query($params);
	  $result=httpGetResponse($url);
              return json_decode($result, true);
    }
   /**
     * 关闭订单
     * @param string $out_trade_no订单支付时传入的商户订单号,不能和 trade_no同时为空。
     * @param string $trade_no	支付宝交易号，和商户订单号不能同时为空
     * @return bool
     */
    public function closeOrder($out_trade_no,$trade_no='') {
               $data['out_trade_no'] = $out_trade_no;
	  $data['trade_no']     = $trade_no;
	  $params['app_id']     = $this->config['app_id'];  //支付宝分配给开发者的应用ID
	  $params['method']     = 'alipay.trade.close';//接口名称
	  $params['format']     = $this->config['format']; //仅支持JSON
	  $params['timestamp']  = date("Y-m-d H:i:s");
	  $params['charset']    = $this->config['input_charset'];//商户网站使用的编码格式，如UTF-8、GBK、GB2312等。
	  $params['version']    ='1.0';
	  $params['biz_content'] = json_encode($data,JSON_UNESCAPED_UNICODE);
	  $params['sign_type']  = $this->config['sign_type'];//DSA、RSA、MD5三个值可选，必须大写
	  $params['sign'] = sign(getSignContent($params),$this->config['sign_type'],$this->config['PrivateKeyFilePath']);
               $url=$this->gatewayUrl . http_build_query($params);
	  $result=httpGetResponse($url);
        return json_decode($result, true);
    }
   /**
     * 订单退款
     * @param string $out_trade_no订单支付时传入的商户订单号,不能和 trade_no同时为空。
     * @param string $trade_no	支付宝交易号，和商户订单号不能同时为空
     * @param Price  $refund_amount	需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数
     * @param string $refund_reason退款的原因说明
     * @param string $out_request_no标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传。
     * @return bool
     */
    public function refundOrder($out_trade_no,$trade_no,$refund_amount,$refund_reason,$out_request_no) {
               $data['out_trade_no'] = $out_trade_no;
	  $data['trade_no']     = $trade_no;
	  $data['refund_amount']     = $refund_amount;
	  $data['refund_reason']     = $refund_reason;
	  $data['out_request_no']     = $out_request_no;
	  $params['app_id']     = $this->config['app_id'];  //支付宝分配给开发者的应用ID
	  $params['method']     = 'alipay.trade.refund';//接口名称
	  $params['format']     = $this->config['format']; //仅支持JSON
	  $params['timestamp']  = date("Y-m-d H:i:s");
	  $params['charset']    = $this->config['input_charset'];//商户网站使用的编码格式，如UTF-8、GBK、GB2312等。
	  $params['version']    ='1.0';
	  $params['biz_content'] = json_encode($data,JSON_UNESCAPED_UNICODE);
	  $params['sign_type']  = $this->config['sign_type'];//DSA、RSA、MD5三个值可选，必须大写
	  $params['sign'] = sign(getSignContent($params),$this->config['sign_type'],$this->config['PrivateKeyFilePath']);
               $url=$this->gatewayUrl . http_build_query($params);
	  $result=httpGetResponse($url);
        return json_decode($result, true);
    }
   /**
     * 订单退款查询
     * @param string $out_trade_no订单支付时传入的商户订单号,不能和 trade_no同时为空。
     * @param string $trade_no	支付宝交易号，和商户订单号不能同时为空
     * @param string $out_request_no标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传。
     * @return bool
     */
    public function refundQuery($out_trade_no,$trade_no,$out_request_no) {
               $data['out_trade_no'] = $out_trade_no;
	  $data['trade_no']     = $trade_no;
	  $data['out_request_no']     = $out_request_no;
	  $params['app_id']     = $this->config['app_id'];  //支付宝分配给开发者的应用ID
	  $params['method']     = 'alipay.trade.fastpay.refund.query';//接口名称
	  $params['format']     = $this->config['format']; //仅支持JSON
	  $params['timestamp']  = date("Y-m-d H:i:s");
	  $params['charset']    = $this->config['input_charset'];//商户网站使用的编码格式，如UTF-8、GBK、GB2312等。
	  $params['version']    ='1.0';
	  $params['biz_content'] = json_encode($data,JSON_UNESCAPED_UNICODE);
	  $params['sign_type']  = $this->config['sign_type'];//DSA、RSA、MD5三个值可选，必须大写
	  $params['sign'] = sign(getSignContent($params),$this->config['sign_type'],$this->config['PrivateKeyFilePath']);
               $url=$this->gatewayUrl . http_build_query($params);
	  $result=httpGetResponse($url);
        return json_decode($result, true);
    }
}