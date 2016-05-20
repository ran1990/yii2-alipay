<?php
namespace leyestd\alipay;

use leyestd\alipay\Aliconfig;
use leyestd\alipay\lib\AlipaySubmit;
use leyestd\alipay\lib\WapAlipaySubmit;

/* *
 * 功能：纯担保交易接口接入页
 * 版本：3.3
/* * ************************请求参数************************* */

class WapAlipay {
    
    
    public $alipay_config = [];
    
    //返回格式
    public $format = "xml";
    //必填，不需要修改
    
    //返回格式
    public $v = "2.0";
    //必填，不需要修改
    
    //请求号
    public $req_id;
    //必填，须保证每次请求都是唯一
    
    //**req_data详细信息**
    
    /////======================================此行待修改===============================================
    //操作中断返回地址
    public $merchant_url = "http://weixin.liketry.com/";
    //用户付款中途退出返回商户的地址。需http://格式的完整路径，不允许加?id=123这类自定义参数
    
    //请求业务参数详细
    
    public function __construct() {
        
        $this->alipay_config=(new Aliconfig)->getAliconfig();
        
        $this->req_id = md5(uniqid(mt_rand(1, 9999), true));
        
        
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
        $req_data = '<direct_trade_create_req><notify_url>' .$notify_url . '</notify_url><call_back_url>' . $return_url . '</call_back_url><seller_account_name>' . $this->alipay_config['seller_email'] . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><merchant_url>' . $this->merchant_url . '</merchant_url></direct_trade_create_req>';
        
        //构造要请求的参数数组，无需改动
        $para_token = array(
            "service" => "alipay.wap.trade.create.direct",
            "partner" => trim($this->alipay_config['partner']),
            "sec_id" => trim($this->alipay_config['sign_type']),
            "format"	=> $this->format,
            "v"	=> $this->v,
            "req_id"	=> $this->req_id,
            "req_data"	=> $req_data,
            "_input_charset"	=> trim($this->alipay_config['input_charset'])
        );
        
        //建立请求
        $alipaySubmit = new WapAlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestHttp($para_token);
        
        //URLDECODE返回的信息
        $html_text = urldecode($html_text);
        //解析远程模拟提交后返回的信息
        $para_html_text = $alipaySubmit->parseResponse($html_text);
        //获取request_token
        $request_token = $para_html_text['request_token'];

        /**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/
        
        //业务详细
        $req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
        //必填
        
        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "alipay.wap.auth.authAndExecute",
            "partner" => trim($this->alipay_config['partner']),
            "sec_id" => trim($this->alipay_config['sign_type']),
            "format"	=> $this->format,
            "v"	=> $this->v,
            "req_id"	=> $this->req_id,
            "req_data"	=> $req_data,
            "_input_charset"	=> trim($this->alipay_config['input_charset'])
        );

        $alipaySubmit = new WapAlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, 'get', 'connecting...');
        return  $html_text;
        
    }

    
}

