<?php
namespace DirectAdmin;

/**
 * The Context Class
 */
class Context {

    const User     = "user";
    const Reseller = "reseller";
    const Admin    = "admin";
    
    
    public $host;
    public $port;
    public $username;
    public $password;

    public $reseller;
    public $user;
    public $domain;
    
    
    /**
     * Creates a new Context instance
     * @param string  $host
     * @param integer $port
     * @param string  $username
     * @param string  $password
     */
    public function __construct(string $host, int $port, string $username, string $password) {
        $this->host     = $host;
        $this->port     = $port;
        $this->username = $username;
        $this->password = $password;
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
     * @param string $user
     * @param string $domain
     * @return void
     */
    public function setUser(string $user, string $domain): void {
        $this->user   = $user;
        $this->domain = $domain;
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
    public function getUsrpwd(string $context): string {
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
     * @param string $path
     * @return string
     */
    public function getPublicPath(string $path): string {
        $path = str_replace("/public_html/", "", $path);
        if (!empty($this->domain)) {
            return "/domains/{$this->domain}/public_html/$path";
        }
        return "/public_html/$path";
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
