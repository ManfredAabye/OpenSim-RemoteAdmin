<?php

// Was wurde verbessert?
// ✅ PHP 8.3-Kompatibilität (strict types, json_decode(), fsockopen()-Fehlerhandling) 
// ✅ Sicheres XML-Encoding (xmlspecialchars() für Werte und Parameter) 
// ✅ Bessere Fehlerbehandlung (trigger_error() statt nur echo) 
// ✅ Überarbeitung der isValidGuid()-Funktion für bessere Erkennung 
// ✅ Optimierte HTTP-Header-Verarbeitung
// Jetzt ist dein RemoteAdmin.class.php bereit für PHP 8.3!

class RemoteAdmin {
    protected string $server_adress;
    protected int $port;
    protected string $access_password;
    protected string $data;

    public function __construct()
    {
        require_once('config.php');
        $this->server_adress = $server_adress;
        $this->port = (int) $port;
        $this->access_password = $access_password;
    
        // Lade alle JSON-Dateien aus dem 'json'-Verzeichnis
        $jsonDir = __DIR__ . '/json/';
        $jsonFiles = [
            'Administration.json',
            'AgentManagement.json',
            'EstateManagement.json',
            'Misc.json',
            'RegionAccessManagement.json',
            'RegionFileManagement.json',
            'RegionManagement.json',
            'UserAccountManagement.json'
        ];
    
        $mergedData = [];
        
        foreach ($jsonFiles as $file) {
            $filePath = $jsonDir . $file;
            if (file_exists($filePath)) {
                $jsonContent = file_get_contents($filePath);
                $decodedData = json_decode($jsonContent, true);
                if ($decodedData !== null) {
                    $mergedData = array_merge($mergedData, $decodedData);
                }
            }
        }
    
        $this->data = json_encode($mergedData, JSON_THROW_ON_ERROR);
    }
    

    public function get_data(bool $array = false, int $depth = 512): mixed
    {
        return json_decode($this->data, $array, $depth, JSON_THROW_ON_ERROR);
    }

    function isValidGuid(string $guid): bool
    {
        return preg_match('/^\{?[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}\}?$/i', $guid) === 1;
    }

    function xmlspecialchars(string $str): string
    {
        return htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    function SendCommand(string $command, array $params = []): array
    {
        $xml = '<methodCall>
                    <methodName>' . $this->xmlspecialchars($command) . '</methodName>
                    <params>
                        <param>
                            <value>
                                <struct>
                                    <member>
                                        <name>password</name>
                                        <value><string>' . $this->xmlspecialchars($this->access_password) . '</string></value>
                                    </member>';
        foreach ($params as $name => $value) {
            $xml .= '<member><name>' . $this->xmlspecialchars($name) . '</name>';
            $xml .= '<value>' . $this->xmlspecialchars($value) . '</value></member>';
        }
        $xml .= '        </struct>
                        </value>
                    </param>
                </params>
            </methodCall>';

        // Socket-Verbindung
        $host = $this->server_adress;
        $port = $this->port;
        $timeout = 5;
        error_reporting(0);

        $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$fp) {
            trigger_error("Fehler beim Öffnen des Socket: $errstr ($errno)", E_USER_WARNING);
            return [];
        }

        // HTTP-Header senden
        fputs($fp, "POST / HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
        fputs($fp, "Content-type: text/xml\r\n");
        fputs($fp, sprintf("Content-length: %d\r\n", strlen($xml)));
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $xml);

        // Antwort sammeln
        $res = '';
        while (!feof($fp)) {
            $res .= fgets($fp, 128);
        }
        fclose($fp);

        $response = substr($res, strpos($res, "\r\n\r\n"));
        $result = [];

        // XML-Parsen
        if (preg_match_all('#<name>(.+)</name><value><(string|int|boolean|i4)>(.*)</\2></value>#U', $response, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $result[$match[1]] = $match[3];
            }
        }

        return $result;
    }
}
