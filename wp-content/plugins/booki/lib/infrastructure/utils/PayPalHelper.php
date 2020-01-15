<?php
class Booki_PayPalHelper{
	public static function read(){
		$repo = new Booki_PaypalSettingRepository();
		return $repo->read();
	}
	
	public static function getCurrencies(){
		return array(
			'AUD'=>'Australian Dollar (A $)'
			,'CAD'=>'Canadian Dollar (C $)'
			,'EUR'=>'Euro (&euro;)'
			,'GBP'=>'British Pound (&pound;)'
			,'JPY'=>'Japanese Yen (&yen;)'
			,'USD'=>'U.S. Dollar ($)'
			,'NZD'=>'New Zealand Dollar ($)'
			,'CHF'=>'Swiss Franc'
			,'HKD'=>'Hong Kong Dollar ($)'
			,'SGD'=>'Singapore Dollar ($)'
			,'SEK'=>'Swedish Krona'
			,'DKK'=>'Danish Krone'
			,'PLN'=>'Polish Zloty'
			,'NOK'=>'Norwegian Krone'
			,'HUF'=>'Hungarian Forint'
			,'CZK'=>'Czech Koruna'
			,'ILS'=>'Israeli New Shekel'
			,'MXN'=>'Mexican Peso'
			,'BRL'=>'Brazilian Real (only for Brazilian members)'
			,'MYR'=>'Malaysian Ringgit (only for Malaysian members)'
			,'PHP'=>'Philippine Peso'
			,'TWD'=>'New Taiwan Dollar'
			,'THB'=>'Thai Baht'
			,'TRY'=>'Turkish Lira (only for Turkish members)'
			, 'RUB'=>'Russian Ruble'
		);
	}
	
	public static function getSandboxConfigParams(){
		return array(
			    'acct1.UserName' =>'jb-us-seller_api1.paypal.com'
			  , 'acct1.Password' =>'1373881165'
			  , 'acct1.Signature' =>'AFcWxV21C7fd0v3bYYYRCpSSRl31Adh.PaNZfowELsI4AvTiaExiqB1p'
			  , 'acct1.AppId' =>'APP-80W284485P519543T'
			  , 'acct1.Subject' =>''
			  , 'acct2.UserName' =>'platfo_1255170694_biz_api1.gmail.com'
			  , 'acct2.Password' =>'2DPPKUPKB7DQLXNR'
			  , 'acct2.CertPath' =>'cert_key.pem'
			  , 'settings.Currency' =>'EUR'
			  , 'settings.BrandName' =>''
			  , 'settings.CustomPageStyle' =>'mystyle'
			  , 'settings.HeaderImage' =>''
			  , 'settings.HeaderBorderColor' =>'#f70a0a'
			  , 'settings.HeaderBackColor' =>'#2372d9'
			  , 'settings.PayFlowColor' =>'#cc31e0'
			  , 'settings.CartBorderColor' =>'#d0e645'
			  , 'settings.Logo' =>''
			  , 'settings.AllowBuyerNote' =>''
			  , 'http.ConnectionTimeOut' =>'30'
			  , 'http.Retry' =>'5'
			  , 'service.EndPoint.PayPalAPI' =>'https://api-3t.sandbox.paypal.com/2.0'
			  , 'service.EndPoint.PayPalAPIAA' =>'https://api-3t.sandbox.paypal.com/2.0'
			  , 'service.EndPoint.IPN' =>'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr'
			  , 'service.RedirectURL' =>'https://www.sandbox.paypal.com/webscr&cmd='
			  , 'service.EndPoint.Permissions' =>'https://svcs.sandbox.paypal.com/'
			  , 'log.FileName' =>'../PayPal.log'
			  , 'log.LogLevel' =>'INFO'
			  , 'log.LogEnabled' =>'1'
		);
	}
	
	public static function getLiveConfigParams(){
		return array(
			   'acct1.UserName' =>'jb-us-seller_api1.paypal.com'
			  , 'acct1.Password' =>'1373881165'
			  , 'acct1.Signature' =>'AFcWxV21C7fd0v3bYYYRCpSSRl31Adh.PaNZfowELsI4AvTiaExiqB1p'
			  , 'acct1.AppId' =>'APP-80W284485P519543T'
			  , 'acct1.Subject' =>''
			  , 'acct2.UserName' =>'platfo_1255170694_biz_api1.gmail.com'
			  , 'acct2.Password' =>'2DPPKUPKB7DQLXNR'
			  , 'acct2.CertPath' =>'cert_key.pem'
			  , 'settings.Currency' =>'EUR'
			  , 'settings.BrandName' =>''
			  , 'settings.CustomPageStyle' =>'mystyle'
			  , 'settings.HeaderImage' =>''
			  , 'settings.HeaderBorderColor' =>'#f70a0a'
			  , 'settings.HeaderBackColor' =>'#2372d9'
			  , 'settings.PayFlowColor' =>'#cc31e0'
			  , 'settings.CartBorderColor' =>'#d0e645'
			  , 'settings.Logo' =>''
			  , 'settings.AllowBuyerNote' =>''
			  , 'http.ConnectionTimeOut' =>'30'
			  , 'http.Retry' =>'5'
			  , 'service.EndPoint.PayPalAPI' =>'https://api-3t.paypal.com/2.0'
			  , 'service.EndPoint.PayPalAPIAA' =>'https://api-3t.paypal.com/2.0'
			  , 'service.EndPoint.IPN' =>'https://ipnpb.paypal.com/cgi-bin/webscr'
			  , 'service.RedirectURL' =>'https://www.paypal.com/webscr&cmd='
			  , 'service.EndPoint.Permissions' =>'https://svcs.sandbox.paypal.com/'
			  , 'log.FileName' =>'../PayPal.log'
			  , 'log.LogLevel' =>'INFO'
			  , 'log.LogEnabled' =>'1'
		);
	}
}
?>