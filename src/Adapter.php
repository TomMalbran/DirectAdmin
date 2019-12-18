<?php
namespace DirectAdmin;

/**
 * The Adapter Class
 */
class Adapter {
    
    private $host;
    private $port;

    private $username;
    private $password;

    private $user;
    private $domain;
    
    
    /**
     * Creates a new Adapter instance
     * @param string  $host
     * @param integer $port
     * @param string  $username
     * @param string  $password
     * @param string  $user     Optional.
     * @param string  $domain   Optional.
     */
    public function __construct(string $host, int $port, string $username, string $password, string $user = "", string $domain = "") {
        $this->host     = $host;
        $this->port     = $port;

        $this->username = $username;
        $this->password = $password;
        $this->user     = $user;
        $this->domain   = $domain;
    }
    
    /**
     * Sets the Subuser and Domain for the Adapter
     * @param string $user
     * @param string $domain
     * @return void
     */
    public function setUser(string $user, string $domain): void {
        $this->user   = $user;
        $this->domain = $domain;
    }
    
    

    /**
     * Does a get query to the server
     * @param string $endPoint
     * @param array  $params   Optional.
     * @return Response
     */
    public function get(string $endPoint, array $params = []): Response {
        return $this->query($endPoint, $params, "GET");
    }

    /**
     * Does a post query to the server
     * @param string $endPoint
     * @param array  $params
     * @return Response
     */
    public function post(string $endPoint, array $params): Response {
        return $this->query($endPoint, $params, "POST");
    }

    /**
     * Does a query to the server
     * @param string  $endPoint
     * @param array   $params
     * @param string  $method
     * @param boolean $withDots Optional.
     * @return Response
     */
    private function query(string $endPoint, array $params, string $method, bool $withDots = false): Response {
        $user    = !empty($this->user) ? "{$this->username}|{$this->user}" : $this->username;
        $request = "https://{$this->host}:{$this->port}{$endPoint}";

        if ($method == "GET") {
            $request .= "?" . http_build_query($params);
        }
        $options = [
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_URL             => $request,
			CURLOPT_USERPWD         => "$user:{$this->password}",
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => false,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_FORBID_REUSE    => true,
            CURLOPT_TIMEOUT         => 100,
            CURLOPT_CONNECTTIMEOUT  => 10,
            CURLOPT_LOW_SPEED_LIMIT => 512,
            CURLOPT_LOW_SPEED_TIME  => 120,
        ];
        if ($method == "POST") {
            $options += [
                CURLOPT_POST       => 1,
                CURLOPT_POSTFIELDS => $params,
            ];
        }

        // Execute the query
        [ $result, $error, $errno ] = $this->execute($options);
        if (!empty($error)) {
            return Response::error("CURL ERROR: $error");
        }

        if (!empty($result) && strpos($result, "<title>DirectAdmin Login</title>") !== false) {
            return Response::error("WRONG USERNAME OR PASSWORD");
        }
        
        // Decode the response and parse it to an array.
        if ($withDots) {
            $result = str_replace("%2E", "___", $result);
        }
        return Response::parse($result);
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
    public function uploadFile(string $path, string $fileName, string $filePath, string $user, string $password): string {
        $url = "ftp://{$this->host}{$path}/{$fileName}";

        [ $result, $error, $errno ] = $this->execute([
            CURLOPT_URL        => $url,
            CURLOPT_USERPWD    => "{$user}:{$password}",
            CURLOPT_HTTPAUTH   => CURLAUTH_BASIC,
            CURLOPT_UPLOAD     => 1,
            CURLOPT_INFILE     => fopen($filePath, "r"),
            CURLOPT_INFILESIZE => filesize($filePath),
        ]);
        
        // print("$url $userpwd ($errno) $error");
        if (!empty($errno)) {
            return "$errno $error";
        }
        return "";
    }

    /**
     * Execute a Curl request
     * @param array $options
     * @return array
     */
    private function execute(array $options): array {
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        $error  = curl_error($curl);
        $errno  = curl_errno($curl);
        curl_close($curl);
        return [ $result, $error, $errno ];
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
        return $this->domain;
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
