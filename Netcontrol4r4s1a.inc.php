<?php
class Netcontrol4r4s1a
{
    private $ip;
    private $port;
    private $user;
    private $pass;

    private $registers = [];
    private $config;

    function __construct()
    {
        $this->registers = [
            'Sensor1' => ['title' => 'T1', 'measure' => 'Temperature', 'address' => '25', 'access' => 'r'],
            'Sensor2' => ['title' => 'Hr', 'measure' => 'Humidity', 'address' => '28', 'access' => 'r'],
            'Sensor3' => ['title' => 'T2', 'measure' => 'Temperature', 'address' => '29', 'access' => 'r'],
            'Sensor4' => ['title' => '', 'measure' => '', 'address' => '', 'access' => 'r'],
            'Alarm'   => ['title' => '', 'measure' => '', 'address' => '', 'access' => 'r'],
            'Relay1'  => ['title' => '', 'measure' => '', 'address' => '9', 'access' => 'rw'],
            'Relay2'  => ['title' => '', 'measure' => '', 'address' => '10', 'access' => 'rw'],
            'Relay3'  => ['title' => '', 'measure' => '', 'address' => '11', 'access' => 'rw'],
            'Relay4'  => ['title' => '', 'measure' => '', 'address' => '12', 'access' => 'rw'],
        ];
    }
    function fetchUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->user:$this->pass");

        if (($response = curl_exec($ch)) === false) {
            $response = curl_error($ch);
        }
        curl_close($ch);

        return $response;
    }



    function init($ip, $port = 80, $user = 'admin', $pass = 'admin')
    {
        $this->user = $user;
        $this->pass = $pass;
        
        $urlRegs = "http://" . $ip . ":" . $port . "/iocfg.js";
        $urlRes  = "http://" . $ip . ":" . $port . "/iocfg.js"; // die($url);
      
        $response = $this->fetchUrl($urlRegs);

        $this->config = $response;
    }

    function readRegister() {}

    function writeRegister() {}

    public function showConfig()
    {

        return $this->config;
    }
}
