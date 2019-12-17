<?php
namespace DirectAdmin\User;

use DirectAdmin\Adapter;

/**
 * The User Accounts
 */
class User {
    
    private $adapter;
    
    /**
     * Creates a new User instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    

    /**
     * Returns the list of users for the current Reseller
     * @return array
     */
    public function getAll() {
        return $this->adapter->query("/CMD_API_SHOW_USERS");
    }

    /**
     * Returns the users limits and usage
     * @param string $username
     * @param string $domain
     * @return array
     */
    public function getInfo($username, $domain) {
        $fields = [ "bandwidth", "quota", "domainptr", "mysql", "nemailf", "nemailr", "nemails", "nsubdomains", "ftp" ];
        $usage  = $this->adapter->query("/CMD_API_SHOW_USER_USAGE",  [ "user" => $username ]);
        
        if (!empty($usage["error"])) {
            $usage  = $this->adapter->query("/CMD_API_SHOW_USER_USAGE",  [ "domain" => $domain ]);
            $config = $this->adapter->query("/CMD_API_SHOW_USER_CONFIG", [ "domain" => $domain ]);
        } else {
            $config = $this->adapter->query("/CMD_API_SHOW_USER_CONFIG", [ "user" => $username ]);
        }
        $result = [];
        
        if (!empty($usage) && !empty($config) && empty($usage["error"]) && empty($config["error"])) {
            foreach ($fields as $field) {
                if (isset($usage[$field]) && isset($config[$field])) {
                    $result[$field] = [
                        "used"   => (int)$usage[$field],
                        "total"  => $config[$field] == "unlimited" ? -1 : (int)$config[$field],
                        "canAdd" => $config[$field] == "unlimited" || (int)$usage[$field] < (int)$config[$field],
                    ];
                }
            }
            
            if (!empty($result)) {
                $result["dbQuota"]    = [ "used" => isset($usage["db_quota"])    ? (int)$usage["db_quota"]    : 0 ];
                $result["emailQuota"] = [ "used" => isset($usage["email_quota"]) ? (int)$usage["email_quota"] : 0 ];

                $result["dbQuota"]["used"]    = floor(($result["dbQuota"]["used"]    / (1024 * 1024)) * 100) / 100;
                $result["emailQuota"]["used"] = floor(($result["emailQuota"]["used"] / (1024 * 1024)) * 100) / 100;

                $result["bandwidth"]["additional"] = !empty($config["additional_bandwidth"]) ? (int)$config["additional_bandwidth"] : 0;
            }
        }
        
        return $result;
    }
    
    /**
     * Returns the users configuration
     * @param string $username
     * @return array
     */
    public function getConfig($username) {
        return $this->adapter->query("/CMD_API_SHOW_USER_CONFIG", [ "user" => $username ]);
    }
    
    /**
     * Returns the main domain for the given user
     * @param string $username
     * @return string
     */
    public function getMainDomain($username) {
        $result = $this->adapter->query("/CMD_API_SHOW_USER_DOMAINS", [ "user" => $username ]);
        foreach (array_keys($result) as $key) {
            return str_replace("_", ".", $key);
        }
    }

    /**
     * Returns the contents of the log File. Requires user login
     * @param string  $domain
     * @param string  $type   Optional.
     * @param integer $lines  Optional.
     * @return string
     */
    public function getLog($domain, $type = "error", $lines = 10) {
        return $this->adapter->query("/CMD_SHOW_LOG", [
            "domain" => $domain,
            "type"   => $type,
            "lines"  => $lines,
        ], "GET", false);
    }


    
    /**
     * Creates a new User
     * @param array $data
     * @return array
     */
    public function create(array $data) {
        return $this->adapter->query("/CMD_API_ACCOUNT_USER", [
            "action"   => "create",
            "add"      => "Submit",
            "username" => $data["username"],
            "email"    => $data["email"],
            "passwd"   => $data["password"],
            "passwd2"  => $data["password"],
            "domain"   => $data["domain"],
            "package"  => $data["package"],
            "ip"       => $this->adapter->getHost(),
            "notify"   => "no",
        ]);
    }
    
    /**
     * Deletes the given User Account
     * @param string $username
     * @return array
     */
    public function delete($username) {
        return $this->adapter->query("/CMD_API_SELECT_USERS", [
            "confirmed" => "Confirm",
            "delete"    => "yes",
            "select0"   => $username,
        ], "POST");
    }
    
    

    /**
     * Suspends or Unsuspends the given User Account
     * @param string|string[] $username
     * @param boolean         $suspend  Optional.
     * @return array|null
     */
    public function suspend($username, $suspend = true) {
        $usernames = is_array($username) ? $username : [ $username ];
        $fields    = $suspend ? [ "dosuspend" => "Suspend" ] : [ "dounsuspend" => "Unsuspend" ];
        
        foreach ($usernames as $index => $value) {
            $fields["select$index"] = $value;
        }
        return $this->adapter->query("/CMD_API_SELECT_USERS", $fields, "POST");
    }
    
    /**
     * Moves the user from the current reseller to a new one
     * @param string $username
     * @param string $reseller
     * @return array|null
     */
    public function changeReseller($username, $reseller) {
        return $this->adapter->query("/CMD_API_MOVE_USERS", [
            "action"  => "moveusers",
            "select1" => $username,
            "creator" => $reseller,
        ]);
    }
    
    /**
     * Changes the User's Email
     * @param string $username
     * @param string $email
     * @return array|null
     */
    public function changeEmail($username, $email) {
        return $this->adapter->query("/CMD_API_MODIFY_USER", [
            "action" => "single",
            "email"  => "Save",
            "user"   => $username,
            "evalue" => $email,
        ], "POST");
    }
    
    /**
     * Changes the User's Username
     * @param string $username
     * @param string $newname
     * @return array|null
     */
    public function changeUsername($username, $newname) {
        return $this->adapter->query("/CMD_API_MODIFY_USER", [
            "action" => "single",
            "name"   => "Save",
            "user"   => $username,
            "nvalue" => $newname,
        ], "POST");
    }
    
    /**
     * Changes the User's Package
     * @param string $username
     * @param string $package
     * @return array|null
     */
    public function changePackage($username, $package) {
        return $this->adapter->query("/CMD_API_MODIFY_USER", [
            "action"  => "package",
            "user"    => $username,
            "package" => $package,
        ], "POST");
    }
    
    /**
     * Changes the Old Domain to the new Domain. Requires user login
     * @param string $oldDomain
     * @param string $newDomain
     * @return array|null
     */
    public function changeDomain($oldDomain, $newDomain) {
        return $this->adapter->query("/CMD_API_CHANGE_DOMAIN", [
            "old_domain" => $oldDomain,
            "new_domain" => $newDomain,
        ], "POST");
    }
    
    /**
     * Resets the given user's password
     * @param string $username
     * @param string $password
     * @return array|null
     */
    public function changePassword($username, $password) {
        return $this->adapter->query("/CMD_API_USER_PASSWD", [
            "username" => $username,
            "passwd"   => $password,
            "passwd2"  => $password,
        ], "POST");
    }
    
    /**
     * Sets the additional bandwidth for the given user
     * @param string  $username
     * @param integer $amount
     * @return array|null
     */
    public function addBandwidth($username, $amount) {
        return $this->adapter->query("/CMD_API_MODIFY_USER", [
            "additional_bandwidth" => $amount,
            "additional_bw"        => "add",
            "action"               => "single",
            "user"                 => $username,
        ], "POST");
    }
    
    /**
     * Sets the pulic stats. Requires user login
     * @param string $domain
     * @return array|null
     */
    public function setPublicStats($domain) {
        return $this->adapter->query("/CMD_API_PUBLIC_STATS", [
            "action"  => "public",
            "path"    => "awstats",
            "domain"  => $domain,
            "select0" => $domain,
        ]);
    }
    
    

    /**
     * Returns the spam configuration. Requires user login
     * @param string $domain
     * @return array|null
     */
    public function getSpamConfig($domain) {
        return $this->adapter->query("/CMD_API_SPAMASSASSIN", [
            "domain" => $domain,
        ]);
    }
    
    /**
     * Sets the spam configuration. Requires user login
     * @param string $domain
     * @param array  $data
     * @return array|null
     */
    public function setSpamConfig($domain, array $data) {
        return $this->adapter->query("/CMD_API_SPAMASSASSIN", [
            "action" => "save",
            "domain" => $domain,
            "is_on"  => "yes",
        ] + $data);
    }
}
