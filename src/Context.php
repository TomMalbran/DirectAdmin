<?php
namespace DirectAdmin;

/**
 * The Context Class
 */
class Context {

    const Admin    = "admin";
    const Reseller = "reseller";
    const User     = "user";


    public $host;
    public $port;
    public $username;
    public $password;
    public $ip;

    public $reseller;
    public $user;
    public $domain;
    public $isDelegated;


    /**
     * Creates a new Context instance
     * @param string  $host
     * @param integer $port
     * @param string  $username
     * @param string  $password
     * @param string  $ip       Optional.
     */
    public function __construct(string $host, int $port, string $username, string $password, string $ip = "") {
        $this->host     = $host;
        $this->port     = $port;
        $this->username = $username;
        $this->password = $password;
        $this->ip       = $ip;
    }

    /**
     * Sets the Reseller for the Context
     * @param string $reseller
     * @return void
     */
    public function setReseller(string $reseller): void {
        $this->reseller = $reseller;
    }

    /**
     * Sets the User and Domain for the Context
     * @param string  $user
     * @param string  $domain
     * @param boolean $isDelegated Optional.
     * @return void
     */
    public function setUser(string $user, string $domain, bool $isDelegated = false): void {
        $this->user        = $user;
        $this->domain      = $domain;
        $this->isDelegated = $isDelegated;
    }



    /**
     * Returns the full Url
     * @param string $endPoint
     * @return string
     */
    public function getUrl(string $endPoint): string {
        return "https://{$this->host}:{$this->port}{$endPoint}";
    }

    /**
     * Returns the full FTP url
     * @param string $path
     * @param string $file
     * @return string
     */
    public function getFtp(string $path, string $file): string {
        return "ftp://{$this->host}{$path}/{$file}";
    }

    /**
     * Returns the User and Password depending on the Context
     * @param string $context
     * @return string
     */
    public function getUserpwd(string $context): string {
        $user = $this->username;
        if ($context == self::User && !empty($this->user)) {
            $user = "{$this->username}|{$this->user}";
        } elseif ($context == self::Reseller && !empty($this->reseller)) {
            $user = "{$this->username}|{$this->reseller}";
        }
        return "$user:{$this->password}";
    }

    /**
     * Returns the Public Path
     * @param string $path Optional.
     * @return string
     */
    public function getPublicPath(string $path = ""): string {
        $result = "/public_html";
        if (!empty($this->domain)) {
            $result = "/domains/{$this->domain}/public_html";
        }
        if (!empty($path)) {
            $path    = str_replace("/public_html/", "", $path);
            $result .= "/$path";
        }
        return $result;
    }

    /**
     * Adds fields to the Params depending on the Context
     * @param string $context
     * @param array  $params
     * @return array
     */
    public function addParams(string $context, array $params): array {
        if ($context == self::User) {
            $params["domain"] = $this->domain;
        }
        return $params;
    }
}
