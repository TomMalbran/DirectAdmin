<?php
namespace DirectAdmin\Domain;

use DirectAdmin\Adapter;

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
     * @param string $domain
     * @return string[]
     */
    public function getAll($domain) {
        $request = $this->adapter->query("/CMD_API_SUBDOMAINS", [ "domain" => $domain ]);
        return (!empty($request) && empty($request["error"]) && !empty($request["list"])) ? $request["list"] : [];
    }
    
    
    
    /**
     * Creates a new Subdomain for the given domain. Requires user login
     * @param string $domain
     * @param string $subdomain
     * @return array|null
     */
    public function create($domain, $subdomain) {
        return $this->adapter->query("/CMD_API_SUBDOMAINS", [
            "action"    => "create",
            "domain"    => $domain,
            "subdomain" => $subdomain,
        ]);
    }
    
    /**
     * Deletes the given Subdomain for the given domain. Requires user login
     * @param string  $domain
     * @param string  $subdomain
     * @param boolean $delContents Optional.
     * @return array|null
     */
    public function delete($domain, $subdomain, $delContents = false) {
        return $this->adapter->query("/CMD_API_SUBDOMAINS", [
            "action"   => "delete",
            "domain"   => $domain,
            "select0"  => $subdomain,
            "contents" => $delContents ? "yes" : "no",
        ]);
    }
}
