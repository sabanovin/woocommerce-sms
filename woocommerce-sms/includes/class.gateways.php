<?php

class WoocommerceIR_Gateways_SMS {

	private static $_instance;
	private $username = array();
	private $password = array();
	private $from = array();

	public static function init() {
		if ( ! self::$_instance ) {
			self::$_instance = new WoocommerceIR_Gateways_SMS();
		}

		return self::$_instance;
	}

	public function __construct() {

		$this->username['SMS'] = PWooSMS()->Options( 'persian_woo_sms_username', 'sms_main_settings' );
		$this->username['TG']  = PWooSMS()->Options( 'persian_woo_tg_username', 'sms_main_settings' );

		$this->password['SMS'] = PWooSMS()->Options( 'persian_woo_sms_password', 'sms_main_settings' );
		$this->password['TG']  = PWooSMS()->Options( 'persian_woo_tg_password', 'sms_main_settings' );

		$this->from['SMS'] = PWooSMS()->Options( 'persian_woo_sms_sender', 'sms_main_settings' );
		$this->from['TG']  = PWooSMS()->Options( 'persian_woo_tg_sender', 'sms_main_settings' );
	}

	public static function get_sms_gateway() {

		$gateway = array(
			'none'           => 'انتخاب کنید',
			'sepehritc'      => 'SepehrITC.com',
			'paniz'          => 'PanizSMS.com',
			'maxsms'         => 'S1.Max-SMS.ir',
			'maxsms2'        => 'S2.Max-SMS.ir',
			'parandsms'      => 'ParandSMS.ir',
			'gamapayamak'    => 'GAMAPayamak.com',
			'limoosms'       => 'LimooSMS.com',
			'smsfa'          => 'SMSFa.ir',
			'aradsms'        => 'Arad-SMS.ir',
			'farapayamak'    => 'FaraPayamak.ir',
			'payamafraz'     => 'PayamAfraz.com',
			'niazpardaz'     => 'SMS.NiazPardaz.com',
			'niazpardaz_'    => 'Login.NiazPardaz.ir',
			'yektasms'       => 'Yektatech.ir',
			'smsbefrest'     => 'SmsBefrest.ir',
			'relax'          => 'Relax.ir',
			'paaz'           => 'Paaz.ir',
			'postgah'        => 'Postgah.info',
			'idehpayam'      => 'IdehPayam.com',
			'azaranpayamak'  => 'Azaranpayamak.ir',
			'smsir'          => 'SMS.ir',
			'manirani'       => 'Manirani.ir',
			'tjp'            => 'TJP.ir',
			'websms'         => 'S1.Websms.ir',
			'payamresan'     => 'Payam-Resan.com',
			'bakhtarpanel'   => 'Bakhtar.xyz',
			'parsgreen'      => 'ParsGreen.com',
			'avalpayam'      => 'Avalpayam.com',
			'iransmsserver'  => 'IranSmsServer.com',
			'ippanel'        => 'IPPanel.com',
			'melipayamak'    => 'MeliPayamak.com',
			'loginpanel'     => 'LoginPanel.ir',
			'nasimnet'       => 'Nasimnet.ir',
			'smshooshmand'   => 'SmsHooshmand.com',
			'smsfor'         => 'SMSFor.ir',
			'chaparpanel'    => 'ChaparPanel.IR',
			'firstpayamak'   => 'FirstPayamak.ir',
			'netpaydar'      => 'SMS.Netpaydar.com',
			'smspishgaman'   => 'Panel.SmsPishgaman.com',
			'parsianpayam'   => 'ParsianPayam.ir',
			'hostiran'       => 'Hostiran.com',
			'iransms'        => 'IranSMS.co',
			'negins'         => 'Negins.com',
			'kavenegar'      => 'Kavenegar.com',
			'afe'            => 'Afe.ir',
			'aradpayamak'    => 'Aradpayamak.net',
			'isms'           => 'iSms.ir',
			'razpayamak'     => 'RazPayamak.com',
			'mihansmscenter' => 'MihanSMSCenter.ir',
			'_0098'          => '0098SMS.com',
			'sefidsms'       => 'SefidSMS.ir',
			'chapargah'      => 'Chapargah.ir',
			'hafezpayam'     => 'HafezPayam.com',
			'farazsms'       => 'FarazSMS.com',
			'mashhadhost'    => 'Mashhadhost.com',
			'mehrpanel'      => 'MehrPanel.ir',
			'kianartpanel'   => 'KianArtPanel.ir',
            'sabanovin' => 'Sabanovin.com'
		);

		return apply_filters( 'persianwoosms_sms_gateway', $gateway );
	}


	function none( $datas ) {
		return false;
	}
	/**
	 * /* پیامک
	 * وب سرویس باید توانایی ارسال پیامک دسته جمعی را داشته باشد . ارسال به چندین شماره در یک بار درخواست وب سرویس
	 */

	/**
	 * Sends SMS via tjp.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function tjp( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		//$from     = $this->from['SMS'];
		$to      = $datas['number'];
		$massage = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( 'http://sms-login.tjp.ir/webservice/?WSDL', array(
			'login'    => $username,
			'password' => $password
		) );
		try {
			$status = $client->sendToMany( $to, $massage );
		} catch ( SoapFault $sf ) {
			$sms_response = $sf->faultcode;
		}
		if ( empty( $sms_response ) ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via Max-SMS.ir - S1
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function maxsms( $datas ) {
		/*
        $response = false;
        $username = $this->username['SMS'];
        $password = $this->password['SMS'];
        $from = $this->from['SMS'];
        $to = $datas['number'];
        $massage = $datas['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }

        $client = new SoapClient('http://login.max-sms.ir/webservice/?WSDL', array('login' => $username,'password' => $password) );
        try
        {
            $status = $client->sendToMany($to , $massage);
        }

        catch (SoapFault $sf)
        {
            $sms_response = $sf->faultcode;
        }
        if (empty($sms_response)) {
            $response = true;
        }

        return $response;
        */

		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		$to       = $datas['number'];

		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}


		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once 'nusoap.php';
		}

		$client                   = new nusoap_client( "http://37.130.202.188/class/sms/wssimple/server.php?wsdl" );
		$client->soap_defencoding = 'UTF-8';
		$client->decode_utf8      = true;

		$result = $client->call( "SendSMS", array(
			'Username'         => $username,
			'Password'         => $password,
			'SenderNumber'     => $from,
			'RecipientNumbers' => $to,
			'Message'          => $massage,
			'Type'             => 'normal'
		) );

		if ( ! empty( $result ) && is_numeric( $result ) ) {
			$response = true;
		}

		return $response;

	}


	/**
	 * Sends SMS via Max-SMS.ir S2
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function maxsms2( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];


		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://panel.max-sms.ir/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}

	/**
	 * Sends SMS via hostiran.com
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function hostiran( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( 'http://sms.hostiran.net/webservice/?WSDL', array(
			'login'    => $username,
			'password' => $password
		) );
		try {
			$status = $client->sendToMany( $to, $massage );
		} catch ( SoapFault $sf ) {
			$sms_response = $sf->faultcode;
		}
		if ( empty( $sms_response ) ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via mehrpanel.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function mehrpanel( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://87.107.121.52/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}
	
	/**
	 * Sends SMS via ParandSMS.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function parandsms( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://87.107.121.52/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}

	/**
	 * Sends SMS via arad-sms.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function aradsms( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://arad-sms.ir/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via smsbefrest.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function smsbefrest( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://87.107.121.52/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via Relax.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function relax( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://onlinepanel.ir/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}
	
	
	/**
	 * Sends SMS via kianartpanel.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function kianartpanel( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://onlinepanel.ir/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}

	/**
	 * Sends SMS via smspishgaman
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function smspishgaman( $datas ) {

		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		$to       = $datas['number'];

		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$i = sizeOf( $to );
		while ( $i -- ) {
			$uNumber = Trim( $to[ $i ] );
			$ret     = &$uNumber;
			if ( substr( $uNumber, 0, 3 ) == '%2B' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 3 ) == '%2b' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 4 ) == '0098' ) {
				$ret = substr( $uNumber, 4 );
			}
			if ( substr( $uNumber, 0, 3 ) == '098' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 3 ) == '+98' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 2 ) == '98' ) {
				$ret = substr( $uNumber, 2 );
			}
			if ( substr( $uNumber, 0, 1 ) == '0' ) {
				$ret = substr( $uNumber, 1 );
			}
			$to[ $i ] = '98' . $ret;
		}

		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once 'nusoap.php';
		}

		try {
			$client                   = new nusoap_client( 'http://82.99.216.45/services/?wsdl', true );
			$client->soap_defencoding = 'UTF-8';
			$client->decode_utf8      = false;

			$results = $client->call( 'Send', array(
				'username'  => $username,
				'password'  => $password,
				'srcNumber' => $from,
				'body'      => $massage,
				'destNo'    => $to,
				'flash'     => '0'
			) );

			foreach ( $results as $result ) {
				if ( ! isset( $result['Mobile'] ) || stripos( $result['ID'], 'e' ) !== false ) {
					return $response = false;
				}
			}

			return $response = true;
		} catch ( Exception $e ) {
			return $response = false;
		}
	}

	/**
	 * Sends SMS via sms.paaz.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function paaz( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://sms.paaz.ir/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}

	/**
	 * Sends SMS via FaraPayamak.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function farapayamak( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://37.228.138.118/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via parsianpayam.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function parsianpayam( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://onepayam.ir/API/Send.asmx?wsdl" );
		try {

			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'flash'    => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);

			$status = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}


		if ( empty( $sms_response ) && $status == 0 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via IranSMSServer.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function iransmsserver( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://sms.iransmsserver.ir/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via niazpardaz.com
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function niazpardaz( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://37.228.138.118/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}

	/**
	 * Sends SMS via niazpardaz.com
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function niazpardaz_( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://185.13.231.178/SendService.svc?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'userName'       => $username,
				'password'       => $password,
				'fromNumber'     => $from,
				'toNumbers'      => $to,
				'messageContent' => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'        => false,
				'udh'            => "",
				'recId'          => array( 0 ),
				'status'         => 0
			);
			$status     = $client->SendSms( $parameters )->SendSMSResult;

		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && isset( $status ) && $status == 0 ) {
			$response = true;
		}

		return $response;
	}

	/**
	 * Sends SMS via SepehrITC.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function sepehritc( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://37.228.138.118/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via panizSMS.COM
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function paniz( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://37.228.138.118/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via payamafraz.com
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function payamafraz( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://payamafraz.ir/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via yektasms.com
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function yektasms( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://37.228.138.118/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via gamapayamak.com
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function gamapayamak( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://37.228.138.118/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via limoosms.com
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function limoosms( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://37.228.138.118/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via smsfa
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function smsfa( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( 'http://smsfa.net/API/Send.asmx?WSDL' );
		try {
			$status = $client->SendSms(
				array(
					'username' => $username,
					'password' => $password,
					'from'     => $from,
					'to'       => $to,
					'text'     => $massage,
					'flash'    => false,
					'udh'      => ''
				)
			)->SendSmsResult;
		} catch ( SoapFault $sf ) {
			$sms_response = $sf->faultcode;
		}
		if ( empty( $sms_response ) && $status > 0 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via postgah
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function postgah( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( 'http://postgah.net/API/Send.asmx?WSDL' );
		try {
			$status = $client->SendSms(
				array(
					'username' => $username,
					'password' => $password,
					'from'     => $from,
					'to'       => $to,
					'text'     => $massage,
					'flash'    => false,
					'udh'      => ''
				)
			)->SendSmsResult;
		} catch ( SoapFault $sf ) {
			$sms_response = $sf->faultcode;
		}
		if ( empty( $sms_response ) && isset( $status ) && $status == 0 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via azaranpayamak
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function azaranpayamak( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( 'http://azaranpayamak.ir/API/Send.asmx?WSDL' );
		try {
			$status = $client->SendSms(
				array(
					'username' => $username,
					'password' => $password,
					'from'     => $from,
					'to'       => $to,
					'text'     => $massage,
					'flash'    => false,
					'udh'      => ''
				)
			)->SendSmsResult;
		} catch ( SoapFault $sf ) {
			$sms_response = $sf->faultcode;
		}
		if ( empty( $sms_response ) && $status == 0 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via manirani.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function manirani( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( 'http://sms.manirani.ir/API/Send.asmx?WSDL' );
		try {
			$status = $client->SendSms(
				array(
					'username' => $username,
					'password' => $password,
					'from'     => $from,
					'to'       => $to,
					'text'     => $massage,
					'flash'    => false,
					'udh'      => ''
				)
			)->SendSmsResult;
		} catch ( SoapFault $sf ) {
			$sms_response = $sf->faultcode;
		}
		if ( empty( $sms_response ) && $status > 0 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via sms.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function smsir( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$to = implode( ',', $datas['number'] );

		$content = 'user=' . rawurlencode( $username ) .
		           '&pass=' . rawurlencode( $password ) .
		           '&to=' . rawurlencode( $to ) .
		           '&lineNo=' . rawurlencode( $from ) .
		           '&text=' . rawurlencode( $massage );

		$curlSession = curl_init();
		curl_setopt( $curlSession, CURLOPT_URL, 'http://ip.sms.ir/SendMessage.ashx?' . $content );
		curl_setopt( $curlSession, CURLOPT_BINARYTRANSFER, true );
		curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );
		$smsir_response = curl_exec( $curlSession );
		curl_close( $curlSession );

		if ( strtolower( $smsir_response ) == 'ok' || $smsir_response == 'ارسال با موفقیت انجام شد .' || $smsir_response == 'ارسال با موفقیت انجام شد' || $smsir_response == 'ارسال با موفقیت انجام شد.' ) {
			$response = true;
		}

		return $response;

	}

	/**
	 * Sends SMS via afe.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function afe( $datas ) {

		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];

		if ( empty( $username ) || empty( $password ) ) {
			return false;
		}

		$response = true;

		foreach ( $datas['number'] as $number ) {

			$curlSession = curl_init();
			curl_setopt( $curlSession, CURLOPT_URL, 'http://www.afe.ir/Url/SendSMS?username=' . $username . '&Password=' . $password . '&Number=' . $from . '&mobile=' . $number . '&sms=' . urlencode( $massage ) );
			curl_setopt( $curlSession, CURLOPT_BINARYTRANSFER, true );
			curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );
			$iransms_response = curl_exec( $curlSession );
			curl_close( $curlSession );

			if ( empty( $iransms_response ) || stripos( $iransms_response, 'success' ) === false ) {
				$response = false;
			}
		}

		return $response;
	}


	/**
	 * Sends SMS via iransms.co
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function iransms( $datas ) {

		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];

		if ( empty( $username ) || empty( $password ) ) {
			return false;
		}

		$response = true;

		foreach ( $datas['number'] as $number ) {

			$curlSession = curl_init();
			curl_setopt( $curlSession, CURLOPT_URL, 'http://www.iransms.co/URLSend.aspx?Username=' . $username . '&Password=' . $password . '&PortalCode=' . $from . '&Mobile=' . $number . '&Message=' . urlencode( $massage ) . '&Flash=0' );
			curl_setopt( $curlSession, CURLOPT_BINARYTRANSFER, true );
			curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );
			$iransms_response = curl_exec( $curlSession );
			curl_close( $curlSession );

			if ( abs( $iransms_response ) < 30 ) {
				$response = false;
			}
		}


		return $response;
	}


	/**
	 * Sends SMS via http://negins.com
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function negins( $datas ) {

		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];

		if ( empty( $username ) || empty( $password ) ) {
			return false;
		}

		$response = true;

		foreach ( $datas['number'] as $number ) {

			$curlSession = curl_init();
			curl_setopt( $curlSession, CURLOPT_URL, 'http://negins.com/URLSend.aspx?Username=' . $username . '&Password=' . $password . '&PortalCode=' . $from . '&Mobile=' . $number . '&Message=' . urlencode( $massage ) . '&Flash=0' );
			curl_setopt( $curlSession, CURLOPT_BINARYTRANSFER, true );
			curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );
			$iransms_response = curl_exec( $curlSession );
			curl_close( $curlSession );

			if ( abs( $iransms_response ) < 30 ) {
				$response = false;
			}
		}


		return $response;
	}


	/**
	 * Sends SMS via sms.netpaydar.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function netpaydar( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$to = implode( ',', $datas['number'] );

		$content = 'user=' . rawurlencode( $username ) .
		           '&pass=' . rawurlencode( $password ) .
		           '&to=' . rawurlencode( $to ) .
		           '&lineNo=' . rawurlencode( $from ) .
		           '&text=' . rawurlencode( $massage );

		$curlSession = curl_init();
		curl_setopt( $curlSession, CURLOPT_URL, 'http://sms.netpaydar.com/SendMessage.ashx?' . $content );
		curl_setopt( $curlSession, CURLOPT_BINARYTRANSFER, true );
		curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );
		$netpaydar_response = curl_exec( $curlSession );
		curl_close( $curlSession );

		if ( strtolower( $netpaydar_response ) == 'ok' || $netpaydar_response == 'ارسال با موفقیت انجام شد .' || $netpaydar_response == 'ارسال با موفقیت انجام شد' || $netpaydar_response == 'ارسال با موفقیت انجام شد.' ) {
			$response = true;
		}

		return $response;

	}


	/**
	 * Sends SMS via smshooshmand
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function smshooshmand( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return false;
		}
		$to = $datas['number'];

		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/nusoap.php';
		}

		$client = new nusoap_client( "http://185.4.28.100/class/sms/webservice/server.php?wsdl" );

		$client->soap_defencoding = 'UTF-8';
		$client->decode_utf8      = true;
		$client->setCredentials( $username, $password, "basic" );

		$i = sizeOf( $to );
		while ( $i -- ) {
			$uNumber = Trim( $to[ $i ] );
			$ret     = &$uNumber;
			if ( substr( $uNumber, 0, 3 ) == '%2B' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 3 ) == '%2b' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 4 ) == '0098' ) {
				$ret = substr( $uNumber, 4 );
			}
			if ( substr( $uNumber, 0, 3 ) == '098' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 3 ) == '+98' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 2 ) == '98' ) {
				$ret = substr( $uNumber, 2 );
			}
			if ( substr( $uNumber, 0, 1 ) == '0' ) {
				$ret = substr( $uNumber, 1 );
			}
			$to[ $i ] = '+98' . $ret;
		}

		$parameters = array(
			'from'       => $from,
			'rcpt_array' => $to,
			'msg'        => $massage,
			'type'       => 'normal'
		);

		$result = $client->call( "enqueue", $parameters );
		if ( ( isset( $result['state'] ) && $result['state'] == 'done' ) && ( isset( $result['errnum'] ) && ( $result['errnum'] == '100' || $result['errnum'] == 100 ) ) ) {
			$response = true;
		}

		return $response;
	}

	/**
	 * Sends SMS via smsfor
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function smsfor( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return false;
		}
		$to = $datas['number'];

		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/nusoap.php';
		}

		$i = sizeOf( $to );
		while ( $i -- ) {
			$uNumber = Trim( $to[ $i ] );
			$ret     = &$uNumber;
			if ( substr( $uNumber, 0, 3 ) == '%2B' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 3 ) == '%2b' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 4 ) == '0098' ) {
				$ret = substr( $uNumber, 4 );
			}
			if ( substr( $uNumber, 0, 3 ) == '098' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 3 ) == '+98' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 2 ) == '98' ) {
				$ret = substr( $uNumber, 2 );
			}
			if ( substr( $uNumber, 0, 1 ) == '0' ) {
				$ret = substr( $uNumber, 1 );
			}
			$to[ $i ] = '0' . $ret;
		}

		$client                   = new nusoap_client( 'http://www.smsfor.ir/webservice/soap/smsService.php?wsdl', 'wsdl' );
		$client->soap_defencoding = 'UTF-8';
		$client->decode_utf8      = false;

		$params = array(
			'username'         => $username,
			'password'         => $password,
			'sender_number'    => array( $from ),
			'receiver_number'  => $to,
			'note'             => array( $massage ),
			'date'             => array(),
			'request_uniqueid' => array(),
			'flash'            => false,
			'onlysend'         => 'ok',
		);
		$md_res = $client->call( "send_sms", $params );

		if ( empty( $md_res['faultcode'] ) && empty( $md_res['faultstring'] ) && is_numeric( str_ireplace( ',', '', $md_res[0] ) ) ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via idehpayam
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function idehpayam( $datas ) {

		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}
		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/nusoap.php';
		}

		$client                   = new nusoap_client( 'http://panel.idehpayam.com/class/sms/wssimple/server.php?wsdl' );
		$client->soap_defencoding = 'UTF-8';
		$client->decode_utf8      = true;

		$result = $client->call( "SendSMS", array(
				'Username'         => $username,
				'Password'         => $password,
				'SenderNumber'     => $from,
				'RecipientNumbers' => $to,
				'Message'          => $massage,
				'Type'             => 'normal'
			)
		);

		if ( substr( $result, 0, 3 ) == '200' ) {
			$response = true;
		}


		return $response;
	}


	/**
	 * Sends SMS via websms
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function websms( $datas ) {

		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$to = implode( ',', $datas['number'] );

		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$content         = 'cusername=' . rawurlencode( $username ) .
		                   '&cpassword=' . rawurlencode( $password ) .
		                   '&cmobileno=' . rawurlencode( $to ) .
		                   '&csender=' . rawurlencode( $from ) .
		                   '&cbody=' . rawurlencode( $massage );
		$websms_response = file_get_contents( 'http://s1.websms.ir/wservice.php?' . $content );
		if ( strlen( $websms_response ) > 8 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via BakhtarPanel
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function bakhtarpanel( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$to = implode( ',', $datas['number'] );

		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/nusoap.php';
		}

		$type    = '4';
		$country = "98";
		$file    = false;
		$client  = new nusoap_client( 'http://login.bakhtar.xyz/webservice/server.asmx?wsdl' );
		$status  = explode( ',', ( $client->call( 'Sendsms', array(
			$type,
			$from,
			$username,
			$password,
			$country,
			$massage,
			$to,
			$file
		) ) ) );
		if ( count( $status ) > 1 && $status[0] == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via nasimnet
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function nasimnet( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		$to       = $datas['number'];

		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}


		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once 'nusoap.php';
		}

		$client                   = new nusoap_client( "http://37.130.202.188/class/sms/wssimple/server.php?wsdl" );
		$client->soap_defencoding = 'UTF-8';
		$client->decode_utf8      = true;

		$result = $client->call( "SendSMS", array(
			'Username'         => $username,
			'Password'         => $password,
			'SenderNumber'     => $from,
			'RecipientNumbers' => $to,
			'Message'          => $massage,
			'Type'             => 'normal'
		) );

		if ( ! empty( $result ) && is_numeric( $result ) ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via IPPanel
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function ippanel( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		$to       = $datas['number'];

		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}


		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once 'nusoap.php';
		}

		$client                   = new nusoap_client( "http://37.130.202.188/class/sms/wssimple/server.php?wsdl" );
		$client->soap_defencoding = 'UTF-8';
		$client->decode_utf8      = true;

		$result = $client->call( "SendSMS", array(
			'Username'         => $username,
			'Password'         => $password,
			'SenderNumber'     => $from,
			'RecipientNumbers' => $to,
			'Message'          => $massage,
			'Type'             => 'normal'
		) );

		if ( ! empty( $result ) && is_numeric( $result ) ) {
			$response = true;
		}

		return $response;
	}
	
	
	/**
	 * Sends SMS via mashhadhost
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function mashhadhost( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		$to       = $datas['number'];

		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}


		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once 'nusoap.php';
		}

		$client                   = new nusoap_client( "http://37.130.202.188/class/sms/wssimple/server.php?wsdl" );
		$client->soap_defencoding = 'UTF-8';
		$client->decode_utf8      = true;

		$result = $client->call( "SendSMS", array(
			'Username'         => $username,
			'Password'         => $password,
			'SenderNumber'     => $from,
			'RecipientNumbers' => $to,
			'Message'          => $massage,
			'Type'             => 'normal'
		) );

		if ( ! empty( $result ) && is_numeric( $result ) ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via MeliPayamak.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function melipayamak( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://37.228.138.118/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via Payam-Resan
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function payamresan( $datas ) {

		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$to = implode( ',', $datas['number'] );

		$content = 'http://www.payam-resan.com/APISend.aspx?UserName=' . rawurlencode( $username ) .
		           '&Password=' . rawurlencode( $password ) .
		           '&To=' . rawurlencode( $to ) .
		           '&From=' . rawurlencode( $from ) .
		           '&Text=' . rawurlencode( $massage );

		if ( extension_loaded( 'curl' ) ) {
			$curlSession = curl_init();
			curl_setopt( $curlSession, CURLOPT_URL, $content );
			curl_setopt( $curlSession, CURLOPT_BINARYTRANSFER, true );
			curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );
			$payamresan_response = curl_exec( $curlSession );
			curl_close( $curlSession );
		} else {
			$payamresan_response = file_get_contents( $content );
		}

		if ( strtolower( $payamresan_response ) == '1' || $payamresan_response == 1 ) {
			$response = true;
		}

		return $response;

	}

	/**
	 * Sends SMS via kavenegar
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function kavenegar( $datas ) {

		$response = false;
		$username = $this->username['SMS'];
		//    $password = $this->password['SMS'];
		$from    = $this->from['SMS'];
		$massage = $datas['sms_body'];
		if ( empty( $username ) ) {
			return $response;
		}

		$messages = urlencode( $massage );
		$to       = implode( ',', $datas['number'] );
		$url      = "https://api.kavenegar.com/v1/$username/sms/send.json?sender=$from&receptor=$to&message=$messages";
		if ( extension_loaded( 'curl' ) ) {
			$ch = curl_init( $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response   = curl_exec( $ch );
			$curl_errno = curl_errno( $ch );
			curl_close( $ch );
			if ( $curl_errno ) {
				return false;
			}
		} else {
			$response = @file_get_contents( $url );
		}
		if ( false !== $response ) {
			$json_response = json_decode( $response );
			if ( $json_response ) {
				$json_return = $json_response->return;
				if ( $json_return->status == 200 ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Sends SMS via ParsGreen
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function parsgreen( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		//  $password = $this->password['SMS'];
		$from    = $this->from['SMS'];
		$massage = $datas['sms_body'];
		if ( empty( $username ) ) {
			return $response;
		}

		$to = $datas['number'];

		try {
			$parameters['signature'] = $username;
			$parameters['from']      = $from;
			$parameters['to']        = $to;
			$parameters['text']      = $massage;
			$parameters['isFlash']   = false;
			$parameters['udh']       = "";
			$parameters['retStr']    = array( 0 );
			$parameters['success']   = 0;
			$client                  = new SoapClient( "http://login.parsgreen.com/Api/SendSMS.asmx?wsdl" );
			$status                  = (array) $client->SendGroupSMS( $parameters )->SendGroupSMSResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status[0] == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via Avalpayam
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function avalpayam( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return false;
		}
		$to = $datas['number'];

		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/nusoap.php';
		}

		$client = new nusoap_client( "http://www.avalpayam.com/class/sms/webservice/server.php?wsdl" );

		$client->soap_defencoding = 'UTF-8';
		$client->decode_utf8      = true;
		$client->setCredentials( $username, $password, "basic" );

		$i = sizeOf( $to );
		while ( $i -- ) {
			$uNumber = Trim( $to[ $i ] );
			$ret     = &$uNumber;
			if ( substr( $uNumber, 0, 3 ) == '%2B' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 3 ) == '%2b' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 4 ) == '0098' ) {
				$ret = substr( $uNumber, 4 );
			}
			if ( substr( $uNumber, 0, 3 ) == '098' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 3 ) == '+98' ) {
				$ret = substr( $uNumber, 3 );
			}
			if ( substr( $uNumber, 0, 2 ) == '98' ) {
				$ret = substr( $uNumber, 2 );
			}
			if ( substr( $uNumber, 0, 1 ) == '0' ) {
				$ret = substr( $uNumber, 1 );
			}
			$to[ $i ] = '+98' . $ret;
		}

		$parameters = array(
			'from'       => $from,
			'rcpt_array' => $to,
			'msg'        => $massage,
			'type'       => 'normal'
		);

		$result = $client->call( "enqueue", $parameters );
		if ( ( isset( $result['state'] ) && $result['state'] == 'done' ) && ( isset( $result['errnum'] ) && ( $result['errnum'] == '100' || $result['errnum'] == 100 ) ) ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via loginpanel.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function loginpanel( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://87.107.121.52/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via loginpanel.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function chaparpanel( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://87.107.121.52/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via firstpayamak.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function firstpayamak( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		try {
			$params = array(
				'username'         => $username,
				'password'         => $password,
				'recipientNumbers' => $to,
				'senderNumbers'    => array( $from ),
				'messageBodies'    => array( $massage ),
			);

			$client  = new SoapClient( "http://ui.firstpayamak.ir/webservice/v2.asmx?WSDL" );
			$results = $client->SendSMS( $params );
			$sends   = $results->SendSMSResult->long;
		} catch ( SoapFault $ex ) {

		}

		$sends = is_array( $sends ) ? $sends : ( (array) $sends );

		foreach ( $sends as $send ) {
			if ( isset( $send ) && $send > 1000 ) {
				$response = true;
				break;
			}
		}

		return $response;
	}


	function aradpayamak( $datas ) {

		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];

		$massage = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$to = implode( ';', $datas['number'] );

		try {

			$client             = new SoapClient( "http://aradpayamak.net/APPs/SMS/WebService.php?wsdl" );
			$sendsms_parameters = array(
				'domain'   => 'aradpayamak.net',
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => $massage,
				'isflash'  => 0,
			);

			$status = call_user_func_array( array( $client, 'sendSMS' ), $sendsms_parameters );

		} catch ( SoapFault $ex ) {

		}

		return ! empty( $status ) ? true : false;
	}


	function isms( $datas ) {

		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];

		$massage = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$data = array(
			'username' => $username,
			'password' => $password,
			'mobiles'  => $datas['number'],
			'body'     => $massage,
			'sender'   => $from,
		);

		$data = http_build_query( $data );
		$ch   = curl_init();
		curl_setopt( $ch, CURLOPT_URL, 'http://ws3584.isms.ir/sendWS' );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		$result = json_decode( curl_exec( $ch ), true );
		if ( ! empty( $result["code"] ) && ! empty( $result["message"] ) ) {
			return false;
		} else {
			return true;
		}
	}


	/**
	 * Sends SMS via RazPayamak.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function razpayamak( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://37.228.138.118/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);
			$status     = $client->SendSms( $parameters )->SendSmsResult;
		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via 0098sms.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function _0098( $datas ) {

		$username  = $this->username['SMS'];
		$password  = $this->password['SMS'];
		$from      = $this->from['SMS'];
		$recievers = $datas['number'];
		$massage   = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return false;
		}

		$response = true;

		foreach ( (array) $recievers as $to ) {

			$content = 'http://www.0098sms.com/sendsmslink.aspx?DOMAIN=0098' .
			           '&USERNAME=' . rawurlencode( $username ) .
			           '&PASSWORD=' . rawurlencode( $password ) .
			           '&FROM=' . rawurlencode( $from ) .
			           '&TO=' . rawurlencode( $to ) .
			           '&TEXT=' . rawurlencode( $massage );

			if ( extension_loaded( 'curl' ) ) {
				$curlSession = curl_init();
				curl_setopt( $curlSession, CURLOPT_URL, $content );
				curl_setopt( $curlSession, CURLOPT_BINARYTRANSFER, true );
				curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );
				$_response = curl_exec( $curlSession );
				curl_close( $curlSession );
			} else {
				$_response = file_get_contents( $content );
			}
			$_response = intval( $_response );

			if ( ! isset( $_response ) || $_response != '0' ) {
				$response = false;
			}
		}

		return $response;

	}

	/**
	 * Sends SMS via SefidSMS.ir
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function sefidsms( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$client = new SoapClient( "http://api.sefidsms.ir/post/send.asmx?wsdl" );
		try {
			$encoding   = "UTF-8";
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from'     => $from,
				'to'       => $to,
				'text'     => iconv( $encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash'  => false,
				'udh'      => "",
				'recId'    => array( 0 ),
				'status'   => 0
			);

			$status = $client->SendSms( $parameters )->SendSmsResult;

		} catch ( SoapFault $ex ) {
			$sms_response = $ex->faultstring;
		}

		if ( empty( $sms_response ) && $status == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends SMS via chapargah
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function chapargah( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$to       = $datas['number'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$massage = iconv( 'UTF-8', 'UTF-8//TRANSLIT', $massage );

		$client = new SoapClient( 'http://panel.chapargah.ir/API/Send.asmx?WSDL' );
		try {
			$status = $client->SendSms(
				array(
					'username' => $username,
					'password' => $password,
					'from'     => $from,
					'to'       => $to,
					'text'     => $massage,
					'flash'    => false,
					'recId'    => array( 0 ),
					'status'   => 0
				)
			)->SendSmsResult;
		} catch ( SoapFault $sf ) {
			$sms_response = $sf->faultcode;
		}
		if ( empty( $sms_response ) && isset( $status ) && $status == 0 ) {
			$response = true;
		}

		return $response;
	}

	/**
	 * Sends SMS via http://hafezpayam.com
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function hafezpayam( $datas ) {
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return false;
		}
		$response = true;
		foreach ( $datas['number'] as $number ) {
			$curlSession = curl_init();
			curl_setopt( $curlSession, CURLOPT_URL, 'http://hafezpayam.com/URLSend.aspx?Username=' . $username . '&Password=' . $password . '&PortalCode=' . $from . '&Mobile=' . $number . '&Message=' . urlencode( $massage ) . '&Flash=0' );
			curl_setopt( $curlSession, CURLOPT_BINARYTRANSFER, true );
			curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );
			$iransms_response = curl_exec( $curlSession );
			curl_close( $curlSession );

			if ( abs( $iransms_response ) < 30 ) {
				$response = false;
			}
		}

		return $response;
	}

	/**
	 * Sends SMS via farazsms
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function farazsms( $datas ) {
		$response = false;
		$username = $this->username['SMS'];
		$password = $this->password['SMS'];
		$from     = $this->from['SMS'];
		$massage  = $datas['sms_body'];
		$to       = $datas['number'];

		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}


		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once 'nusoap.php';
		}

		$client                   = new nusoap_client( "http://37.130.202.188/class/sms/wssimple/server.php?wsdl" );
		$client->soap_defencoding = 'UTF-8';
		$client->decode_utf8      = true;

		$result = $client->call( "SendSMS", array(
			'Username'         => $username,
			'Password'         => $password,
			'SenderNumber'     => $from,
			'RecipientNumbers' => $to,
			'Message'          => $massage,
			'Type'             => 'normal'
		) );

		if ( ! empty( $result ) && is_numeric( $result ) ) {
			$response = true;
		}

		return $response;
	}
    
    
	function sabanovin( $data ) {
		
        $response = false;
        $apiKey = $this->username['SMS'];
        $senderPhone = $this->from['SMS'];
        $message = $data['sms_body'];
        $to = $data['number'];
        
        if(empty($apiKey) or empty($senderPhone)){
            return $response;
        }
        
        if(!function_exists('curl_version')){
            return $response;
        }
        
        $WSConnection = curl_init('http://api.sabanovin.com/v1/'.$apiKey.'/sms/send.json');
        curl_setopt($WSConnection, CURLOPT_POST, true);
        curl_setopt($WSConnection, CURLOPT_POSTFIELDS, array(
            'gateway' => $senderPhone,
            'text' => $message,
            'to' => implode($to, ',')
        ));
        
        curl_setopt($WSConnection, CURLOPT_RETURNTRANSFER, true);
        $WSResult = json_decode(curl_exec($WSConnection));
        curl_close($WSConnection);
        
        if($WSResult->status->code == 200){
            $response = true;
        }
        
		return $response;
	}

//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------
	/**
	 * تلگرام
	 */
	public static function get_tg_gateway() {
		$gateway = array(
			'none'            => 'انتخاب کنید',
			'tg_bakhtarpanel' => 'Bakhtar.xyz',
			'tg_ilamclub'     => 'ILAMClub.IR',
		);

		return apply_filters( 'persianwoosms_tg_gateway', $gateway );
	}

	/**
	 * Sends tg via BakhtarPanel
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function tg_bakhtarpanel( $datas ) {
		$response = false;
		$username = $this->username['TG'];
		$password = $this->password['TG'];
		$from     = $this->from['TG'];
		$from     = $from ? $from : '1';
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$to = implode( ',', $datas['number'] );

		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/nusoap.php';
		}

		$type    = '3';
		$country = '';
		$file    = false;
		$client  = new nusoap_client( 'http://login.bakhtar.xyz/webservice/server.asmx?wsdl' );
		$status  = explode( ',', ( $client->call( 'Sendsms', array(
			$type,
			$from,
			$username,
			$password,
			$country,
			$massage,
			$to,
			$file
		) ) ) );
		if ( count( $status ) > 1 && $status[0] == 1 ) {
			$response = true;
		}

		return $response;
	}


	/**
	 * Sends tg via IlamClub.IR
	 *
	 * @param $datas
	 *
	 * @return bool
	 */
	function tg_ilamclub( $datas ) {
		$response = false;
		$username = $this->username['TG'];
		$password = $this->password['TG'];
		$from     = $this->from['TG'];
		$from     = $from ? $from : '1';
		$massage  = $datas['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}

		$to = implode( ',', $datas['number'] );

		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/nusoap.php';
		}

		$type    = '3';
		$country = '';
		$file    = false;
		$client  = new nusoap_client( 'http://ilamclub.ir/webservice/server.asmx?wsdl' );
		$status  = explode( ',', ( $client->call( 'Sendsms', array(
			$type,
			$from,
			$username,
			$password,
			$country,
			$massage,
			$to,
			$file
		) ) ) );
		if ( count( $status ) > 1 && $status[0] == 1 ) {
			$response = true;
		}

		return $response;
	}


}