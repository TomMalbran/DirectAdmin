<?php
namespace DirectAdmin\Domain;

use DirectAdmin\Adapter;

/**
 * The Server Redirects
 */
class Redirect {
    
    private $adapter;
    
    /**
     * Creates a new Redirect instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns a list of redirects. Requires user login
     * @param string $domain
     * @return array
     */
    public function getAll($domain) {
        $request = $this->adapter->query("/CMD_API_REDIRECT", [ "domain" => $domain ]);
        $result  = [];
        
        if (!empty($request) && empty($request["error"])) {
            foreach ($request as $from => $to) {
                $result[] = [
                    "from" => $from,
                    "to"   => $to,
                ];
            }
            return $result;
        }
        return $request;
    }
    
    
    
    /**
     * Adds a new Redirect for the given domain. Requires user login
     * @param string $domain
     * @param string $from
     * @param string $to
     * @return array|null
     */
    public function create($domain, $from, $to) {
        return $this->adapter->query("/CMD_API_REDIRECT", [
            "action" => "add",
            "domain" => $domain,
            "from"   => $from,
            "to"     => $to,
        ]);
    }
    
    /**
     * Deletes the given Redirect from the given domain. Requires user login
     * @param string $domain
     * @param string $from
     * @return array|null
     */
    public function delete($domain, $from) {
        return $this->adapter->query("/CMD_API_REDIRECT", [
            "action"  => "delete",
            "domain"  => $domain,
            "select0" => $from,
        ]);
    }
}
