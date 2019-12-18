<?php
namespace DirectAdmin\Email;

use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Email Accounts
 */
class Email {
    
    private $adapter;
    
    /**
     * Creates a new Email instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns a list with all the Email Accounts Data for the given domain. Requires user login
     * @return array
     */
    public function getAll(): array {
        $domain   = $this->adapter->getDomain();
        $response = $this->adapter->get("/CMD_API_POP", [
            "action" => "full_list",
            "domain" => $domain,
        ]);
        
        $result = [];
        $index  = 0;
        foreach ($response->data as $user => $data) {
            $result[] = [
                "index"       => $index,
                "user"        => $user,
                "email"       => "$user@$domain",
                "quota"       => !empty($data["quota"]) ? (float)$data["quota"] : 0,
                "usage"       => !empty($data["usage"]) ? (float)$data["usage"] : 0,
                "isSuspended" => !empty($data["suspended"]) ? $data["suspended"] == "yes" : false,
            ];
            $index += 1;
        }
        return $result;
    }
    
    /**
     * Returns a list with all the Email Accounts usernames for the given domain. Requires user login
     * @return string[]
     */
    public function getUsers(): array {
        $response = $this->adapter->get("/CMD_API_POP", [
            "action" => "list",
            "domain" => $this->adapter->getDomain(),
        ]);
        return $response->list;
    }
    
    /**
     * Returns the Quota information for the given Email Account for the given domain. Requires user login
     * @param string $user
     * @return Response
     */
    public function getQuota(string $user): Response {
        return $this->adapter->get("/CMD_API_POP", [
            "type"   => "quota",
            "domain" => $this->adapter->getDomain(),
            "user"   => $user,
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
        return $this->adapter->post("/CMD_API_POP", $fields);
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
        return $this->adapter->post("/CMD_API_POP", $fields);
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
            "domain" => $this->adapter->getDomain(),
            "user"   => $user,
            "quota"  => $quota,
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
        $domain   = $this->adapter->getDomain();
        $email    = "$user@$domain";
        $response = $this->adapter->get("/CMD_EMAIL_REG/$domain/$email/$email/outlook_$user.reg");
        return $response->raw;
    }
    
    /**
     * Suspends the Email Account with the given user in the given domain. Requires user login
     * @param string $user
     * @return Response
     */
    public function suspend(string $user): Response {
        return $this->adapter->post("/CMD_API_POP", [
            "suspend" => "Suspend",
            "action"  => "delete",
            "domain"  => $this->adapter->getDomain(),
            "user"    => $user,
        ]);
    }
    
    /**
     * Unsuspends the Email Account with the given user in the given domain. Requires user login
     * @param string $user
     * @return Response
     */
    public function unsuspend(string $user): Response {
        return $this->adapter->post("/CMD_API_POP", [
            "unsuspend" => "Unsuspend",
            "action"    => "delete",
            "domain"    => $this->adapter->getDomain(),
            "user"      => $user,
        ]);
    }
    
    
    
    /**
     * Deletes the Email Account with the given user in the given domain. Requires user login
     * @param string $user
     * @return Response
     */
    public function delete(string $user): Response {
        return $this->adapter->post("/CMD_API_POP", [
            "action"           => "delete",
            "clean_forwarders" => "yes",
            "domain"           => $this->adapter->getDomain(),
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
            "domain" => $this->adapter->getDomain(),
            "file"   => $file,
            "what"   => $what,
        ];
        $users = is_array($user) ? $user : [ $user ];
        foreach ($users as $index => $value) {
            $fields["select$index"] = $value;
        }
        return $this->adapter->post("/CMD_EMAIL_POP", $fields);
    }
    
    
    
    /**
     * Enables/Disables the email local server. Requires user login
     * @param boolean $disable
     * @return Response
     */
    public function toggleMXDNS(bool $disable): Response {
        return $this->adapter->post("/CMD_API_DNS_MX", [
            "action"   => "internal",
            "domain"   => $this->adapter->getDomain(),
            "internal" => $disable ? "no" : "yes",
        ]);
    }
}
