<?php
namespace DirectAdmin\Reseller;

use DirectAdmin\Adapter;

/**
 * The Reseller Transfer
 */
class Transfer {
    
    private $adapter;
    
    /**
     * Creates a new Transfer instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    

    /**
     * Returns the list of Backups. Requires user login
     * @return string[]
     */
    public function getAll() {
        $request = $this->adapter->query("/CMD_API_ADMIN_BACKUP");
        if (!empty($request) && empty($request["error"]) && !empty($request["list"])) {
            return $request["list"];
        }
        return [];
    }
    
    

    /**
     * Creates a new Backup for the given domain. Requires reseller login
     * @param string $username
     * @return array|null
     */
    public function create($username) {
        return $this->adapter->query("/CMD_API_USER_BACKUP", [
            "action"  => "create",
            "who"     => "selected",
            "select0" => $username,
            "when"    => "now",
            "where"   => "local",
        ]);
    }
    
    /**
     * Restores the given Backup for the given domain. Requires reseller login
     * @param string $ipFrom
     * @param string $ipTo
     * @param string $reseller
     * @param string $password
     * @param string $username
     * @return array|null
     */
    public function restore($ipFrom, $ipTo, $reseller, $password, $username) {
        return $this->adapter->query("/CMD_API_USER_BACKUP", [
            "action"       => "restore",
            "where"        => "ftp",
            "ftp_ip"       => $ipFrom,
            "ftp_username" => $reseller,
            "ftp_password" => $password,
            "ftp_path"     => "/user_backups",
            "ftp_port"     => "21",
            "ip_choice"    => "file",
            "select0"      => $username . ".tar.gz",
            "ip_choice"    => "select",
            "ip"           => $ipTo,
        ]);
    }
}
