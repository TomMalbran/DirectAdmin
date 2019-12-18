<?php
namespace DirectAdmin\Email;

use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Email Forwarders
 */
class Forwarder {
    
    private $adapter;
    
    /**
     * Creates a new Forwarder instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns a list with all the Email Forwarders for the given domain. Requires user login
     * @return array
     */
    public function getAll(): array {
        $response = $this->adapter->get("/CMD_API_EMAIL_FORWARDERS", [
            "domain" => $this->adapter->getDomain(),
        ]);
            
        $result = [ "data" => [], "list" => [] ];
        $index  = 0;
        foreach ($response->data as $user => $dest) {
            $result["data"][$index] = [
                "index" => $index,
                "user"  => $user,
                "dest"  => $dest,
                "to"    => explode(",", $dest),
            ];
            $result["list"][] = $user;
            $index += 1;
        }
        return $result;
    }
    
    
    
    /**
     * Creates an Email Forwarder. Requires user login
     * @param string $user
     * @param string $email
     * @return Response
     */
    public function create(string $user, string $email): Response {
        return $this->adapter->post("/CMD_API_EMAIL_FORWARDERS", [
            "action" => "create",
            "domain" => $this->adapter->getDomain(),
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
        return $this->adapter->post("/CMD_API_EMAIL_FORWARDERS", [
            "action" => "modify",
            "domain" => $this->adapter->getDomain(),
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
        return $this->adapter->post("/CMD_API_EMAIL_FORWARDERS", [
            "action"  => "delete",
            "domain"  => $this->adapter->getDomain(),
            "select0" => $user,
        ]);
    }
}
