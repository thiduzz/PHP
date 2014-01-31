<?php

class ConnectionAdapter {
    
    public function __construct() {

    }
    /**
     * 
     * @return blabla
     */
    public function connect()
    {
        $mysql_connected = false;
        $pruser = "";
        for ($i = 1; $i <= 5; $i++) {
            if ($con = mysqli_connect("192.168.1.4", "thiago.mello", "test")) {
                return $con;
            } else {
                if ($i == 5)
                    $pruser = "+++";
                file_put_contents(dirname(dirname(__DIR__)) . '/log/' . date("Y-m-d-H") . '.log', date("Y-m-d H:i:s") . " ->" . "(" . mysql_errno() . ")" . mysql_error(), FILE_APPEND);
            }
            usleep(10000); // 10ms
        }
        if (!$mysql_connected) {
            header('HTTP/1.1 503 Service Temporarily Unavailable'); // http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
            return null;
        }
    }
    
    public function connect_dibi(){
                try {
                dibi::connect('driver=mysql&host=192.168.1.4&username=thiago.mello&password=test&database=thiago&charset=cp1250');
                echo 'OK';

        } catch (DibiException $e) {
                echo get_class($e), ': ', $e->getMessage(), "\n";
        }
    }
}

?>