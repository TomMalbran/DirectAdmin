<?php
namespace DirectAdmin\User;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The User FTP Accounts
 */
class FTPAccount extends Adapter {

    /**
     * Returns a list of FTP Accounts. Requires user login
     * @return array{}[]
     */
    public function getAll(): array {
        $response = $this->get(Context::User, "/CMD_API_FTP");
        $domain   = $this->context->domain;
        $result   = [];
        $index    = 0;

        foreach ($response->keys as $account) {
            $account = str_replace("_", ".", $account);
            $user    = str_replace("@$domain", "", $account);
            $data    = $this->get(Context::User, "/CMD_API_FTP_SHOW", [ "user" => $user ]);

            $result[$index] = [
                "index"   => $index,
                "account" => $data->data["fulluser"],
                "user"    => $data->data["user"],
                "type"    => $data->data["type"],
                "path"    => $data->data["path"],
                "isMain"  => $data->data["fulluser"] == $data->data["user"],
            ];
            $index += 1;
        }
        return $result;
    }

    /**
     * Returns a list of FTP Accounts. Requires user login
     * @return string[]
     */
    public function getList(): array {
        $response = $this->get(Context::User, "/CMD_API_FTP");
        $domain   = $this->context->domain;
        $result   = [];

        foreach ($response->keys as $account) {
            $account = str_replace("_", ".", $account);
            if (strpos($account, "@$domain") !== FALSE) {
                $result[] = str_replace("@$domain", "", $account);
            }
        }
        return $result;
    }



    /**
     * Creates an FTP Account. Requires user login
     * @param string $user
     * @param string $type
     * @param string $password Optional.
     * @param string $path     Optional.
     * @return Response
     */
    public function create(string $user, string $type, string $password = "", string $path = ""): Response {
        $fields = $this->createFields("create", $user, $type, $password, $path);
        return $this->post(Context::User, "/CMD_API_FTP", $fields);
    }

    /**
     * Edits an FTP Account. Requires user login
     * @param string $user
     * @param string $type
     * @param string $password Optional.
     * @param string $path     Optional.
     * @return Response
     */
    public function edit(string $user, string $type, string $password = "", string $path = ""): Response {
        $fields = $this->createFields("modify", $user, $type, $password, $path);
        return $this->post(Context::User, "/CMD_API_FTP", $fields);
    }

    /**
     * Returns the fields to create or edit an FTP Account
     * @param string $action
     * @param string $user
     * @param string $type
     * @param string $password Optional.
     * @param string $path     Optional.
     * @return array{}
     */
    private function createFields(string $action, string $user, string $type, string $password = "", string $path = ""): array {
        $fields = [
            "action"  => $action,
            "user"    => $user,
            "type"    => $type,
            "passwd"  => $password,
            "passwd2" => $password,
        ];
        if (!empty($path)) {
            $fields["custom_val"] = $path;
        }
        return $fields;
    }



    /**
     * Deletes the given FTP Account. Requires user login
     * @param string $user
     * @return Response
     */
    public function delete(string $user): Response {
        return $this->post(Context::User, "/CMD_API_FTP", [
            "action"  => "delete",
            "select0" => $user,
        ]);
    }
}
