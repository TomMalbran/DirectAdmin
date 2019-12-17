<?php
namespace DirectAdmin\User;

use DirectAdmin\Adapter;

/**
 * The User Databases
 */
class Database {
    
    private $adapter;
    
    /**
     * Creates a new Database instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns a list of Databases for the user. Requires user login
     * @return string[]
     */
    public function getAll() {
        $request = $this->adapter->query("/CMD_API_DATABASES");
        return (!empty($request) && empty($request["error"]) && !empty($request["list"])) ? $request["list"] : [];
    }
    
    /**
     * Returns a list of Databases for the user. Requires user login
     * @return array
     */
    public function getWithUsers() {
        $request = $this->adapter->query("/CMD_API_DATABASES");
        $result  = [
            "data"  => [],
            "names" => [],
            "users" => [],
        ];
        
        if (!empty($request) && empty($request["error"]) && !empty($request["list"])) {
            foreach ($request["list"] as $index => $name) {
                $users = $this->adapter->query("/CMD_API_DB_USER", [ "name" => $name ]);
                array_shift($users["list"]);
                
                $result["data"][$index] = [
                    "index" => $index,
                    "name"  => $name,
                    "users" => $users["list"],
                ];
                $result["names"][] = $name;
                $result["users"]   = array_merge($result["users"], $users["list"]);
            }
        }
        return $result;
    }
    
    
    
    /**
     * Creates a new Database with the given name and user. Requires user login
     * @param string $name
     * @param string $user
     * @param string $password
     * @return array|null
     */
    public function create($name, $user, $password) {
        return $this->adapter->query("/CMD_API_DATABASES", [
            "action"  => "create",
            "name"    => $name,
            "user"    => $user,
            "passwd"  => $password,
            "passwd2" => $password,
        ]);
    }
    
    /**
     * Deletes the Database with the given name. Requires user login
     * @param string $name
     * @return array|null
     */
    public function delete($name) {
        return $this->adapter->query("/CMD_API_DATABASES", [
            "action"  => "delete",
            "select0" => $name,
        ]);
    }
    
    
    
    /**
     * Creates a Database User. Requires user login
     * @param string $name
     * @param string $user
     * @param string $password
     * @return array|null
     */
    public function createUser($name, $user, $password) {
        return $this->adapter->query("/CMD_API_DB_USER", [
            "action"  => "create",
            "name"    => $name,
            "user"    => $user,
            "passwd"  => $password,
            "passwd2" => $password,
        ]);
    }
    
    /**
     * Edits a Database User. Requires user login
     * @param string $name
     * @param string $user
     * @param string $password
     * @return array|null
     */
    public function editUser($name, $user, $password) {
        return $this->adapter->query("/CMD_API_DB_USER", [
            "action"  => "modify",
            "name"    => $name,
            "user"    => $user,
            "passwd"  => $password,
            "passwd2" => $password,
        ]);
    }
    
    /**
     * Deletes the given Database User. Requires user login
     * @param string $name
     * @param string $user
     * @return array|null
     */
    public function deleteUser($name, $user) {
        return $this->adapter->query("/CMD_API_DB_USER", [
            "action"  => "delete",
            "name"    => $name,
            "select0" => $user,
        ]);
    }
    
    
    
    /**
     * Creates a new Access Host. Requires user login
     * @param string $db
     * @param string $host
     * @return array|null
     */
    public function createHost($db, $host) {
        return $this->adapter->query("/CMD_API_DATABASES", [
            "action" => "accesshosts",
            "create" => "yes",
            "db"     => $db,
            "host"   => $host,
        ]);
    }
    
    /**
     * Creates a new Access Host. Requires user login
     * @param string $db
     * @param string $host
     * @return array|null
     */
    public function deleteHost($db, $host) {
        return $this->adapter->query("/CMD_API_DATABASES", [
            "action"  => "accesshosts",
            "delete"  => "yes",
            "db"      => $db,
            "select0" => $host,
        ]);
    }
}
