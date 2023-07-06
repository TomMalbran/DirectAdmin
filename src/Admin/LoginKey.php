<?php
namespace DirectAdmin\Admin;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Login Keys
 */
class LoginKey extends Adapter {

    /**
     * Creates a new Login Key
     * @param string $key
     * @param string $password
     * @param string $ip
     * @param string $keyName
     * @return Response
     */
    public function create(string $key, string $password, string $ip, string $keyName): Response {
        $fields = $this->createFields($key, $password, $ip, $keyName, true);
        return $this->post(Context::Admin, "/CMD_API_LOGIN_KEYS", $fields);
    }

    /**
     * Edits a Login Key
     * @param string $key
     * @param string $password
     * @param string $ip
     * @param string $keyName
     * @return Response
     */
    public function edit(string $key, string $password, string $ip, string $keyName): Response {
        $fields = $this->createFields($key, $password, $ip, $keyName, false);
        return $this->post(Context::Admin, "/CMD_API_LOGIN_KEYS", $fields);
    }

    /**
     * Creates the Login Key fields
     * @param string  $key
     * @param string  $password
     * @param string  $ip
     * @param string  $keyName
     * @param boolean $isCreate Optional.
     * @return array{}
     */
    private function createFields(string $key, string $password, string $ip, string $keyName, bool $isCreate): array {
        return [
            "action"         => $isCreate ? "create" : "modify",
            "keyname"        => $keyName,
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
     * @param string $keyName
     * @return Response
     */
    public function delete(string $keyName): Response {
        return $this->post(Context::Admin, "/CMD_API_LOGIN_KEYS", [
            "delete"  => "Delete",
            "action"  => "select",
            "select1" => $keyName,
        ]);
    }
}
