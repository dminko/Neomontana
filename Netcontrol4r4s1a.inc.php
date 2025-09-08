<?php
class Netcontrol4r4s1a
{
    private $ip;
    private $port;
    private $user;
    private $pass;

    private $registers = [];
    private $data;
    private $urlIOChange;
    private $error;

    function __construct()
    {
        $this->registers = [
            'Sensor1' => ['title' => 'T1', 'measure' => 'Temperature', 'address' => '24', 'access' => 'r'],
            'Sensor2' => ['title' => 'Hr', 'measure' => 'Humidity', 'address' => '27', 'access' => 'r'],
            'Sensor3' => ['title' => 'T2', 'measure' => 'Temperature', 'address' => '28', 'access' => 'r'],
            'Sensor4' => ['title' => 'Sensor4', 'measure' => '', 'address' => '29', 'access' => 'r'],
            'Alarm'   => ['title' => 'Alarm', 'measure' => '', 'address' => '30', 'access' => 'r'],
            'Relay1'  => ['title' => 'Relay1', 'measure' => '', 'address' => '8', 'access' => 'rw'],
            'Relay2'  => ['title' => 'Relay2', 'measure' => '', 'address' => '9', 'access' => 'rw'],
            'Relay3'  => ['title' => 'Relay3', 'measure' => '', 'address' => '10', 'access' => 'rw'],
            'Relay4'  => ['title' => 'Relay4', 'measure' => '', 'address' => '11', 'access' => 'rw'],
        ];
    }
    private function fetchUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->user:$this->pass");

        if (($response = curl_exec($ch)) === false) {
            $this->error = curl_error($ch);
        }
        curl_close($ch);

        return $response;
    }



    public function init($ip, $port = 80, $user = 'admin', $pass = 'admin')
    {
        $this->user = $user;
        $this->pass = $pass;
        $this->ip = $ip;
        $this->port = $port;

        $this->urlIOChange = 'http://'.$ip.'/iochange.cgi?ref=re-io';
        
        $urlConf = "http://" . $this->ip . ":" . $this->port . "/ioreg.js";
        if (false === ($data = $this->fetchUrl($urlConf))) {
            return false; // $this->error
        }   

        $SHT = $this->extractArray($data, 'SHT');
        $IO  = $this->extractArray($data, 'IO');
        $PM  = $this->extractArray($data, 'PM');
 
        // Преобразуваме към десетични числа
        $this->data['SHT'] = array_map(fn($h)=>hexdec($h), $SHT); 
        $this->data['IO']  = array_map(fn($h)=>hexdec($h), $IO);
        $this->data['PM']  = array_map(fn($h)=>hexdec($h), $PM);
    }

    public function readRegisters()
    {
        foreach ($this->registers as $name => $reg) {
            $addr = (int)$reg['address'];
            $pm_code = $this->data['PM'][$addr];
            $v_raw = $this->data['IO'][$addr];
            $SHT0 = $this->data['SHT'][0];
            $SHT1 = $this->data['SHT'][1];

            $value = $this->fcValue($pm_code, $v_raw, $SHT0, $SHT1);
            if ($name === 'Alarm') {
                // Специално за алармата
                $value = ($value > 511) ? "OPEN" : "CLOSED";
            }

             if (false !== strpos($name,'Relay')) {
                $this->data['sens'][$name] = $value ? "ON" : "OFF";
            } else {
                $this->data['sens'][$name] = $value;
            }
        }

        return $this->data['sens'];
    }

    public function writeRegisters($name, $value)
    {
        if (isset($this->registers[$name] ) && strpos($this->registers[$name]['access'], 'w') !== false ) {
            // Create a stream
            $opts = [
                'http'=>[
                    'method'=>"GET",
                    'header' => "Authorization: Basic " . base64_encode("{$this->user}:{$this->pass}") . "\r\n"               
                ]
            ];
            $context = stream_context_create($opts);
            $addr = $this->registers[$name]['address'];
            $cmd = sprintf("%02X=%02X",intval($addr),intval($value));
            
            // Open the file using the HTTP headers set above
            $file = file_get_contents($this->urlIOChange.'&'.$cmd, false, $context);

            return true;
        }
        return false;
    }

    // Функция за извличане на масиви от скрипта
    private function extractArray($script, $varName, $delimiter = ',') {
        $lines = explode("\n", $script);
    
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (strpos($line, "var $varName=") === 0) {
                $value = substr($line, strlen("var $varName=")); // Премахване на "var IO="
                $value = trim($value, " ;"); // Премахване на ";"
    
                // Проверяваме дали масивът е в кавички или в квадратни скоби
                if ($value[0] === '"') {
                    $value = trim($value, '"'); // Премахваме кавичките
                } elseif ($value[0] === '[') {
                    $value = trim($value, '[]'); // Премахваме квадратните скоби
                }
    
                return array_map('trim', explode($delimiter, $value)); // Разделяме по запетая
            }
        }
        return [];
    }

    // === Реализация на lib.js::fc(i,v) за нужните случаи ===
    // v_raw е 10-битов ADC (0..1023) от IO[]; първо става в милитиволти
    private function fcValue($pm_code, $v_raw, $SHT0, $SHT1) {
        // 10-bit ADC @ 3.3V
        $v = 3300.0 * ($v_raw & 0x3FF) / 1023.0; // mV

        switch ($pm_code) {
            case 33: // case 33: v=(v-500)/10; q=1  -> °C (TMP36-подобно)
                return round(($v - 500.0) / 10.0, 1);

            case 39: // SHT temp: v=-(SHT0/SHT1)*(v-1650)/22/1000; q=2
                $coef = $SHT0 / $SHT1;
                return round(-$coef * ($v - 1650.0) / (22.0 * 1000.0), 2);

            case 40: // Humidity: v=(125*v/3300)-6; q=0
                $rh = (125.0 * $v / 3300.0) - 6.0;
                if ($rh < 0) $rh = 0;
                if ($rh > 100) $rh = 100;
                return round($rh, 0);

            case 38: // Raw ADC count (0..1023)
                // fc прави v=v*1023/3300, което се връща към суров count
                $cnt = $v * 1023.0 / 3300.0;
                return (int)round($cnt);

            case 0:  // дигитални 0/1
            case 1:
            case 2:
            case 8:
                return ($v_raw ? 1 : 0);

            default:
                // по подразбиране върни mV закръглено
                return (int)round($v);
        }
    }

}
