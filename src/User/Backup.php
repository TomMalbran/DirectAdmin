<?php
namespace DirectAdmin\User;

use DirectAdmin\Adapter;

/**
 * The User Backups
 */
class Backup {
    
    private $adapter;
    
    /**
     * Creates a new Backup instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    

    /**
     * Returns the list of Backups. Requires user login
     * @param string $domain
     * @return string[]
     */
    public function getAll($domain) {
        $request = $this->adapter->query("/CMD_API_SITE_BACKUP", [ "domain" => $domain ]);
        return $this->adapter->getListResult($request);
    }
    
    

    /**
     * Creates a new Backup for the given domain. Requires user login
     * @param string $domain
     * @return array|null
     */
    public function create($domain) {
        return $this->adapter->query("/CMD_API_SITE_BACKUP", [
            "action"          => "backup",
            "domain"          => $domain,
            "select0"         => "domain",
            "select1"         => "subdomain",
            "select2"         => "email",
            "select3"         => "forwarder",
            "select4"         => "autoresponder",
            "select5"         => "vacation",
            "select6"         => "list",
            "select7"         => "emailsettings",
            "select8"         => "ftp",
            "select9"         => "ftpsettings",
            "select10"        => "database",
            "supress_message" => 1,
        ]);
    }
    
    /**
     * Restores the given Backup for the given domain. Requires user login
     * @param string $domain
     * @param string $name
     * @return array|null
     */
    public function restore($domain, $name) {
        $data = $this->adapter->query("/CMD_API_SITE_BACKUP", [
            "action" => "view",
            "domain" => $domain,
            "file"   => $name,
        ]);
        
        if (!empty($data["error"])) {
            $fields = [
                "action" => "restore",
                "domain" => $domain,
                "file"   => $name,
            ];
            foreach ($data as $index => $select) {
                $fields["select" . $index] = $select;
            }
            return $this->adapter->query("/CMD_API_SITE_BACKUP", $fields);
        }
        return $data;
    }
}
