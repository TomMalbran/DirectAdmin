<?php
namespace DirectAdmin\User;

use DirectAdmin\Adapter;

/**
 * The User FTP Accounts
 */
class FTPAccount {
    
    private $adapter;
    
    /**
     * Creates a new FTPAccount instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns a list of FTP Accounts for the given domain. Requires user login
     * @param string $domain
     * @return array
     */
    public function getAll($domain) {
        $request = $this->adapter->query("/CMD_API_FTP", [ "domain" => $domain ]);
        $result  = [];
        $index   = 0;
        
        if (!empty($request) && empty($request["error"])) {
            foreach (array_keys($request) as $account) {
                $account = str_replace("_", ".", $account);
                $user    = str_replace("@" . $domain, "", $account);
                $data    = $this->adapter->query("/CMD_API_FTP_SHOW", [ "domain" => $domain, "user" => $user ]);
                
                $result[$index] = [
                    "index"   => $index,
                    "account" => $data["fulluser"],
                    "user"    => $data["user"],
                    "type"    => $data["type"],
                    "path"    => $data["path"],
                    "isMain"  => $data["fulluser"] == $data["user"],
                ];
                $index += 1;
            }
        }
        return $result;
    }
    
    /**
     * Returns a list of FTP Accounts for the given domain. Requires user login
     * @param string $domain
     * @return array
     */
    public function getList($domain) {
        $request = $this->adapter->query("/CMD_API_FTP", [ "domain" => $domain ]);
        $result  = [];
        
        if (!empty($request) && empty($request["error"])) {
            foreach (array_keys($request) as $account) {
                $account  = str_replace("_", ".", $account);
                if (strpos($account, "@" . $domain) !== FALSE) {
                    $result[] = str_replace("@" . $domain, "", $account);
                }
            }
        }
        return $result;
    }
    
    
    
    /**
     * Creates an FTP Account for the given domain. Requires user login
     * @param string $domain
     * @param string $user
     * @param string $type
     * @param string $password Optional.
     * @param string $path     Optional.
     * @return array|null
     */
    public function create($domain, $user, $type, $password = "", $path = "") {
        $fields = $this->getFields($domain, $user, $type, $password, $path);
        $fields["action"] = "create";
        return $this->adapter->query("/CMD_API_FTP", $fields);
    }
    
    /**
     * Edits an FTP Account for the given domain. Requires user login
     * @param string $domain
     * @param string $user
     * @param string $type
     * @param string $password Optional.
     * @param string $path     Optional.
     * @return array|null
     */
    public function edit($domain, $user, $type, $password = "", $path = "") {
        $fields = $this->getFields($domain, $user, $type, $password, $path);
        $fields["action"] = "modify";
        return $this->adapter->query("/CMD_API_FTP", $fields);
    }
    
    /**
     * Returns the fields to create or edit an FTP Account for the given domain
     * @param string $domain
     * @param string $user
     * @param string $type
     * @param string $password Optional.
     * @param string $path     Optional.
     * @return array
     */
    private function getFields($domain, $user, $type, $password = "", $path = "") {
        $fields = [
            "domain"  => $domain,
            "user"    => $user,
            "type"    => $type,
            "passwd"  => $password,
            "passwd2" => $password,
        ];
        if (!empty($path)) {
            $fields["custom_val"] = $path;
        }
        return $fields;
    }
    
    
    
    /**
     * Deletes the given FTP Account from the given domain. Requires user login
     * @param string $domain
     * @param string $user
     * @return array|null
     */
    public function delete($domain, $user) {
        return $this->adapter->query("/CMD_API_FTP", [
            "action"  => "delete",
            "domain"  => $domain,
            "select0" => $user,
        ]);
    }
}
