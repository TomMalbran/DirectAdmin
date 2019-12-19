<?php
namespace DirectAdmin\Admin;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Result;

/**
 * The Login Keys
 */
class LoginKey extends Adapter {
    
    /**
     * Creates a new Login Key
     * @param string $key
     * @param string $password
     * @param string $ip
     * @param string $keyname
     * @return Response
     */
    public function create(string $key, string $password, string $ip, string $keyname): Response {
        $fields = $this->createFields($key, $password, $ip, $keyname, true);
        return $this->post(Context::Admin, "/CMD_API_LOGIN_KEYS", $fields);
    }

    /**
     * Edits a Login Key
     * @param string $key
     * @param string $password
     * @param string $ip
     * @param string $keyname
     * @return Response
     */
    public function edit(string $key, string $password, string $ip, string $keyname): Response {
        $fields = $this->createFields($key, $password, $ip, $keyname, false);
        return $this->post(Context::Admin, "/CMD_API_LOGIN_KEYS", $fields);
    }

    /**
     * Creates the Login Key fields
     * @param string  $key
     * @param string  $password
     * @param string  $ip
     * @param string  $keyname
     * @param boolean $isCreate Optional.
     * @return array
     */
    private function createFields(string $key, string $password, string $ip, string $keyname, bool $isCreate): array {
        return [
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
        ];
    }


    
    /**
     * Deletes the Login Key
     * @param string $keyname
     * @return Response
     */
    public function delete(string $keyname): Response {
        return $this->post(Context::Admin, "/CMD_API_LOGIN_KEYS", [
            "delete"  => "Delete",
            "action"  => "select",
            "select1" => $keyname,
        ]);
    }
}
