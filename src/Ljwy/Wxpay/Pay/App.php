<?php
namespace Ljwy\Wxpay\Pay;

use Ljwy\Wxpay\WxpayException;
use Ljwy\Wxpay\Sdk\Api;
use Ljwy\Wxpay\Models\JsApiPay;
use Ljwy\Wxpay\Models\UnifiedOrder;

/**
 *
 * JSAPI支付实现类
 * 该类实现了从微信公众平台获取code、通过code获取openid和access_token、
 * 生成jsapi支付js接口所需的参数、生成获取共享收货地址所需的参数
 *
 * 该类是微信支付提供的样例程序，商户可根据自己的需求修改，或者使用lib中的api自行开发
 *
 */
class App
{

	protected $config;

	protected $api;

	public function __construct($config)
	{
		$this->config = $config;
		$this->api = new Api($config);
	}

	/**
	 * 微信小程序支付
	 * @param UnifiedOrder $data
	 * @throws WxPayException
	 *
	 * @return json数据
	 */
	public function pay(UnifiedOrder $data)
	{
            $order = $this->api->unifiedOrder($data);
            return $this->GetJsApiParameters($order);
	}
	
	/**
	 *
	 * 获取jsapi支付的参数
	 * @param array $UnifiedOrderResult 统一支付接口返回的数据
	 * @throws WxPayException
	 *
	 * @return json数据，可直接填入js函数作为参数
	 */
	public function GetJsApiParameters($UnifiedOrderResult)
	{
		if (! array_key_exists('appid', $UnifiedOrderResult) || ! array_key_exists('prepay_id', $UnifiedOrderResult) || $UnifiedOrderResult['prepay_id'] == '') {
			throw new WxPayException('参数错误');
		}
		$jsapi = new JsApiPay();
		$jsapi->setAppid($UnifiedOrderResult['appid']);
		$timeStamp = time();
		$jsapi->setTimeStamp($timeStamp);
		$jsapi->setNonceStr($this->api->getNonceStr());
		$jsapi->setPackage('prepay_id=' . $UnifiedOrderResult['prepay_id']);
		$jsapi->setSignType('MD5');
		$jsapi->setPaySign($jsapi->makeSign());
		$parameters = json_encode($jsapi->getValues());
		return $parameters;
	}
}
