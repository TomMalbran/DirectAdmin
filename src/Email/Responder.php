<?php
namespace DirectAdmin\Email;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Email Responders
 */
class Responder extends Adapter {

    /**
     * Returns a list with all the Email Auto Responders. Requires user login
     * @return array{}[]
     */
    public function getAll(): array {
        $response = $this->get(Context::User, "/CMD_API_EMAIL_AUTORESPONDER");
        $result   = [];
        $index    = 0;

        foreach ($response->data as $user => $cc) {
            $data = $this->get(Context::User, "/CMD_API_EMAIL_AUTORESPONDER_MODIFY", [
                "user" => $user,
            ]);

            $result[] = [
                "index" => $index,
                "user"  => $user,
                "cc"    => $cc,
                "text"  => $data->data["text"],
            ];
            $index += 1;
        }
        return $result;
    }

    /**
     * Returns a list with all the Email Auto Responders usernames. Requires user login
     * @return string[]
     */
    public function getUsers(): array {
        $response = $this->get(Context::User, "/CMD_API_EMAIL_AUTORESPONDER");
        return $response->keys;
    }



    /**
     * Creates an Email Auto Responder. Requires user login
     * @param string $user
     * @param string $text
     * @param string $cc   Optional.
     * @return Response
     */
    public function create(string $user, string $text, string $cc = ""): Response {
        $fields = $this->createFields("create", $user, $text, $cc);
        return $this->post(Context::User, "/CMD_API_EMAIL_AUTORESPONDER", $fields);
    }

    /**
     * Edits an Email Auto Responder. Requires user login
     * @param string $user
     * @param string $text
     * @param string $cc   Optional.
     * @return Response
     */
    public function edit(string $user, string $text, string $cc = ""): Response {
        $fields = $this->createFields("modify", $user, $text, $cc);
        return $this->post(Context::User, "/CMD_API_EMAIL_AUTORESPONDER", $fields);
    }

    /**
     * Returns the fields to create or edit an Email Auto Responder
     * @param string $action
     * @param string $user
     * @param string $text
     * @param string $cc     Optional.
     * @return array{}
     */
    private function createFields(string $action, string $user, string $text, string $cc = ""): array {
        $fields = [
            "action" => $action,
            "user"   => $user,
            "text"   => $text,
        ];
        if (!empty($cc)) {
            $fields += [
                "cc"    => "ON",
                "email" => $cc,
            ];
        }
        return $fields;
    }



    /**
     * Deletes the Email Auto Responder with the given user in the given domain. Requires user login
     * @param string $user
     * @return Response
     */
    public function delete(string $user): Response {
        return $this->post(Context::User, "/CMD_API_EMAIL_AUTORESPONDER", [
            "action"  => "delete",
            "select0" => $user,
        ]);
    }
}
