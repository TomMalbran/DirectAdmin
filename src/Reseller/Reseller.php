<?php
namespace DirectAdmin\Reseller;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Reseller Accounts
 */
class Reseller extends Adapter {

    /**
     * Returns the list of Resellers for the current server
     * @return array
     */
    public function getAll(): array {
        $response = $this->get(Context::Admin, "/CMD_API_SHOW_RESELLERS");
        return $response->list;
    }

    /**
     * Returns the Reseller limits and usage
     * @param string $user
     * @return array
     */
    public function getInfo(string $user): array {
        $fields = [ "bandwidth", "quota", "domainptr", "mysql", "nemailf", "nemailr", "nemails", "nsubdomains", "vdomains" ];
        $config = $this->get(Context::Admin, "/CMD_API_RESELLER_STATS", [ "user" => $user ]);

        if (!$config->hasError) {
            $usage     = $this->get(Context::Admin, "/CMD_API_RESELLER_STATS", [
                "user" => $user,
                "type" => "usage",
            ]);
            $allocated = $this->get(Context::Admin, "/CMD_API_RESELLER_STATS", [
                "user" => $user,
                "type" => "allocated",
            ]);
        }
        $result = [];

        foreach ($fields as $field) {
            if (isset($config->data[$field])) {
                $result[$field] = [
                    "used"      => isset($usage->data[$field])     ? (int)$usage->data[$field]     : 0,
                    "allocated" => isset($allocated->data[$field]) ? (int)$allocated->data[$field] : 0,
                    "total"     => $config->data[$field] == "unlimited" ? -1 : (int)$config->data[$field],
                ];
            }
        }

        return $result;
    }



    /**
     * Creates a new Reseller
     * @param array $data
     * @return Response
     */
    public function create(array $data): Response {
        return $this->post(Context::Admin, "/CMD_API_ACCOUNT_RESELLER", [
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
     * @param string $user
     * @param string $password
     * @param string $domain   Optional.
     * @param string $email    Optional.
     * @return Response
     */
    public function createUnlimited(string $user, string $password, string $domain = "", string $email = ""): Response {
        return $this->post(Context::Admin, "/CMD_API_ACCOUNT_RESELLER", [
            "action"       => "create",
            "add"          => "Submit",
            "username"     => $user,
            "passwd"       => $password,
            "passwd2"      => $password,
            "email"        => !empty($email)  ? $email  : (!empty($domain) ? "info@$domain" : "info@$user.com"),
            "domain"       => !empty($domain) ? $domain : "$user.com",
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
     * @param string $user
     * @param string $package
     * @return Response
     */
    public function changePackage(string $user, string $package) {
        return $this->post(Context::Admin, "/CMD_API_MODIFY_RESELLER", [
            "action"  => "package",
            "user"    => $user,
            "package" => $package,
        ]);
    }
}
