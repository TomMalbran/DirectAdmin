<?php
namespace DirectAdmin\Reseller;

use DirectAdmin\Adapter;

/**
 * The Reseller Accounts
 */
class Reseller {
    
    private $adapter;
    
    /**
     * Creates a new Reseller instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns the list of Resellers for the current server
     * @return array
     */
    public function getAll() {
        return $this->adapter->query("/CMD_API_SHOW_RESELLERS");
    }

    /**
     * Returns the users limits and usage
     * @param string $username
     * @return array
     */
    public function getInfo($username) {
        $fields = [ "bandwidth", "quota", "domainptr", "mysql", "nemailf", "nemailr", "nemails", "nsubdomains", "vdomains" ];
        $config = $this->adapter->query("/CMD_API_RESELLER_STATS", [ "user" => $username ]);
        
        if (empty($config["error"])) {
            $usage     = $this->adapter->query("/CMD_API_RESELLER_STATS", [ "user" => $username, "type" => "usage" ]);
            $allocated = $this->adapter->query("/CMD_API_RESELLER_STATS", [ "user" => $username, "type" => "allocated" ]);
        }
        $result = [];
        
        foreach ($fields as $field) {
            if (isset($config[$field])) {
                $result[$field] = [
                    "used"      => isset($usage[$field])     ? (int)$usage[$field]     : 0,
                    "allocated" => isset($allocated[$field]) ? (int)$allocated[$field] : 0,
                    "total"     => $config[$field] == "unlimited" ? -1 : (int)$config[$field],
                ];
            }
        }
        
        return $result;
    }
    
    

    /**
     * Creates a new Reseller
     * @param array $data
     * @return array|null
     */
    public function create(array $data) {
        return $this->adapter->query("/CMD_API_ACCOUNT_RESELLER", [
            "action"   => "create",
            "add"      => "Submit",
            "username" => $data["username"],
            "email"    => $data["email"],
            "passwd"   => $data["password"],
            "passwd2"  => $data["password"],
            "domain"   => $data["domain"],
            "package"  => $data["package"],
            "ip"       => "shared",
            "notify"   => "no",
        ]);
    }
    
    /**
     * Creates a new Reseller account with the given username and password. Requires Admin login
     * @param string $username
     * @param string $password
     * @param string $domain   Optional.
     * @param string $email    Optional.
     * @return array|null
     */
    public function createUnlimited($username, $password, $domain = "", $email = "") {
        return $this->adapter->query("/CMD_API_ACCOUNT_RESELLER", [
            "action"       => "create",
            "add"          => "Submit",
            "username"     => $username,
            "email"        => !empty($email)  ? $email  : (!empty($domain) ? "info@$domain" : "info@$username.com"),
            "passwd"       => $password,
            "passwd2"      => $password,
            "domain"       => !empty($domain) ? $domain : "$username.com",
            "ubandwidth"   => "ON",
            "uquota"       => "ON",
            "uinode"       => "ON",
            "uvdomains"    => "ON",
            "unsubdomains" => "ON",
            "ips"          => 0,
            "unemails"     => "ON",
            "unemailf"     => "ON",
            "unemailml"    => "OFF",
            "unemailr"     => "ON",
            "umysql"       => "ON",
            "udomainptr"   => "ON",
            "uftp"         => "ON",
            "aftp"         => "OFF",
            "cgi"          => "ON",
            "php"          => "ON",
            "spam"         => "ON",
            "catchall"     => "ON",
            "cron"         => "ON",
            "ssl"          => "ON",
            "ssh"          => "OFF",
            "userssh"      => "OFF",
            "dnscontrol"   => "ON",
            "dns"          => "OFF",
            "serverip"     => "ON",
            "ip"           => "shared",
            "notify"       => "no",
        ]);
    }
    
    /**
     * Changes the Resellers's Package
     * @param string $username
     * @param string $package
     * @return array|null
     */
    public function changePackage($username, $package) {
        return $this->adapter->query("/CMD_API_MODIFY_RESELLER", [
            "action"  => "package",
            "user"    => $username,
            "package" => $package,
        ], "POST");
    }
}
