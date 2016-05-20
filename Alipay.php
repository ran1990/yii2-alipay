<?php
namespace leyestd\alipay;

use leyestd\alipay\Aliconfig;
use leyestd\alipay\lib\AlipaySubmit;

/* *
 * 功能：纯担保交易接口接入页
 * 版本：3.3
/* * ************************请求参数************************* */

class Alipay {
    
    public $alipay_config = [];
    
    public $parameter = [
        'service' => 'create_direct_pay_by_user',//接口名称
        'payment_type' =>1,	//支付类型
        'anti_phishing_key' => null,	//防钓鱼时间戳
        'exter_invoke_ip' => null,	//客户端的IP地址
        '_input_charset' => 'utf-8',	//字符编码格式
    ];

    //是否移动端
    private $isMobile = false;
    
    public function __construct(){
        $this->alipay_config=(new Aliconfig)->getAliconfig();
        
        if($this->isMobile()){
            $this->parameter['service'] = 'alipay.wap.create.direct.pay.by.user';
            
            $this->isMobile = true;
        }
    }
    
    /**
     * 获取支付链接
     * @method getPayUrl
     * @param string $notify_url 异步通知地址
     * @param string $return_url 同步通知地址
     * @param string $out_trade_no 商户订单号
     * @param number $total_fee 付款金额
     * @param string $subject 订单名称
     * @param string [$body=null] 订单描述
     * @param string [$show_url=null] 商品展示地址
     * @param string [$pay_id=''] 支付方式
     * @param string [$bank=''] 银行编码
     * @return string
     * @example $this->getPayUrl($notify_url, $return_url, $out_trade_no, $subject, $total_fee, $body, $show_url, $expired_at);
     */
    
    public  function getPayUrl($notify_url, $return_url, $out_trade_no, $subject, $total_fee, $body = null, $show_url = null,$pay_id= 0,$bank = null)
    {
        
        if ($pay_id == 1) {
            $paymethod = '';
            $bank = '';
        } else {
            $paymethod = 'bankPay';
        }
        
        $params = array_merge([
            'seller_email' =>trim($this->alipay_config['seller_email']),
            'partner' => trim($this->alipay_config['partner']),
            "paymethod" => $paymethod,
            "defaultbank" => $bank,
            'notify_url' => $notify_url,
            'return_url' => $return_url,
            'out_trade_no' =>trim($out_trade_no),
            'subject' => $subject,
            'total_fee' => sprintf("%.2f",$total_fee),
            'body' => $body,
            'show_url' => $show_url,
            //'it_b_pay' => $expired_at > 0 ? ($this->isMobile ? date('Y-m-d H:i:s', $expired_at) : max(1, floor(($expired_at - time()) / 60)) . 'm') : null,
        ], $this->parameter);
        
        $alipaySubmit = new AlipaySubmit($this->alipay_config);
        
        return $alipaySubmit->buildRequestForm($params, 'get', '正在请求支付网关，请稍候……');
    }

    /**
     * 移动端检测
     * @method isMobile
     * @since 0.0.1
     * @return {boolean}
     */
    private function isMobile(){
        return isset($_SERVER['HTTP_X_WAP_PROFILE']) || (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], 'wap')) || (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(nokia|sony|ericsson|mot|samsung|htc|sgh|lg|sharp|sie-|philips|panasonic|alcatel|lenovo|iphone|ipod|blackberry|meizu|android|netfront|symbian|ucweb|windowsce|palm|operamini|operamobi|openwave|nexusone|cldc|midp|wap|mobile)/i', strtolower($_SERVER['HTTP_USER_AGENT'])));
    }
    
}

