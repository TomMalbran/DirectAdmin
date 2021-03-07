<?php
namespace DirectAdmin\User;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The User Account
 */
class Account extends Adapter {

    /**
     * Returns the Users limits and usage
     * @return array
     */
    public function getInfo(): array {
        $fields = [ "bandwidth", "quota", "domainptr", "mysql", "nemailf", "nemailr", "nemails", "nsubdomains", "ftp" ];
        $user   = $this->context->user;
        $domain = $this->context->domain;
        $usage  = $this->get(Context::Admin, "/CMD_API_SHOW_USER_USAGE", [ "user" => $user ]);

        if ($usage->hasError) {
            $usage  = $this->get(Context::Admin, "/CMD_API_SHOW_USER_USAGE",  [ "domain" => $domain ]);
            $config = $this->get(Context::Admin, "/CMD_API_SHOW_USER_CONFIG", [ "domain" => $domain ]);
        } else {
            $config = $this->get(Context::Admin, "/CMD_API_SHOW_USER_CONFIG", [ "user" => $user ]);
        }
        $result = [];

        if (!$usage->hasError && !$config->hasError) {
            foreach ($fields as $field) {
                if (isset($usage->data[$field]) && isset($config->data[$field])) {
                    $result[$field] = [
                        "used"   => (int)$usage->data[$field],
                        "total"  => $config->data[$field] == "unlimited" ? -1 : (int)$config->data[$field],
                        "canAdd" => $config->data[$field] == "unlimited" || (int)$usage->data[$field] < (int)$config->data[$field],
                    ];
                }
            }

            if (!empty($result)) {
                $result["dbQuota"]    = [ "used" => isset($usage->data["db_quota"])    ? (int)$usage->data["db_quota"]    : 0 ];
                $result["emailQuota"] = [ "used" => isset($usage->data["email_quota"]) ? (int)$usage->data["email_quota"] : 0 ];

                $result["dbQuota"]["used"]    = floor(($result["dbQuota"]["used"]    / (1024 * 1024)) * 100) / 100;
                $result["emailQuota"]["used"] = floor(($result["emailQuota"]["used"] / (1024 * 1024)) * 100) / 100;

                $result["bandwidth"]["additional"] = !empty($config->data["additional_bandwidth"]) ? (int)$config->data["additional_bandwidth"] : 0;
            }
        }

        return $result;
    }

    /**
     * Returns the User's Configuration
     * @return array
     */
    public function getConfig() {
        $response = $this->get(Context::Admin, "/CMD_API_SHOW_USER_CONFIG", [
            "user" => $this->context->user,
        ]);
        return $response->data;
    }

    /**
     * Returns the User's Main Domain
     * @return string
     */
    public function getMainDomain(): string {
        $response = $this->get(Context::Admin, "/CMD_API_SHOW_USER_DOMAINS", [
            "user" => $this->context->user,
        ]);
        foreach ($response->keys as $key) {
            return str_replace("_", ".", $key);
        }
        return "";
    }



    /**
     * Suspends or Unsuspends the User
     * @param boolean $suspend Optional.
     * @return Response
     */
    public function suspend(bool $suspend = true): Response {
        $fields = $suspend ? [ "dosuspend" => "Suspend" ] : [ "dounsuspend" => "Unsuspend" ];
        $fields["select0"] = $this->context->user;
        return $this->post(Context::Admin, "/CMD_API_SELECT_USERS", $fields);
    }

    /**
     * Moves the User from the current reseller to a new one
     * @param string $reseller
     * @return Response
     */
    public function changeReseller(string $reseller): Response {
        return $this->post(Context::Admin, "/CMD_API_MOVE_USERS", [
            "action"  => "moveusers",
            "select1" => $this->context->user,
            "creator" => $reseller,
        ]);
    }

    /**
     * Changes the User's Email
     * @param string $email
     * @return Response
     */
    public function changeEmail(string $email): Response {
        return $this->post(Context::Admin, "/CMD_API_MODIFY_USER", [
            "action" => "single",
            "email"  => "Save",
            "user"   => $this->context->user,
            "evalue" => $email,
        ]);
    }

    /**
     * Changes the User's Username
     * @param string $newName
     * @return Response
     */
    public function changeUsername(string $newName): Response {
        return $this->post(Context::Admin, "/CMD_API_MODIFY_USER", [
            "action" => "single",
            "name"   => "Save",
            "user"   => $this->context->user,
            "nvalue" => $newName,
        ]);
    }

    /**
     * Changes the User's Package
     * @param string $package
     * @return Response
     */
    public function changePackage(string $package): Response {
        return $this->post(Context::Admin, "/CMD_API_MODIFY_USER", [
            "action"  => "package",
            "user"    => $this->context->user,
            "package" => $package,
        ]);
    }

    /**
     * Changes the old Domain to the new Domain. Requires user login
     * @param string $domain
     * @return Response
     */
    public function changeDomain(string $domain): Response {
        return $this->post(Context::User, "/CMD_API_CHANGE_DOMAIN", [
            "old_domain" => $this->context->domain,
            "new_domain" => $domain,
        ]);
    }

    /**
     * Resets the given User's Password
     * @param string $password
     * @return Response
     */
    public function changePassword(string $password): Response {
        return $this->post(Context::Admin, "/CMD_API_USER_PASSWD", [
            "username" => $this->context->user,
            "passwd"   => $password,
            "passwd2"  => $password,
        ]);
    }

    /**
     * Sets the User's Additional Bandwidth
     * @param integer $amount
     * @return Response
     */
    public function addBandwidth(int $amount): Response {
        return $this->post(Context::Admin, "/CMD_API_MODIFY_USER", [
            "user"                 => $this->context->user,
            "additional_bandwidth" => $amount,
            "additional_bw"        => "add",
            "action"               => "single",
        ]);
    }



    /**
     * Sets the Public Stats. Requires user login
     * @return Response
     */
    public function setPublicStats(): Response {
        return $this->post(Context::User, "/CMD_API_PUBLIC_STATS", [
            "action"  => "public",
            "path"    => "awstats",
            "select0" => $this->context->domain,
        ]);
    }

    /**
     * Returns the contents of the Error Log File. Requires user login
     * @param integer $lines Optional.
     * @return string
     */
    public function getErrorLog(int $lines = 10) {
        $response = $this->get(Context::User, "/CMD_SHOW_LOG", [
            "type"  => "error",
            "lines" => $lines,
        ]);
        return $response->raw;
    }

    /**
     * Returns the contents of the Access Log File. Requires user login
     * @param integer $lines Optional.
     * @return string
     */
    public function getAccessLog(int $lines = 10) {
        $response = $this->get(Context::User, "/CMD_SHOW_LOG", [
            "type"  => "access",
            "lines" => $lines,
        ]);
        return $response->raw;
    }

    /**
     * Returns the Spam Configuration. Requires user login
     * @return Response
     */
    public function getSpamConfig(): Response {
        return $this->post(Context::User, "/CMD_API_SPAMASSASSIN");
    }

    /**
     * Sets the Spam Configuration. Requires user login
     * @param array $data
     * @return Response
     */
    public function setSpamConfig(array $data): Response {
        return $this->post(Context::User, "/CMD_API_SPAMASSASSIN", [
            "action" => "save",
            "is_on"  => "yes",
        ] + $data);
    }
}
