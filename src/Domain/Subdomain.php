<?php
namespace DirectAdmin\Domain;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Server Subdomains
 */
class Subdomain extends Adapter {
    
    /**
     * Returns a list of Subdomains. Requires user login
     * @return string[]
     */
    public function getAll(): array {
        $response = $this->get(Context::User, "/CMD_API_SUBDOMAINS");
        return $response->list;
    }
    
    
    
    /**
     * Creates a new Subdomain. Requires user login
     * @param string $subdomain
     * @return Response
     */
    public function create(string $subdomain): Response {
        return $this->post(Context::User, "/CMD_API_SUBDOMAINS", [
            "action"    => "create",
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
        return $this->post(Context::User, "/CMD_API_SUBDOMAINS", [
            "action"   => "delete",
            "select0"  => $subdomain,
            "contents" => $delContents ? "yes" : "no",
        ]);
    }
}
