<?php

namespace IDCT\Networking\Soap;

class Client extends \SoapClient {

    protected $auth;
    protected $authLogin;
    protected $authPassword;
    
    protected $customHeaders;

    protected $ignoreCertVerify;

    protected $negotiationTimeout;
    protected $persistanceFactor;
    protected $persistanceTimeout;
    
    public function __construct($wsdl, $options, $negotiationTimeout = 0, $persistanceFactor = 1, $persistanceTimeout = 0) {	
        $this->setNegotiationTimeout($negotiationTimeout)
             ->setPersistanceFactor($persistanceFactor)
             ->setPersistanceTimeout($persistanceTimeout)
             ->setIgnoreCertVerify(false)
             ;
        if(array_key_exists("login",$options)) {
            $this->auth = true;
            $this->authLogin = $options['login'];
            if(array_key_exists("password",$options)) {
                $this->authPassword = $options['password'];
            } else {
                $this->authPassword = null;
            }
        }
        $this->customHeaders = array();
        parent::__construct($wsdl,$options);
    }
    
    public function setNegotiationTimeout($timeoutInSeconds) {
        if($timeoutInSeconds < 0) {
            throw new \Exception('Negotiation timeout must be a positive integer or 0 to disable.');
        } else {
            $this->negotiationTimeout = $timeoutInSeconds;
        }
        
        return $this;
    }
    
    public function getNegotiationTimeout() {
        return $this->negotiationTimeout;
    }
    
    public function setPersistanceFactor($attempts) {
        if($attempts < 1) {
            throw new \Exception('Number of attempts must be at least equal to 1.');
        } else {
            $this->persistanceFactor = $attempts;
        }
        
        return $this;
    }
    
    public function getPersistanceFactor() {
        return $this->persistanceFactor;
    }
    
    public function setPersistanceTimeout($timeoutInSeconds) {
        if($timeoutInSeconds < 0) {
            throw new \Exception('Persistance timeout must be a positive integer or 0 to disable.');
        } else {
            $this->persistanceFactor = $timeoutInSeconds;
        }
        
        return $this;
    }
    
    public function getPersistanceTimeout() {
        return $this->persistanceTimeout;
    }
    
    public function setHeaders($headers) {
        if(is_array($headers))
        {
            $this->customHeaders = $headers;
        } else {
            throw new \Exception('Not an array.');
        }
    }
    
    public function getHeaders() {
        return $this->customHeaders;
    }
    
    public function setHeader($header, $value) {
        if(strlen($header) < 1) {
            throw new \Exception('Header must be a string.');
        }
        $this->customHeaders[$header] = $value;
        return $this;
    }
    
    public function getHeader($header) {
        return $this->customHeaders[$header];
    }
    
    public function setIgnoreCertVerify($value) {
        $this->ignoreCertVerify = $value;
        return $this;
    }
    
    public function getIgnoreCertVerify() {
        return $this->ignoreCertVerify;
    }
    
    
    
    public function __doRequest ($request, $location, $action, $version, $one_way = null) {
        $response = "";
        for($attempt = 0; $attempt < $this->persistanceFactor; $attempt++) {
            $ch = curl_init($location);
            curl_setopt($ch, CURLOPT_HEADER, false);
            if($one_way !== true) {
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->negotiationTimeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->persistanceTimeout);
            $defaultHeaders = array("Content-Type"=>"type/application-xml");
            $headers = array_merge($defaultHeaders,$this->customHeaders);
            $headersFormatted = array();
            foreach($headers as $header => $value) 
            {
                $headersFormatted[] = $header . ": " . $value;
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headersFormatted);
            if($this->getIgnoreCertVerify() === true) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            }
            
            if($this->auth === true) {
                $credentials = $this->authLogin;
                $credentials .= ($this->authPassword !== null) ? ":" . $this->authPassword : "";                    
                curl_setopt($ch, CURLOPT_USERPWD, $credentials);
            }
            
            $response = curl_exec($ch);
            if(curl_errno($ch) !== 0 && $attempt >= $this->persistanceFactor -1) {
                throw new \Exception('Request failed for the maximum number of attempts.');
            } else {
                break;
            }
        }
        return $response;
    }

}

