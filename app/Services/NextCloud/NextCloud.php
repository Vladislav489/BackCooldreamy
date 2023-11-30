<?php


namespace App\Services\NextCloud;


class NextCloud {
    private $userName = null;
    private $password = null;
    private $url = null;
    private $method = null;
    private $requestUrl = null;
    private $response = null;
    private $error = null;
    private $typeFile = null;

    private $listAction = ['folder'=>'/remote.php/dav/files/'];
    // $URL = 'https://nc.cooldreamy.com/remote.php/dav/files/dmitry/media';

    //$username = 'dmitry';
    //$password = 'Aa@19528091!';
    public function __construct($user = null,$pass = null,$url = null){
        if(is_null($user) || is_null($pass) || is_null($url)){
            $user = env('NEXT_CLOUD_USER');
            $pass = env('NEXT_CLOUD_PASS');
            $url =  env('NEXT_CLOUD_URL');
        }
        $this->setConfig($user,$pass,$url);
    }

    public function setConfig($user,$pass,$url){
        $this->userName = $user;
        $this->password = $pass;
        $this->url = $url;
        return $this;
    }

    private function buildUrl($action){
       return  "https://".$this->url.$this->listAction[$action].$this->userName;
    }



    private function requestCloud($content = null){
        $curl = curl_init();
        $header = array(
            ': ',
            'Authorization: Basic '.base64_encode($this->userName.":".$this->password),
        );
        if(!is_null($content)){
            $type = null;
            switch ($this->typeFile){
                case "png":
                    $type =  'image/png';
                    break;
                case "jpg":
                case "jpeg":
                    $type =   'image/jpeg';
                    break;
                case "webp":
                     $type =  'image/webp';
                     break;
            }
            array_unshift($header,"Content-Type: {$type}");
        }
        logger($this->requestUrl);
        $paramsCurl = array(
            CURLOPT_URL =>$this->requestUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_TIMEOUT => 0,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_HTTPHEADER => $header
        );
         if(!is_null($content))
         $paramsCurl[CURLOPT_POSTFIELDS] = $content;

        curl_setopt_array($curl,$paramsCurl);
        $response = curl_exec($curl);
        logger(json_encode($response));
        if (curl_errno($curl)) {
            $this->error = curl_error($curl);
            logger(json_encode($this->error));
        }
        curl_close($curl);
        return $response;
    }



    public function getFolder($pathFolder = null){
        $this->method = 'PROPFIND';
        $this->requestUrl = (!is_null($pathFolder))? $this->buildUrl('folder').$pathFolder:$this->buildUrl('folder');
        $this->response = $this->requestCloud();
        return $this;
    }

    public function getFile($path,$fileName = null){
        $this->method = 'GET';
        $this->requestUrl = (!is_null($fileName))? $this->buildUrl('folder').$path."/".$fileName:$this->buildUrl('folder').$path;
        $this->response = $this->requestCloud();
        return $this;
    }
    public function upoadFile($path,$content,$fileName,$type){
        $this->method = 'PUT';
        $this->requestUrl =  $this->buildUrl('folder').$path."/".$fileName;
        $this->typeFile = $type;
        $this->response = $this->requestCloud($content);
    }
    public function moveFolder($fromPath,$toPath){
        $this->method = 'MOVE';
    }
    public function deleteFile($pathFileName){
        $this->method = 'DELETE';
        $this->requestUrl = $this->buildUrl('folder').$pathFileName;
        $this->response = $this->requestCloud();
        return $this;
    }

    public function createFolder($pathNewFolder){
        $this->method = 'MKCOL';
        $this->requestUrl = $this->buildUrl('folder').$pathNewFolder;
        $this->response = $this->requestCloud();
        return $this;
    }
    public function deleteFolder($pathFolder){
        $this->method = 'DELETE';
        $this->requestUrl = $this->buildUrl('folder').$pathFolder;
        $this->response = $this->requestCloud();
        return $this;
    }

    public function getResponse(){
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($this->response, NULL, NULL, "DAV:");
        if($xml) {
            $json = json_encode($xml);
            $arr = json_decode($json, TRUE)['response'];
            array_shift($arr);
            if(isset($arr[0])) {
                if (count($arr)) {
                    foreach ($arr as $key => $item) {
                        $search = $this->listAction['folder'] . $this->userName;
                        $arr[$key] = str_replace($search, '', $item['href']);
                    }
                    return $arr;
                }
            }
            return null;
        } else {
            return $this->response;
        }
    }

}
