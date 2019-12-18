<?php
namespace DirectAdmin\User;

use DirectAdmin\Adapter;
use DirectAdmin\Response;

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
     * @return string[]
     */
    public function getAll(): array {
        $response = $this->adapter->get("/CMD_API_SITE_BACKUP", [
            "domain" => $this->adapter->getDomain(),
        ]);
        return $response->list;
    }
    
    

    /**
     * Creates a new Backup. Requires user login
     * @return Response
     */
    public function create(): Response {
        return $this->adapter->post("/CMD_API_SITE_BACKUP", [
            "action"          => "backup",
            "domain"          => $this->adapter->getDomain(),
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
     * Restores the given Backup. Requires user login
     * @param string $name
     * @return Response
     */
    public function restore(string $name): Response {
        $response = $this->adapter->get("/CMD_API_SITE_BACKUP", [
            "action" => "view",
            "domain" => $this->adapter->getDomain(),
            "file"   => $name,
        ]);
        if ($response->hasError) {
            return $response;
        }

        $fields = [
            "action" => "restore",
            "domain" => $domain,
            "file"   => $name,
        ];
        foreach ($response->data as $index => $value) {
            $fields["select$index"] = $value;
        }
        return $this->adapter->post("/CMD_API_SITE_BACKUP", $fields);
    }
}
