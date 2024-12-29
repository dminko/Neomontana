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
            'Sensor1' => ['title' => '', 'measure' => '', 'address' => '', 'readable' => 'yes', 'writable' => 'no'],
            'Sensor2' => ['title' => '', 'measure' => '', 'address' => '', 'readable' => 'yes', 'writable' => 'no'],
            'Sensor3' => ['title' => '', 'measure' => '', 'address' => '', 'readable' => 'yes', 'writable' => 'no'],
            'Sensor4' => ['title' => '', 'measure' => '', 'address' => '', 'readable' => 'yes', 'writable' => 'no'],
            'Alarm'   => ['title' => '', 'measure' => '', 'address' => '', 'readable' => 'yes', 'writable' => 'no'],
            'Relay1'  => ['title' => '', 'measure' => '', 'address' => '', 'readable' => 'yes', 'writable' => 'yes'],
            'Relay2'  => ['title' => '', 'measure' => '', 'address' => '', 'readable' => 'yes', 'writable' => 'yes'],
            'Relay3'  => ['title' => '', 'measure' => '', 'address' => '', 'readable' => 'yes', 'writable' => 'yes'],
            'Relay4'  => ['title' => '', 'measure' => '', 'address' => '', 'readable' => 'yes', 'writable' => 'yes'],
        ];
    }

    function init($ip, $port = 80, $user = 'admin', $pass = 'admin')
    {
        $url = "http://" . $ip . ":" . $port . "/iocfg.js"; // die($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");

        if (($response = curl_exec($ch)) === false) {
            $response = curl_error($ch);
        }
        curl_close($ch);

        $this->config = $response;
    }

    function readRegister() {}

    function writeRegister() {}

    public function showConfig()
    {

        return $this->config;
    }
}
