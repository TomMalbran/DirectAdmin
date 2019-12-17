<?php
namespace DirectAdmin;

use DirectAdmin\DirectAdmin;

/**
 * The Adapter Class
 */
class Adapter {
    
    private $host;
    private $port;

    private $user;
    private $username;
    private $password;
    private $subuser;
    private $domain;

    private $socket;
    
    
    /**
     * Creates a new Adapter instance
     * @param string  $host
     * @param integer $port
     * @param string  $username
     * @param string  $password
     * @param string  $subuser  Optional.
     * @param string  $domain   Optional.
     */
    public function __construct(string $host, int $port, string $username, string $password, string $subuser = "", string $domain = "") {
        $this->host     = $host;
        $this->port     = $port;

        $this->user     = !empty($subuser) ? "$username|$subuser" : $username;
        $this->username = $username;
        $this->password = $password;
        $this->subuser  = $subuser;
        $this->domain   = $domain;
        
        $this->socket   = new DirectAdmin();
        $this->socket->connect("https://{$this->host}", $this->port);
        $this->socket->set_login($this->user, $this->password);
    }

    /**
     * Sets the Subuser for the Adapter
     * @param string $subuser
     * @return void
     */
    public function setSubuser(string $subuser):void {
        $this->user = "{$this->username}|$subuser";
        $this->socket->set_login($this->user, $this->password);
    }

    /**
     * Sets the Domain for the Adapter
     * @param string $domain
     * @return void
     */
    public function setDomain(string $domain): void {
        $this->domain = $domain;
    }
    
    

    /**
     * Does a query over the server
     * @param string  $request
     * @param array   $params   Optional.
     * @param string  $method   Optional.
     * @param boolean $parse    Optional.
     * @param boolean $withDots Optional.
     * @return array
     */
    public function query(string $request, array $params = [], string $method = "GET", bool $parse = true, bool $withDots = false): array {
        $this->socket->set_method($method);
        $this->socket->query($request, $params);
        $result = $this->socket->fetch_body();
        
        if (!empty($result) && strpos($result, "<title>DirectAdmin Login</title>") !== false) {
            return new Response([
                "error"     => 1,
                "error_msg" => "WRONG USERNAME OR PASSWORD",
            ]);
        }
        
        $result = $this->socket->fetch_body();
        if ($parse) {
            if ($withDots) {
                $result = str_replace("%2E", "___", $result);
            }
            parse_str($result, $x);
            return new Response($x);
        }
        return new Response($result);
    }
    
    /**
     * Uploads a File using FTP
     * @param string $path
     * @param string $fileName
     * @param string $filePath
     * @param string $user
     * @param string $password
     * @return string
     */
    public function postFileFTP(string $path, string $fileName, string $filePath, string $user, string $password): string {
        $url     = "ftp://{$this->host}{$path}/{$fileName}";
        $userpwd = "{$user}:{$password}";
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL        => $url,
            CURLOPT_USERPWD    => $userpwd,
            CURLOPT_HTTPAUTH   => CURLAUTH_BASIC,
            CURLOPT_UPLOAD     => 1,
            CURLOPT_INFILE     => fopen($filePath, "r"),
            CURLOPT_INFILESIZE => filesize($filePath),
        ]);
        
        $result = curl_exec($curl);
        $error  = curl_error($curl);
        $errno  = curl_errno($curl);
        curl_close($curl);
        
        // print("$url $userpwd ($errno) $error");
        if (!empty($errno)) {
            return "$errno $error";
        }
        return "";
    }

    /**
     * Parses the request and returns an error or an array with the content
     * @param array $request
     * @return array
     */
    public function getListResult(array $request): array {
        if (!empty($request) && !empty($request["error"])) {
            return [ "error" => true ];
        }
        if (!empty($request["list"])) {
            return $request["list"];
        }
        return [];
    }
    
    

    /**
     * Returns the Server IP
     * @return string
     */
    public function getHost(): string {
        return $this->host;
    }
    
    /**
     * Returns the Server Port
     * @return integer
     */
    public function getPort(): int {
        return $this->port;
    }
    
    /**
     * Returns the Current Domain
     * @return string
     */
    public function getDomain(): string {
        return $this->port;
    }

    /**
     * Returns the Public path
     * @param string $path
     * @return string
     */
    public function getPublicPath(string $path): string {
        if (!empty($this->domain)) {
            return "/domains/{$this->domain}/public_html/$path";
        }
        return "/public_html/$path";
    }
}
