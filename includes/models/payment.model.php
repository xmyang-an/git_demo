<?php

/* 支付方式 payment */
class PaymentModel extends BaseModel
{
    var $table  = 'payment';
    var $prikey = 'payment_id';
    var $_name  = 'payment';

    var $_autov     =   array(
        'store_id'  =>  array(
            'required'  =>  true,
            'filter'    => 'intval',
        ),
        'payment_code'  => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'payment_name'  =>  array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'payment_desc'  => array(
            'filter'    => 'trim',
        ),
        'config'        => array(
            'filter'    => 'serialize',
        ),
        'enabled'       => array(
            'filter'    => 'intval',
        ),
        'sort_order'       => array(
            'filter'    => 'intval',
        ),
    );

    var $_relation  =   array(
        // 一个支付方式只能属于一个店铺
        'belongs_to_store' => array(
            'model'         => 'store',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'store_id',
            'reverse'       => 'has_payment',
        ),
    );


    /* 对店铺支付方式的操作 */
    /**
     *    安装支付方式
     *
     *    @author    MiMall
     *    @param     array $payment
     *    @return    bool
     */
    function install($payment)
    {
        if (!$this->in_white_list($payment['payment_code']))
        {
            $this->_error('system_disabled_payment');

            return;
        }

        return $this->add($payment);
    }

    /**
     *    卸载支付方式
     *
     *    @author    MiMall
     *    @param     int $store_id
     *    @param     int $payment_id
     *    @return    bool
     */
    function uninstall($store_id, $payment_id)
    {
        return $this->drop("store_id = {$store_id} AND payment_id={$payment_id}");
    }

    /**
     *    获取已安装的支付方式
     *
     *    @author    MiMall
     *    @param     int $store_id
     *    @return    array
     */
    function get_installed($store_id)
    {
        return $this->find(array(
            'conditions'    => "store_id={$store_id}",
            'order'         => 'sort_order',
        ));
    }

    /**
     *    获取已启用的
     *
     *    @author    MiMall
     *    @param     int $store_id
     *    @return    array
     */
    function get_enabled($store_id, $removeImproper = TRUE)
    {
		/* 平台统一配置的支付方式及卖家配置的支付方式（目前只有货到付款是开放给卖家配置） */
		$conditions = " enabled=1 AND (store_id=0 || (store_id={$store_id} AND payment_code = 'cod')) ";
		
		$payments = parent::find(array(
            'conditions'    => $conditions . " AND payment_code" . db_create_in($this->get_white_list()),
            'order'         => 'sort_order',
        ));
		
		/* 剔除不合适的支付方式 */
		if($removeImproper === TRUE)
		{
			//$improper = array(); 不合适的
			$suitable = array();// 合适的
			
			/* 如果是手机端 */
			if((defined('IN_MOBILE') && IN_MOBILE === true) || (defined('IN_API') && IN_API == true)) {
				if(isWeixin()) {		
					// 是手机端且是微信端，则剔除支付宝（PC），支付宝WAP支付，微信扫码支付
					//$improper = array('alipay', 'alipay_mobile', 'tenpay', 'wxnativepay');
					if(defined('IN_API') && IN_API == true)
					{
						$suitable = array('deposit','wxminiprogram');
					}
					else{
						$suitable = array('cod', 'deposit', 'tenpay_wap', 'unionpay', 'wxpay');
					}
				}
					
				else {
						
					// 是手机端，但不是微信端，则剔除支付宝PC，财付通（PC），微信JSAPI支付，微信扫码支付
					//$improper = array('alipay', 'tenpay', 'wxpay', 'wxnativepay');
					$suitable = array('cod', 'deposit', 'alipay_mobile', 'tenpay_wap', 'unionpay','wxpay_h5'); 
				}
			}
			
			else {
				
				// 如果是PC端，则剔除支付宝WAP支付，在财付通（WAP），微信JSAPI支付
				//$improper = array('alipay_mobile', 'tenpay_wap', 'wxpay');
				$suitable = array('cod', 'deposit', 'alipay', 'tenpay', 'unionpay', 'wxnativepay');
			}
			
			foreach($payments as $key => $payment) {
				//if(in_array($payment['payment_code'], $improper)) {
				if(!in_array($payment['payment_code'], $suitable)) {
					unset($payments[$key]);
				}
			} 
		}
		
		if($payments)
		{
			/* 排序，使得在收银台界面余额支付排在第一位 */
			$tmp = array();
			foreach($payments as $key => $payment)
			{
				if(in_array($payment['payment_code'], array('deposit'))) {
					$tmp[$key] = $payment;
					unset($payments[$key]);
					break;
				}
			}
			$payments = $tmp + $payments;
		}
		
		return $payments;
    }

    /*---------对内置支付方式的操作---------*/

    /**
     *    获取内置支付方式
     *
     *    @author    MiMall
     *    @param     array $withe_list 白名单
     *    @return    array
     */
    function get_builtin($white_list = null)
    {
        static $payments = null;
        if ($payments === null)
        {
            $payment_dir = ROOT_PATH . '/includes/payments';
            $dir = dir($payment_dir);
            $payments = array();
            while (false !== ($entry = $dir->read()))
            {
                /* 隐藏文件，当前目录，上一级，排除 */
                if ($entry{0} == '.')
                {
                    continue;
                }

                if (is_array($white_list) && !in_array($entry, $white_list))
                {
                    continue;
                }

                /* 获取支付方式信息 */
                $payments[$entry] = $this->get_builtin_info($entry);
            }
        }
        if (is_array($payments))
        {
            uksort($payments, "cmp_payment");
        }

        return $payments;
    }

    /**
     *    获取内置支付方式的配置信息
     *
     *    @author    MiMall
     *    @param     string $code
     *    @return    array
     */
    function get_builtin_info($code)
    {
        Lang::load(lang_file('payment/' . $code));
        $payment_path = ROOT_PATH . '/includes/payments/' . $code . '/payment.info.php';

        return include($payment_path);
    }

    /**
     *    获取支付方式白名单
     *
     *    @author    MiMall
     *    @return    array
     */
    function get_white_list()
    {
        $file = ROOT_PATH . '/data/payments.inc.php';
        if (!is_file($file))
        {
            return array();
        }

        return include($file);
    }

    /**
     *    启用内置支付方式
     *
     *    @author    MiMall
     *    @param     string $code
     *    @return    bool
     */
    function enable_builtin($code)
    {
        $white_list = $this->get_white_list();
        $white_list[] = $code;
        $white_list = array_unique($white_list);
        return $this->save_white_list($white_list);
    }

    /**
     *    禁用内置支付方式
     *
     *    @author    MiMall
     *    @param     string $code
     *    @return    void
     */
    function disable_builtin($code)
    {
        $white_list = $this->get_white_list();
        $index = array_search($code, $white_list);
        if (false !== $index)
        {
            unset($white_list[$index]);

            return $this->save_white_list($white_list);
        }

        return false;
    }

    /**
     *    保存白名单
     *
     *    @author    MiMall
     *    @param     array $white_list
     *    @return    bool
     */
    function save_white_list($white_list)
    {
        $payments_inc_file = ROOT_PATH . '/data/payments.inc.php';
        $php_data = "<?php\n\nreturn " . var_export($white_list, true) . ";\n\n?>";

        return file_put_contents($payments_inc_file, $php_data, LOCK_EX);
    }

    /**
     *    判断指定code的payment是否在白名单中
     *
     *    @author    MiMall
     *    @param     string $code
     *    @return    bool
     */
    function in_white_list($code)
    {
        if (!$code)
        {
            return;
        }
        $white_list = $this->get_white_list();

        return in_array($code, $white_list);
    }
	
	// 获取支付方式的键值
	function getKeysOfPayments($payments = array())
	{
		$keys = array();
		foreach($payments as $key => $payment)
		{
			$keys[] = $payment['payment_code'];
		}
		
		return $keys;
	}
	
	function getAvailablePayments($orderInfo = array(), $userId = 0, $showDepositPay = TRUE, $showCodPay = FALSE, $allCodPayments = FALSE)
	{
		$order_mod 			= &m('order');
		$deposit_account_mod= &m('deposit_account');
		$payments 			= $this->get_enabled(0);
		
		$all_payments = $cod_payments = array();
			
		$selected_option = $errorMsg = FALSE;
		foreach ($payments as $key => $payment)
		{
			// 如果支付的金额为零，那么仅显示余额付款和货到付款，不显示网银等支付
			if($orderInfo['amount'] <= 0) {
				if(!in_array($payment['payment_code'], array('deposit', 'cod')))  continue;
			}
			if(in_array($payment['payment_code'], array('deposit'))) 
			{
				if($showDepositPay === TRUE)
				{
					$deposit_account = $deposit_account_mod->get(array('conditions'=>'user_id='.$userId));
					
					if(in_array($deposit_account['pay_status'], array('ON'))) {
						if($orderInfo['amount'] > $deposit_account['money']) {
							$payment['disabled'] = 1;
							$payment['disabled_desc'] = Lang::get('balancepay_not_enough');
						} else {
							$selected_option = TRUE;
							$payment['selected'] = 1;
						}
					}
					else
					{
						$payment['disabled'] = 1;
						$payment['disabled_desc'] = Lang::get('balancepay_disabled');
					}
				}
				else
				{
					$payment = FALSE;
				}
			}
            
			if($payment !== FALSE) {
				if ($selected_option === FALSE && !$payment['disabled']) {
					$selected_option = TRUE;
					$payment['selected'] = 1;
				}
				
				$all_payments[$payment['payment_code']] = $payment;
			}
		}
		
		
		// 检查是否支持货到付款(目前只有购物订单允许使用货到付款，其他如购买应用等不允许)
		if($showCodPay === TRUE && in_array($orderInfo['bizIdentity'], array(TRADE_ORDER))) 
		{	
			if($cod_payments = $order_mod->_checkMergePayCodPaymentEnable($orderInfo['orderList'])){
				
				$cod_payment = current($cod_payments);
				
				// 如果还没有选择默认的支付方式，则选择
				if($selected_option === FALSE) $cod_payment['selected'] = 1;
				$all_payments[$cod_payment['payment_code']] = $cod_payment;
			}
		}
		
		if(empty($all_payments)) {
			$errorMsg = Lang::get('store_no_payment');
		}
				
		return array($all_payments, $cod_payments, $errorMsg);	
	}
}

/* 比较函数，实现支付方式排序 */
function cmp_payment($a, $b)
{
    if ($b == 'alipay')
    {
        return 1;
    }
    elseif ($b == 'tenpay2' && $a != 'alipay')
    {
        return 1;
    }
    elseif ($b == 'tenpay' && $a != 'alipay' && $a != 'tenpay2')
    {
        return 1;
    }
    else
    {
        return -1;
    }
}

?>