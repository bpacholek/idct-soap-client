<?php

namespace IDCT\Networking\Soap;

class Client extends \SoapClient {

    /**
     * Defines if request will be sent with a basic http auth
     *
     * @var boolean
     */
    protected $auth;

    /**
     * If auth set to true this will be sent as login for the basic http auth
     *
     * @var string
     */
    protected $authLogin;

    /**
     * If auth set to true this will be sent as password for the basic http auth
     *
     * @var string
     */
    protected $authPassword;

    /**
     * Associative array of custom headers to sent together with the request
     *
     * @var array
     */
    protected $customHeaders;

    /**
     * If set to false then request will not fail in case of invalid SSL cert
     *
     * @var boolean
     */
    protected $ignoreCertVerify;

    /**
     * Connection negotiation timeout in seconds
     *
     * @var int
     */
    protected $negotiationTimeout;

    /**
     * Number of retries until exception is thrown
     *
     * @var int
     */
    protected $persistanceFactor;

    /**
     * Read timeout (after a successful connection) in seconds)
     *
     * @var int
     */
    protected $persistanceTimeout;

    /**
     * Constructor of the new object. Creates an instance of the new SoapClient.
     * Sets default values of the timeouts and number of retries.
     *
     * @param sting $wsdl Url of the WebService's wsdl
     * @param array $options PHP SoapClient's array of options
     * @param int $negotiationTimeout Connection timeout in seconds. 0 to disable.
     * @param int $persistanceFactor Number of retries.
     * @param int $persistanceTimeout Read timeout in seconds. 0 to disable. null to use ini default_socket_timeout
     */
    public function __construct($wsdl, $options, $negotiationTimeout = 0, $persistanceFactor = 1, $persistanceTimeout = null) {

        if($persistanceTimeout === null)
        {
            //let us try default to default_socket_timeout
            $iniDefaultSocketTimeout = ini_get('default_socket_timeout');
            $persistanceTimeout = $iniDefaultSocketTimeout ? $iniDefaultSocketTimeout : 0; //if setting missing default to disabled value (0)
        }

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

    /**
     * Sets the negotiation (connection) timeout in seconds.
     * Throws an exception in case a negative value.
     * Set 0 to disable the timeout.
     *
     * @param int $timeoutInSeconds
     */
    public function setNegotiationTimeout($timeoutInSeconds) {
        if($timeoutInSeconds < 0) {
            throw new \Exception('Negotiation timeout must be a positive integer or 0 to disable.');
        } else {
            $this->negotiationTimeout = $timeoutInSeconds;
        }

        return $this;
    }

    /**
     * Gets the negotiation (connection) timeout in seconds
     *
     * @return int
     */
    public function getNegotiationTimeout() {
        return $this->negotiationTimeout;
    }

    /**
     * Sets the maximum number of full data read (connection+read) retries.
     * Value must be at least equal to one.
     *
     * @param int $attempts
     */
    public function setPersistanceFactor($attempts) {
        if($attempts < 1) {
            throw new \Exception('Number of attempts must be at least equal to 1.');
        } else {
            $this->persistanceFactor = $attempts;
        }

        return $this;
    }

    /**
     * Gets the maximum number of full data read (connection+read) retries.
     *
     * @return int
     */
    public function getPersistanceFactor() {
        return $this->persistanceFactor;
    }

    /**
     * Sets the data read (after a successful negotiation) timeout in seconds.
     * Throws an exception when value is negative.
     * Set 0 to disable timeout. null to use ini default_socket_timeout
     *
     * @param int $timeoutInSeconds
     */
    public function setPersistanceTimeout($timeoutInSeconds = null) {
        if($timeoutInSeconds === null)
        {
            //let us try default to default_socket_timeout
            $iniDefaultSocketTimeout = ini_get('default_socket_timeout');
            $this->persistanceTimeout = $iniDefaultSocketTimeout ? $iniDefaultSocketTimeout : 0; //if setting missing default to disabled value (0)
        } else {
            if($timeoutInSeconds < 0) {
                throw new \Exception('Persistance timeout must be a positive integer, 0 to disable or null to use ini default_socket_timeout value.');
            } else {
                $this->persistanceTimeout = $timeoutInSeconds;
            }
        }

        return $this;
    }

    /**
     * Gets the data read (after negotiation) timeout in seconds.
     * @return int
     */
    public function getPersistanceTimeout() {
        return $this->persistanceTimeout;
    }

    /**
     * Sets an array of custom http headers to be sent together with the request.
     * Throws an exception if not an array.
     *
     * @param array $headers
     */
    public function setHeaders($headers) {
        if(is_array($headers))
        {
            $this->customHeaders = $headers;
        } else {
            throw new \Exception('Not an array.');
        }
    }

    /**
     * Gets the array of custom headers to be sent together with the request.
     *
     * @return array
     */
    public function getHeaders() {
        return $this->customHeaders;
    }

    /**
     * Sets a custom header to be sent together with the request.
     * Throws an exception if header's name is not at least 1 char long.
     *
     * @param string $header
     * @param string $value
     */
    public function setHeader($header, $value) {
        if(strlen($header) < 1) {
            throw new \Exception('Header must be a string.');
        }
        $this->customHeaders[$header] = $value;
        return $this;
    }

    /**
     * Gets a custom header from the array of headers to be sent with the request or null.
     *
     * @param string $header
     * @return string
     */
    public function getHeader($header) {
        return $this->customHeaders[$header];
    }

    /**
     * Sets a boolean value of the flag which indicates if request should not worry about invalid SSL certificate.
     *
     * @param boolean $value
     */
    public function setIgnoreCertVerify($value) {
        $this->ignoreCertVerify = $value;
        return $this;
    }

    /**
     * Gets the value of the flag which indicates if request should not worry about invalid SSL certificate.
     * @return boolean
     */
    public function getIgnoreCertVerify() {
        return $this->ignoreCertVerify;
    }


    /**
     * Performs the request using cUrl, should not be called directly, but through
     * normal usage of PHP SoapClient (using particular methods of the WebService).
     * Throws an exception if connection or data read fails more than the number of retries (persistanceFactor).
     * Returns data response / content.
     *
     * @param string $request Request (XML/Data) to be sent to the WebService parsed by SoapClient.
     * @param string $location WebService URL.
     * @param string $action Currently not used. In the signature for compatibility with SoapClient. TODO: to be used with particular soap versions.
     * @param int $version Currently not used. In the signature for compatibility with SoapClient. TODO: add Soap Version selection.
     * @param bool $one_way Currently not used. In the signature for compatibility with SoapClient.
     * @return mixed
     */
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
            $defaultHeaders = array("Content-Type"=>"text/xml");
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
            $errno = curl_errno($ch);
            curl_close($ch);
            if($errno !== 0 && $attempt >= $this->persistanceFactor -1) {
                throw new \Exception('Request failed for the maximum number of attempts.');
            } else {
                break;
            }
        }
        return $response;
    }

}

