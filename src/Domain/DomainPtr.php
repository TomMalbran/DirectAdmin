<?php
namespace DirectAdmin\Domain;

use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Server Domain Pointers
 */
class DomainPtr {
    
    private $adapter;
    
    /**
     * Creates a new DomainPtr instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns a list of Domain Pointers. Requires user login
     * @return array
     */
    public function getAll(): array {
        $result   = [];
        $response = $this->adapter->get("/CMD_API_DOMAIN_POINTER", [
            "domain" => $this->adapter->getDomain(),
        ]);
        
        foreach ($response->data as $from => $alias) {
            $result[] = [
                "name"    => str_replace("_", ".", $from),
                "isAlias" => $alias == "alias",
            ];
        }
        return $result;
    }
    
    
    
    /**
     * Creates a new Domain Pointer. Requires user login
     * @param string  $from
     * @param boolean $isAlias Optional.
     * @return Response
     */
    public function create(string $from, bool $isAlias = true): Response {
        $fields = [
            "action" => "add",
            "domain" => $this->adapter->getDomain(),
            "from"   => $from,
        ];
        if ($isAlias) {
            $fields["alias"] = "yes";
        }
        return $this->adapter->post("/CMD_API_DOMAIN_POINTER", $fields);
    }
    
    /**
     * Deletes the given Domain Pointer. Requires user login
     * @param string $from
     * @return Response
     */
    public function delete(string $from): Response {
        return $this->adapter->post("/CMD_API_DOMAIN_POINTER", [
            "action"  => "delete",
            "domain"  => $this->adapter->getDomain(),
            "select0" => $from,
        ]);
    }
}
