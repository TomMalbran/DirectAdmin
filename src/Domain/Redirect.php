<?php
namespace DirectAdmin\Domain;

use DirectAdmin\Adapter;
use DirectAdmin\Response;

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
     * Returns a list of Redirects. Requires user login
     * @return array
     */
    public function getAll(): array {
        $result   = [];
        $response = $this->adapter->get("/CMD_API_REDIRECT", [
            "domain" => $this->adapter->getDomain(),
        ]);
        
        foreach ($response->data as $from => $to) {
            $result[] = [
                "from" => $from,
                "to"   => $to,
            ];
        }
        return $result;
    }
    
    
    
    /**
     * Adds a new Redirect. Requires user login
     * @param string $from
     * @param string $to
     * @return Response
     */
    public function create(string $from, string $to): Response {
        return $this->adapter->post("/CMD_API_REDIRECT", [
            "action" => "add",
            "domain" => $this->adapter->getDomain(),
            "from"   => $from,
            "to"     => $to,
        ]);
    }
    
    /**
     * Deletes the given Redirect. Requires user login
     * @param string $from
     * @return Response
     */
    public function delete(string $from): Response {
        return $this->adapter->post("/CMD_API_REDIRECT", [
            "action"  => "delete",
            "domain"  => $this->adapter->getDomain(),
            "select0" => $from,
        ]);
    }
}
