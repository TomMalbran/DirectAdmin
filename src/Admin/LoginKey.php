<?php
namespace DirectAdmin\Admin;

use DirectAdmin\Adapter;

/**
 * The Login Keys
 */
class LoginKey {
    
    private $adapter;
    
    /**
     * Creates a new LoginKey instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Creates a new Login Key
     * @param string  $key
     * @param string  $password
     * @param string  $ip
     * @param string  $keyname
     * @param boolean $isCreate Optional.
     * @return array|null
     */
    public function edit($key, $password, $ip, $keyname, $isCreate = true) {
        return $this->adapter->query("/CMD_API_LOGIN_KEYS", [
            "action"         => $isCreate ? "create" : "modify",
            "keyname"        => $keyname,
            "key"            => $key,
            "key2"           => $key,
            "never_expires"  => "yes",
            "max_uses"       => 0,
            "allow_htm"      => "no",
            "select_allow0"  => "ALL_ADMIN",
            "select_allow1"  => "ALL_RESELLER",
            "select_allow2"  => "ALL_USER",
            "select_deny67"  => "CMD_API_LOGIN_KEYS",
            "select_deny80"  => "CMD_API_PASSWD",
            "select_deny169" => "CMD_LOGIN_KEYS",
            "select_deny181" => "CMD_PASSWD",
            "ips"            => $ip,
            "passwd"         => $password,
            "create"         => $isCreate ? "Create" : "Modify",
        ]);
    }
    
    /**
     * Deletes the Login Key
     * @param string $keyname
     * @return array|null
     */
    public function delete($keyname) {
        return $this->adapter->query("/CMD_API_LOGIN_KEYS", [
            "select1" => $keyname,
            "delete"  => "Delete",
            "action"  => "select",
        ]);
    }
}
