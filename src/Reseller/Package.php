<?php
namespace DirectAdmin\Reseller;

use DirectAdmin\Adapter;

/**
 * The Reseller Packages
 */
class Package {
    
    private $adapter;
    
    /**
     * Creates a new Package instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns the list of users packages for the current Reseller
     * @return array
     */
    public function getUsers() {
        $request = $this->adapter->query("/CMD_API_PACKAGES_USER");
        $result  = [];
        
        if (!empty($request) && empty($request["error"])) {
            foreach ($request["list"] as $name) {
                $package  = $this->adapter->query("/CMD_API_PACKAGES_USER", [ "package" => $name ]);
                $result[] = [
                    "name"      => $name,
                    "storage"   => $package["quota"],
                    "bandwidth" => $package["bandwidth"],
                    "dbs"       => $package["mysql"],
                ];
            }
        }
        return $result;
    }
    


    /**
     * Creates or Edits a User Package
     * @param string  $name
     * @param integer $storage
     * @param integer $bandwidth
     * @param integer $databases
     * @return array|null
     */
    public function editUsers($name, $storage, $bandwidth, $databases) {
        return $this->adapter->query("/CMD_API_MANAGE_USER_PACKAGES", [
            "add"              => "Save",
            "packagename"      => $name,
            "bandwidth"        => $bandwidth,
            "ubandwidth"       => $bandwidth == 0 ? "ON" : "OFF",
            "quota"            => $storage,
            "uquota"           => $storage == 0 ? "ON" : "OFF",
            "uinode"           => "ON",
            "vdomains"         => 1,
            "unsubdomains"     => "ON",
            "unemails"         => "ON",
            "unemailf"         => "ON",
            "nemailml"         => 0,
            "unemailr"         => "ON",
            "mysql"            => $databases,
            "udomainptr"       => "ON",
            "uftp"             => "ON",
            "cgi"              => "OFF",
            "php"              => "ON",
            "spam"             => "ON",
            "ssl"              => "ON",
            "cron"             => "ON",
            "sysinfo"          => "OFF",
            "dnscontrol"       => "ON",
            "suspend_at_limit" => "ON",
            "skin"             => "enhanced",
            "language"         => "en",
        ]);
    }
    
    /**
     * Deletes the given User Package
     * @param string $name
     * @return array|null
     */
    public function deleteUsers($name) {
        return $this->adapter->query("/CMD_API_MANAGE_USER_PACKAGES", [
            "delete"  => "Delete",
            "delete0" => $name,
        ]);
    }
    
    

    /**
     * Creates or Edits a User Package
     * @param string  $name
     * @param integer $domains
     * @param integer $storage
     * @param integer $bandwidth
     * @param integer $databases
     * @return array|null
     */
    public function editResellers($name, $domains, $storage, $bandwidth, $databases) {
        return $this->adapter->query("/CMD_API_MANAGE_RESELLER_PACKAGES", [
            "add"          => "Save",
            "packagename"  => $name,
            "bandwidth"    => $bandwidth,
            "quota"        => $storage,
            "uinode"       => "ON",
            "vdomains"     => $domains,
            "unsubdomains" => "ON",
            "ips"          => 0,
            "unemails"     => "ON",
            "unemailf"     => "ON",
            "nemailml"     => 0,
            "unemailr"     => "ON",
            "mysql"        => $databases,
            "udomainptr"   => "ON",
            "uftp"         => "ON",
            "cgi"          => "ON",
            "php"          => "ON",
            "spam"         => "ON",
            "catchall"     => "ON",
            "ssl"          => "ON",
            "oversell"     => "ON",
            "cron"         => "ON",
            "sysinfo"      => "ON",
            "login_keys"   => "ON",
            "dnscontrol"   => "ON",
            "dns"          => "OFF",
            "serverip"     => "ON",
        ]);
    }
    
    /**
     * Deletes the given Reseller Package
     * @param string $name
     * @return array|null
     */
    public function deleteResellers($name) {
        return $this->adapter->query("/CMD_API_MANAGE_RESELLER_PACKAGES", [
            "delete"  => "Delete",
            "delete0" => $name,
        ]);
    }
}
