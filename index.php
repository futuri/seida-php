<?php
include 'vendor/autoload.php';

use WsdlToPhp\WsSecurity\WsSecurity;
use BlakeGardner\MacAddress;

/**
 * Seida SUNAT
 */
class Seida
{
	
	protected $ruc;
	protected $usuario_sol;
	protected $clave_sol;
	protected $soapHeader;
	protected $soapClient;
	protected $webService = 'https://test.sunat.gob.pe:444/ol-ad-itseida-ws/ReceptorService.htm?wsdl';

	/**
	* @param $usuario_sol 
	* @param $clave_sol 
	*/
	function __construct(String $ruc, String $usuario_sol, String $clave_sol){
		$this->ruc = $ruc;
		$this->usuario_sol = $usuario_sol;
		$this->clave_sol = $clave_sol;

		$this->soapClient = new \SoapClient('https://test.sunat.gob.pe:444/ol-ad-itseida-ws/ReceptorService.htm?wsdl');
		$this->soapHeader = WsSecurity::createWsSecuritySoapHeader($this->getUser(), $this->clave_sol, true);
	}

	public function call(String $function, Array $arguments)
	{
		$this->soapClient->__setSoapHeaders($this->soapHeader);
		$this->soapClient->__soapCall($function, $arguments);
	}


	/*
	private function setHeader()
	{
		$this->soapClient->__setSoapHeaders($this->soapHeader);
	}

	public function getHeader()
	{
		return $this->soapHeader;
	}
	*/

	public function getUser()
	{
		$macAddress = MacAddress::generateMacAddress();
		$ipAddress = $this->getIp();

		$user = [$this->ruc, $this->usuario_sol, $macAddress, $ipAddress, 2];

		return implode('|', $user);
	}

	//revisar esta logica mas tarde
	private function getIp()
	{
        if ( isset($_SERVER["HTTP_CLIENT_IP"]) )
        {
            return $_SERVER["HTTP_CLIENT_IP"];

        } elseif ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ) {

            return $_SERVER["HTTP_X_FORWARDED_FOR"];

        } elseif ( isset($_SERVER["HTTP_X_FORWARDED"]) ) {

            return $_SERVER["HTTP_X_FORWARDED"];

        } elseif ( isset($_SERVER["HTTP_FORWARDED_FOR"]) ) {

            return $_SERVER["HTTP_FORWARDED_FOR"];

        } elseif ( isset($_SERVER["HTTP_FORWARDED"]) ) {

            return $_SERVER["HTTP_FORWARDED"];

        } else {
            return $_SERVER["REMOTE_ADDR"];
        }
	}

}

$seida = new Seida('20312239117','MODDATOS', 'moddatos');
echo var_dump($seida->getUser());

?>