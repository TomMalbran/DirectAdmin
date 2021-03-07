<?php
namespace DirectAdmin\Email;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Email Accounts
 */
class Email extends Adapter {

    /**
     * Returns a list with all the Email Accounts Data. Requires user login
     * @return array
     */
    public function getAll(): array {
        $response = $this->get(Context::User, "/CMD_API_POP", [
            "action" => "full_list",
        ]);

        $result = [];
        $index  = 0;
        foreach ($response->data as $user => $data) {
            $result[] = [
                "index"       => $index,
                "user"        => $user,
                "email"       => "$user@{$this->context->domain}",
                "quota"       => !empty($data["quota"]) ? (float)$data["quota"] : 0,
                "usage"       => !empty($data["usage"]) ? (float)$data["usage"] : 0,
                "isSuspended" => !empty($data["suspended"]) ? $data["suspended"] == "yes" : false,
            ];
            $index += 1;
        }
        return $result;
    }

    /**
     * Returns a list with all the Email Accounts usernames. Requires user login
     * @return string[]
     */
    public function getUsers(): array {
        $response = $this->get(Context::User, "/CMD_API_POP", [
            "action" => "list",
        ]);
        return $response->list;
    }

    /**
     * Returns the Quota information for the given Email Account. Requires user login
     * @param string $user
     * @return Response
     */
    public function getQuota(string $user): Response {
        return $this->get(Context::User, "/CMD_API_POP", [
            "type" => "quota",
            "user" => $user,
        ]);
    }



    /**
     * Creates an Email Account. Requires user login
     * @param string  $user
     * @param string  $password Optional.
     * @param integer $quota    Optional.
     * @return Response
     */
    public function create(string $user, string $password = "", int $quota = 0): Response {
        $fields = $this->createFields([
            "action" => "create",
        ], $user, $password, $quota);
        return $this->post(Context::User, "/CMD_API_POP", $fields);
    }

    /**
     * Edits an Email Account. Requires user login
     * @param string  $user
     * @param string  $newuser
     * @param string  $password Optional.
     * @param integer $quota    Optional.
     * @return Response
     */
    public function edit(string $user, string $newuser, string $password = "", int $quota = 0): Response {
        $fields = $this->createFields([
            "action"  => "modify",
            "newuser" => $newuser,
        ], $user, $password, $quota);
        return $this->post(Context::User, "/CMD_API_POP", $fields);
    }

    /**
     * Returns the fields to create or edit an Email Account
     * @param array   $fields
     * @param string  $user
     * @param string  $password
     * @param integer $quota
     * @return array
     */
    private function createFields(array $fields, string $user, string $password, int $quota): array {
        $fields += [
            "user"  => $user,
            "quota" => $quota,
        ];
        if (!empty($password)) {
            $fields += [
                "passwd"  => $password,
                "passwd2" => $password,
            ];
        }
        return $fields;
    }



    /**
     * Returns the contents of the outlook reg file. Requires user login
     * @param string $user
     * @return string
     */
    public function getOutlook(string $user): string {
        $domain   = $this->context->domain;
        $email    = "$user@$domain";
        $response = $this->get(Context::User, "/CMD_EMAIL_REG/$domain/$email/$email/outlook_$user.reg");
        return $response->raw;
    }

    /**
     * Suspends the Email Account with the given user in the given domain. Requires user login
     * @param string $user
     * @return Response
     */
    public function suspend(string $user): Response {
        return $this->post(Context::User, "/CMD_API_POP", [
            "suspend" => "Suspend",
            "action"  => "delete",
            "user"    => $user,
        ]);
    }

    /**
     * Unsuspends the Email Account with the given user in the given domain. Requires user login
     * @param string $user
     * @return Response
     */
    public function unsuspend(string $user): Response {
        return $this->post(Context::User, "/CMD_API_POP", [
            "unsuspend" => "Unsuspend",
            "action"    => "delete",
            "user"      => $user,
        ]);
    }



    /**
     * Deletes the Email Account with the given user in the given domain. Requires user login
     * @param string $user
     * @return Response
     */
    public function delete(string $user): Response {
        return $this->post(Context::User, "/CMD_API_POP", [
            "action"           => "delete",
            "clean_forwarders" => "yes",
            "user"             => $user,
        ]);
    }

    /**
     * Purges the Emails from the given domain. Requires user login
     * @param string[]|string $user
     * @param string          $file
     * @param string          $what
     * @return Response
     */
    public function purge($user, string $file, string $what): Response {
        $fields = [
            "action" => "delete",
            "purge"  => "Purge From",
            "file"   => $file,
            "what"   => $what,
        ];
        $users = is_array($user) ? $user : [ $user ];
        foreach ($users as $index => $value) {
            $fields["select$index"] = $value;
        }
        return $this->post(Context::User, "/CMD_EMAIL_POP", $fields);
    }



    /**
     * Enables/Disables the email local server. Requires user login
     * @param boolean $disable
     * @return Response
     */
    public function toggleMXDNS(bool $disable): Response {
        return $this->post(Context::User, "/CMD_API_DNS_MX", [
            "action"   => "internal",
            "internal" => $disable ? "no" : "yes",
        ]);
    }
}
