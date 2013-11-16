<?

class UNIFI_CONTROL    {

    private $unifihost        =    "";
    private $unifiuser        =    "";
    private $unifipass        =    "";
    private $unifiurl        =    "";
    private $maxusertime    =    30;

    /*    Função construtora da classe    */
    function __construct($unifiurl,$unifiuser,$unifipass,$maxusertime=30)    {
        $this->unifiuser    =    $unifiuser;
        $this->unifipass    =    $unifipass;
        $this->unifiurl     =    $unifiurl;
        $this->maxusertime    =    $maxusertime;
    }
    static function WebCall($url, $param)    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, TRUE);
        $cookie_file = "/tmp/unifi_cookie";
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
          // Login to the UniFi controller
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_POSTFIELDS,$param);
          $output = curl_exec ($ch);
          curl_close ($ch);
        //error_log("WebCall ($url,$param): $output)");
        return json_decode($output);
    }
    /*    Ubiquity Functions    */
    function Login()    {
        return UNIFI_CONTROL::WebCall($this->unifiurl."/login", "login=login&username=".urlencode($this->unifiuser)."&password=".urlencode($this->unifipass));
    }    
    function Logout()    {    
        return UNIFI_CONTROL::WebCall($this->unifiurl."/logout", "");
    }
    function MacFunction($mac, $cmd, $mgr="stamgr")    {
          $data    =    json_encode(    array(    
                                        'cmd'        =>    $cmd    ,    
                                        'mac'        =>    $mac                
                                    )
                                );
        return UNIFI_CONTROL::WebCall($this->unifiurl.'/api/cmd/'.$mgr, 'json='.urlencode($data));    
    }
    function AuthorizeClient($mac)    {
          $data    =    json_encode(    array(    
                                        'cmd'        =>    'authorize-guest'    ,    
                                        'mac'        =>    $mac                ,
                                        'minutes'    =>    $this->maxusertime
                                    )
                                );
        return UNIFI_CONTROL::WebCall($this->unifiurl.'/api/cmd/stamgr', 'json='.urlencode($data));    
    }
    function BlockClient($mac)    {
        return $this->MacFunction($mac, "block-sta");
    }
    function UnBlockClient($mac)    {
        return $this->MacFunction($mac, "unblock-sta");
    }
    function DisconnectClient($mac)    {
        return $this->MacFunction($mac, "kick-sta");
    }
    function RestartAP($mac)    {
        return $this->MacFunction($mac, "restart", "devmgr");
    }
    function GetAccessPoints()    {
          $data    =    json_encode(    array(    
                                        'depth'        =>    2    ,    
                                        'test'        =>    null
                                    )
                                );
        return UNIFI_CONTROL::WebCall($this->unifiurl.'/api/stat/device', 'json='.urlencode($data));    
    }
    function GetClients()    {
        return UNIFI_CONTROL::WebCall($this->unifiurl.'/api/stat/sta', "");
    }
    
}
