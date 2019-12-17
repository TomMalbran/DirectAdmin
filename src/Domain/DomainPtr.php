<?php
namespace DirectAdmin\Domain;

use DirectAdmin\Adapter;

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
     * Returns a list of Domain Pointers for the given domain. Requires user login
     * @param string $domain
     * @return array
     */
    public function getAll($domain) {
        $request = $this->adapter->query("/CMD_API_DOMAIN_POINTER", [ "domain" => $domain ]);
        $result  = [];
        
        if (!empty($request) && empty($request["error"])) {
            foreach ($request as $from => $alias) {
                $result[] = [
                    "name"    => str_replace("_", ".", $from),
                    "isAlias" => $alias == "alias",
                ];
            }
        }
        return $result;
    }
    
    
    
    /**
     * Creates a new Domain Pointer for the given domain. Requires user login
     * @param string  $domain
     * @param string  $from
     * @param boolean $isAlias Optional.
     * @return array|null
     */
    public function create($domain, $from, $isAlias = true) {
        $fields = [
            "action" => "add",
            "domain" => $domain,
            "from"   => $from,
        ];
        if ($isAlias) {
            $fields["alias"] = "yes";
        }
        return $this->adapter->query("/CMD_API_DOMAIN_POINTER", $fields);
    }
    
    /**
     * Deletes the given Domain Pointer from the given domain. Requires user login
     * @param string $domain
     * @param string $from
     * @return array|null
     */
    public function delete($domain, $from) {
        return $this->adapter->query("/CMD_API_DOMAIN_POINTER", [
            "action"  => "delete",
            "domain"  => $domain,
            "select0" => $from,
        ]);
    }
}
