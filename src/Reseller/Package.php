<?php
namespace DirectAdmin\Reseller;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Reseller Packages
 */
class Package extends Adapter {

    /**
     * Returns the list of users packages for the current Reseller
     * @return array
     */
    public function getUsers() {
        $response = $this->get(Context::Reseller, "/CMD_API_PACKAGES_USER");
        $result   = [];

        foreach ($response->list as $name) {
            $package  = $this->get(Context::Reseller, "/CMD_API_PACKAGES_USER", [ "package" => $name ]);
            $result[] = [
                "name"      => $name,
                "storage"   => $package->data["quota"],
                "bandwidth" => $package->data["bandwidth"],
                "dbs"       => $package->data["mysql"],
            ];
        }
        return $result;
    }



    /**
     * Creates or Edits a User Package
     * @param string  $name
     * @param integer $storage
     * @param integer $bandwidth
     * @param integer $databases
     * @return Response
     */
    public function editUsers(string $name, int $storage, int $bandwidth, int $databases): Response {
        return $this->post(Context::Reseller, "/CMD_API_MANAGE_USER_PACKAGES", [
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
     * @return Response
     */
    public function deleteUsers(string $name): Response {
        return $this->post(Context::Reseller, "/CMD_API_MANAGE_USER_PACKAGES", [
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
     * @return Response
     */
    public function editResellers(string $name, int $domains, int $storage, int $bandwidth, int $databases): Response {
        return $this->post(Context::Admin, "/CMD_API_MANAGE_RESELLER_PACKAGES", [
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
     * @return Response
     */
    public function deleteResellers(string $name): Response {
        return $this->post(Context::Admin, "/CMD_API_MANAGE_RESELLER_PACKAGES", [
            "delete"  => "Delete",
            "delete0" => $name,
        ]);
    }
}
