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
     * @param $ruc 
     * @param $usuario_sol 
     * @param $clave_sol 
     */
    function __construct(String $ruc, String $usuario_sol, String $clave_sol){
        $this->ruc = $ruc;
        $this->usuario_sol = $usuario_sol;
        $this->clave_sol = $clave_sol;

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $this->soapClient = new \SoapClient($this->webService,['stream_context' => $context]);
        $this->soapHeader = WsSecurity::createWsSecuritySoapHeader($this->getUser(), $this->clave_sol, true);
    }

    /**
     * Recibe la ruta del archivo para subirlo
     * @param  String $numero_transaccion
     * @param  String $ruta
     * @return boolean
     */
    public function recibirArchivo(String $numero_transaccion, String $file)
    {
        
        try {
            
            $this->soapClient->__setSoapHeaders($this->soapHeader);

            //preparo archivo , lo codifico en base64
            $fileString = base64_encode(fread(fopen($file, "r"), filesize($file)));
            $result = $this->soapClient->__soapCall('recibirArchivo', ['numeroTransaccion' => $numero_transaccion ,'informacionArchivo' => $fileString]);

            return $result;

        } catch (\SOAPFaultException $e) {
            error_log("recibirArchivo SOAPFaultException: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("recibirArchivo Exception: " . $e->getMessage());
        }

        return false;
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
        $macAddress = MacAddress::getCurrentMacAddress('enp1s0');//MacAddress::generateMacAddress();
        $ipAddress = '192.168.1.26';//ClientIP::get();

        $user = [$this->ruc.$this->usuario_sol, $macAddress, $ipAddress, 2];

        return implode('|', $user);
    }

}

$seida = new Seida('20312239117','MODDATOS', 'moddatos');
//$seida = new Seida('20100010136','PILOTONS', 'moddatos');
$rs = $seida->recibirArchivo('0101','0101.zip');
echo var_dump($rs);
echo var_dump($seida->getUser());
//enp1s0
?>