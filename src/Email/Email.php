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
        $index    = 0;
        $result   = [];
        $response = $this->adapter->query("/CMD_API_POP", [
            "action" => "full_list",
            "domain" => $this->adapter->getDomain(),
        ], "GET", true, true);
        
        foreach ($response->data as $user => $data) {
            parse_str($data, $data);
            $result[$index] = [
                "index"       => $index,
                "user"        => str_replace("___", ".", $user),
                "quota"       => !empty($data["quota"]) ? str_replace("___", ".", $data["quota"]) : 0,
                "usage"       => !empty($data["usage"]) ? str_replace("___", ".", $data["usage"]) : 0,
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
        $response = $this->adapter->query("/CMD_API_POP", [
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
        return $this->adapter->query("/CMD_API_POP", [
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
        $fields = $this->getFields($user, $password, $quota);
        $fields["action"] = "create";
        return $this->adapter->query("/CMD_API_POP", $fields);
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
        $fields = $this->getFields($user, $password, $quota);
        $fields["action"]  = "modify";
        $fields["newuser"] = $newuser;
        return $this->adapter->query("/CMD_API_POP", $fields);
    }
    
    /**
     * Returns the fields to create or edit an Email Account
     * @param string  $user
     * @param string  $password
     * @param integer $quota
     * @return array
     */
    private function getfields(string $user, string $password, int $quota): array {
        $fields = [
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
        $response = $this->adapter->query("/CMD_EMAIL_REG/$domain/$email/$email/outlook_$user.reg", [], "GET", false);
        return $response->data;
    }
    
    /**
     * Suspends the Email Account with the given user in the given domain. Requires user login
     * @param string $user
     * @return Response
     */
    public function suspend(string $user): Response {
        return $this->adapter->query("/CMD_API_POP", [
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
        return $this->adapter->query("/CMD_API_POP", [
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
        return $this->adapter->query("/CMD_API_POP", [
            "action"           => "delete",
            "clean_forwarders" => "yes",
            "domain"           => $this->adapter->getDomain(),
            "user"             => $user,
        ]);
    }
    
    /**
     * Purges the Emails from the given domain. Requires user login
     * @param string[] $users
     * @param string   $file
     * @param string   $what
     * @return Response
     */
    public function purge(array $users, string $file, string $what): Response {
        $fields = [
            "action" => "delete",
            "purge"  => "Purge From",
            "domain" => $this->adapter->getDomain(),
            "file"   => $file,
            "what"   => $what,
        ];
        foreach ($users as $key => $value) {
            $fields["select$key"] = $value;
        }
        return $this->adapter->query("/CMD_EMAIL_POP", $fields);
    }
    
    
    
    /**
     * Enables/Disables the email local server. Requires user login
     * @param boolean $disable
     * @return Response
     */
    public function toggleMXDNS(bool $disable): Response {
        return $this->adapter->query("/CMD_API_DNS_MX", [
            "action"   => "internal",
            "domain"   => $this->adapter->getDomain(),
            "internal" => $disable ? "no" : "yes",
        ]);
    }
}