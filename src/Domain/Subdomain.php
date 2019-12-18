<?php
namespace DirectAdmin\Domain;

use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Server Subdomains
 */
class Subdomain {
    
    private $adapter;
    
    /**
     * Creates a new Subdomain instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns a list of Subdomains. Requires user login
     * @return string[]
     */
    public function getAll(): array {
        $response = $this->adapter->get("/CMD_API_SUBDOMAINS", [
            "domain" => $this->adapter->getDomain(),
        ]);
        return $response->list;
    }
    
    
    
    /**
     * Creates a new Subdomain. Requires user login
     * @param string $subdomain
     * @return Response
     */
    public function create(string $subdomain): Response {
        return $this->adapter->post("/CMD_API_SUBDOMAINS", [
            "action"    => "create",
            "domain"    => $this->adapter->getDomain(),
            "subdomain" => $subdomain,
        ]);
    }
    
    /**
     * Deletes the given Subdomain. Requires user login
     * @param string  $subdomain
     * @param boolean $delContents Optional.
     * @return Response
     */
    public function delete(string $subdomain, bool $delContents = false): Response {
        return $this->adapter->post("/CMD_API_SUBDOMAINS", [
            "action"   => "delete",
            "domain"   => $this->adapter->getDomain(),
            "select0"  => $subdomain,
            "contents" => $delContents ? "yes" : "no",
        ]);
    }
}
