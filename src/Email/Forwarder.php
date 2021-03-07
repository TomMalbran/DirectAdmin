<?php
namespace DirectAdmin\Email;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Email Forwarders
 */
class Forwarder extends Adapter {

    /**
     * Returns a list with all the Email Forwarders. Requires user login
     * @return array
     */
    public function getAll(): array {
        $response = $this->get(Context::User, "/CMD_API_EMAIL_FORWARDERS");
        $result   = [];
        $index    = 0;

        foreach ($response->data as $user => $dest) {
            $result[] = [
                "index" => $index,
                "user"  => $user,
                "dest"  => $dest,
                "to"    => explode(",", $dest),
            ];
            $index += 1;
        }
        return $result;
    }

    /**
     * Returns a list with all the Email Forwarders usernames. Requires user login
     * @return string[]
     */
    public function getUsers(): array {
        $response = $this->get(Context::User, "/CMD_API_EMAIL_FORWARDERS");
        return $response->keys;
    }


    /**
     * Creates an Email Forwarder. Requires user login
     * @param string $user
     * @param string $email
     * @return Response
     */
    public function create(string $user, string $email): Response {
        return $this->post(Context::User, "/CMD_API_EMAIL_FORWARDERS", [
            "action" => "create",
            "user"   => $user,
            "email"  => $email,
        ]);
    }

    /**
     * Edits an Email Forwarder. Requires user login
     * @param string $user
     * @param string $email
     * @return Response
     */
    public function edit(string $user, string $email): Response {
        return $this->post(Context::User, "/CMD_API_EMAIL_FORWARDERS", [
            "action" => "modify",
            "user"   => $user,
            "email"  => $email,
        ]);
    }

    /**
     * Deletes the Email Forwarder with the given user in the given domain. Requires user login
     * @param string $user
     * @return Response
     */
    public function delete(string $user): Response {
        return $this->post(Context::User, "/CMD_API_EMAIL_FORWARDERS", [
            "action"  => "delete",
            "select0" => $user,
        ]);
    }
}
