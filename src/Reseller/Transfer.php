<?php
namespace DirectAdmin\Reseller;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Reseller Transfer
 */
class Transfer extends Adapter {

    /**
     * Returns the list of Backups. Requires user login
     * @return string[]
     */
    public function getAll(): array {
        $response = $this->post(Context::User, "/CMD_API_ADMIN_BACKUP");
        return $response->list;
    }
    
    

    /**
     * Creates a new Backup for the given user. Requires reseller login
     * @param string $user
     * @return Response
     */
    public function create(string $user): Response {
        return $this->post(Context::Reseller, "/CMD_API_USER_BACKUP", [
            "action"  => "create",
            "who"     => "selected",
            "select0" => $user,
            "when"    => "now",
            "where"   => "local",
        ]);
    }
    
    /**
     * Restores the given Backup. Requires reseller login
     * @param string $ipFrom
     * @param string $ipTo
     * @param string $reseller
     * @param string $password
     * @param string $user
     * @return Response
     */
    public function restore(string $ipFrom, string $ipTo, string $reseller, string $password, string $user): Response {
        return $this->post(Context::Reseller, "/CMD_API_USER_BACKUP", [
            "action"       => "restore",
            "where"        => "ftp",
            "ftp_ip"       => $ipFrom,
            "ftp_username" => $reseller,
            "ftp_password" => $password,
            "ftp_path"     => "/user_backups",
            "ftp_port"     => "21",
            "ip_choice"    => "file",
            "select0"      => "{$user}.tar.gz",
            "ip_choice"    => "select",
            "ip"           => $ipTo,
        ]);
    }
}
