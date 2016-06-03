<?php
namespace leyestd\alipay;

use leyestd\alipay\Aliconfig;
use leyestd\alipay\lib\AlipaySubmit;
use leyestd\alipay\lib\AlipayCore;


/**
 * @abstract 支付宝订单管理
 * @author lenovo
 *
 */

class AlipayApi extends AlipaySubmit {
    
    public $alipay_config = [];
    
    public function __construct(){
        
        $this->alipay_config=(new Aliconfig)->getAliconfig();
        parent::__construct($this->alipay_config);
    }
    

    /**
     * 关闭待支付的订单
     * @param string $out_trade_no 商户订单号
     * @param string $trade_no 支付宝交易号
     * @return string T | F //T成功|F失败
     */
    public function closeOrder($out_trade_no, $trade_no = ''){
        
        $ret = 'F';
        
        //构造要请求的参数数组，无需改动
        $parameter = array("service" => "close_trade",
            "partner" => trim($this->alipay_config['partner']),
            "trade_no" => $trade_no,
            "out_order_no" => $out_trade_no,
            "_input_charset" => trim(strtolower($this->alipay_config['input_charset']))
        );
        //建立请求
        $html_text = $this->buildRequestHttp($parameter);
        
        
        //解析XML
        //注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
        $doc = new \DOMDocument();
        $doc->loadXML($html_text);
        
        //解析XML
        if(!empty($doc->getElementsByTagName("alipay")->item(0)->nodeValue)) {
            $ret = $doc->getElementsByTagName("alipay")->item(0)->nodeValue;
        }

        if($ret != 'T' && $ret != 'F') {
            $ret = 'F';
        }
        
        return $ret;
        
    }
    
    /**
     * 查询订单
     * @param string $out_trade_no 订单号
     * @param string $trade_no 支付宝交易流水号
     * @return \leyestd\alipay\lib\要请求的参数数组
     */
    public function selectOrder($out_trade_no,$trade_no='') {
        
        //构造要请求的参数数组，无需改动
        $parameter = array("service" => "single_trade_query",
            "partner" => trim($this->alipay_config['partner']),
            "trade_no" => $trade_no,
            "out_trade_no" => $out_trade_no,
            "_input_charset" => trim(strtolower($this->alipay_config['input_charset']))
        );
        
        $veryfy_url = 'https://mapi.alipay.com/gateway.do?service=single_trade_query&';
        
        $veryfy_url.= $this->buildRequestParaToString($parameter);
        
        $responseTxt = AlipayCore::getHttpResponseGET($veryfy_url, $this->alipay_config['cacert']);
        
       return  simplexml_load_string($responseTxt);
    }
    
}

