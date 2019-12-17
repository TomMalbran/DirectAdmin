<?php
namespace DirectAdmin\File;

use DirectAdmin\Adapter;

/**
 * The Server Directories
 */
class Directory {
    
    private $adapter;
    
    /**
     * Creates a new Directory instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns the protections for the given Directory. Requires user login
     * @param string $path
     * @param string $name
     * @return array
     */
    public function getProtections($path, $name) {
        $request = $this->adapter->query("/CMD_API_FILE_MANAGER", [
            "action" => "protect",
            "path"   => $path . "/" . $name,
        ]);
        
        if (!empty($request) && empty($request["error"])) {
            return [
                "text"      => $request["name"],
                "username"  => !empty($request[0]) ? $request[0] : "",
                "isEnabled" => $request["enabled"] == "yes",
            ];
        }
        return $request;
    }
    

    
    /**
     * Makes a new Directory. Requires user login
     * @param string $path
     * @param string $name
     * @return array|null
     */
    public function create($path, $name) {
        $fullPath = $this->adapter->getPublicPath($path);
        return $this->adapter->query("/CMD_API_FILE_MANAGER", [
            "action" => "folder",
            "path"   => $fullPath,
            "name"   => $name,
        ]);
    }

    /**
     * Protects a Directory. Requires user login
     * @param string  $path
     * @param string  $name
     * @param string  $text
     * @param string  $username
     * @param string  $password
     * @param boolean $isEnabled
     * @return array|null
     */
    public function protect($path, $name, $text, $username, $password, $isEnabled) {
        $fields = [
            "action"  => "protect",
            "path"    => $path . "/" . $name,
            "name"    => $text,
            "user"    => $username,
            "passwd"  => $password,
            "passwd2" => $password,
        ];
        if ($isEnabled) {
            $fields["enabled"] = "yes";
        }
        return $this->adapter->query("/CMD_API_FILE_MANAGER", $fields, "POST");
    }
    
    /**
     * Removes the protection from a Directory. Requires user login
     * @param string $path
     * @param string $name
     * @param string $username
     * @return array|null
     */
    public function unprotect($path, $name, $username) {
        $result1 = $this->adapter->query("/CMD_API_FILE_MANAGER", [
            "action"  => "delete",
            "path"    => $path . "/" . $name,
            "select0" => $username,
        ]);
        if (!empty($result1["error"])) {
            return $result1;
        }
        
        return $this->adapter->query("/CMD_API_FILE_MANAGER", [
            "action" => "protect",
            "path"   => $path . "/" . $name,
            "name"   => " ",
        ], "POST");
    }
}
