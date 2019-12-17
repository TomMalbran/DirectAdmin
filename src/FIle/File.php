<?php
namespace DirectAdmin\File;

use DirectAdmin\Adapter;

/**
 * The Server Files
 */
class File {
    
    private $adapter;
    
    /**
     * Creates a new File instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns the data for the files and a list of them. Requires user login
     * @param string $path
     * @return array
     */
    public function getAll($path) {
        $request = $this->adapter->query("/CMD_API_FILE_MANAGER", [ "path" => $path ]);
        $parent  = str_replace(".", "_", substr($path, 0, strrpos($path, "/")));
        $result  = [];
        
        if (!empty($request) && empty($request["error"])) {
            foreach ($request as $filePath => $fileData) {
                if (!empty($fileData) && is_string($fileData) && $filePath != $parent && $filePath != "/") {
                    parse_str($fileData, $x);
                    if (!empty($x)) {
                        $result[] = $x;
                    }
                }
            }
            return $result;
        }
        return $request;
    }
    
    /**
     * Returns true if the given File/Directory exists. Requires user login
     * @param string $path
     * @param string $name
     * @return boolean
     */
    public function exists($path, $name) {
        $result = $this->getSize($path, $name);
        return !empty($result["error"]);
    }

    /**
     * Returns the Size of a File/Directory. Requires user login
     * @param string $path
     * @param string $name
     * @return array|null
     */
    public function getSize($path, $name) {
        $fullPath = $this->adapter->getPublicPath($path);
        return $this->adapter->query("/CMD_API_FILE_MANAGER", [
            "action" => "filesize",
            "path"   => "$fullPath/$name",
        ]);
    }
    

    
    /**
     * Edits/Creates the given File. Requires user login
     * @param string $path
     * @param string $name
     * @param string $text
     * @return array|null
     */
    public function edit($path, $name, $text) {
        $fullPath = $this->adapter->getPublicPath($path);
        return $this->adapter->query("/CMD_API_FILE_MANAGER", [
            "action"   => "edit",
            "path"     => $fullPath,
            "text"     => $text,
            "filename" => $name,
        ], "POST");
    }
    
    /**
     * Uploads a File to the server creating an FTP user
     * @param string $path
     * @param string $fileName
     * @param string $filePath
     * @param string $username
     * @param string $password
     * @return string
     */
    public function upload($path, $fileName, $filePath, $username, $password) {
        return $this->adapter->postFileFTP($path, $fileName, $filePath, $username, $password);
    }

    /**
     * Uploads a File to the server creating an FTP user
     * @param string $domain
     * @param string $username
     * @param string $path
     * @param string $fileName
     * @param string $filePath
     * @param string $password
     * @return string
     */
    public function uploadFTP($domain, $username, $path, $fileName, $filePath, $password) {
        $ftp  = "raq";
        $ftps = $this->adapter->query("/CMD_API_FTP", [ "domain" => $domain ]);
        $pdom = "@" . str_replace(".", "_", $domain);
        
        $index  = 0;
        $fields = [];
        foreach (array_keys($ftps) as $name) {
            if (strpos($name, $pdom) !== FALSE) {
                $fields["select" . $index] = str_replace($pdom, "", $name);
            }
        }
        if (!empty($fields)) {
            $fields["action"] = "delete";
            $fields["domain"] = $domain;
            $this->adapter->query("/CMD_API_FTP", $fields);
        }
        
        $this->adapter->query("/CMD_API_FTP", [
            "action"     => "create",
            "domain"     => $domain,
            "user"       => $ftp,
            "type"       => "custom",
            "custom_val" => "/home/$username",
            "passwd"     => $password,
            "passwd2"    => $password,
        ]);
        $result = $this->adapter->postFileFTP($path, $fileName, $filePath, "$ftp@$domain", $password);
        
        $this->adapter->query("/CMD_API_FTP", [
            "action"  => "delete",
            "domain"  => $domain,
            "select0" => $ftp,
        ]);
        
        return $result;
    }
    
    /**
     * Returns the contents of the given File. Requires user login
     * @param string $path
     * @param string $file
     * @return string
     */
    public function download($path, $file) {
        return $this->adapter->query("/CMD_FILE_MANAGER", [
            "path" => "$path/$file",
        ], "GET", false);
    }
    
    /**
     * Extracts the given File. Requires user login
     * @param string $path
     * @param string $file
     * @return array|null
     */
    public function extract($path, $file) {
        $fullPath = $this->adapter->getPublicPath($path);
        return $this->adapter->query("/CMD_API_FILE_MANAGER", [
            "action"    => "extract",
            "path"      => "$fullPath/$file",
            "directory" => $fullPath,
            "page"      => 2,
        ]);
    }
    
    /**
     * Renames a File/Directory from the old name to the new one. Requires user login
     * @param string  $path
     * @param string  $oldName
     * @param string  $newName
     * @param boolean $overwrite Optional.
     * @return array|null
     */
    public function rename($path, $oldName, $newName, $overwrite = false) {
        $fullPath = $this->adapter->getPublicPath($path);
        return $this->adapter->query("/CMD_API_FILE_MANAGER", [
            "action"    => "rename",
            "path"      => $fullPath,
            "old"       => $oldName,
            "filename"  => $newName,
            "overwrite" => $overwrite ? "yes" : "no",
        ], "POST");
    }
    
    /**
     * Copies a File/Directory from the old name to the new one. Requires user login
     * @param string  $path
     * @param string  $oldName
     * @param string  $newName
     * @param boolean $overwrite Optional.
     * @return array|null
     */
    public function duplicate($path, $oldName, $newName, $overwrite = false) {
        $fullPath = $this->adapter->getPublicPath($path);
        return $this->adapter->query("/CMD_API_FILE_MANAGER", [
            "action"    => "copy",
            "path"      => $fullPath,
            "old"       => $oldName,
            "filename"  => $newName,
            "overwrite" => $overwrite ? "yes" : "no",
        ], "POST");
    }
    
    /**
     * Resets a File's/Directory's owner. Requires user login
     * @param string $path
     * @param string $file
     * @return array|null
     */
    public function resetOwner($path, $file) {
        $fullPath = $this->adapter->getPublicPath($path);
        return $this->adapter->query("/CMD_API_FILE_MANAGER", [
            "action" => "resetowner",
            "path"   => "$fullPath/$file",
        ]);
    }
    
    /**
     * Sets the Permissions for the given Files/Directories. Requires user login
     * @param string   $path
     * @param string   $chmod
     * @param string[] $files
     * @return array|null
     */
    public function setPermission($path, $chmod, array $files) {
        $fullPath = $this->adapter->getPublicPath($path);
        $fields   = [
            "action" => "multiple",
            "button" => "permission",
            "chmod"  => $chmod,
            "path"   => $path,
        ];
        foreach ($files as $index => $file) {
            $fields["select" . $index] = "$fullPath/$file";
        }
        return $this->adapter->query("/CMD_API_FILE_MANAGER", $fields);
    }
    
    /**
     * Moves the given Files/Directories from a path to another path. Requires user login
     * @param string   $fromPath
     * @param string   $toPath
     * @param string[] $files
     * @return array|null
     */
    public function move($fromPath, $toPath, array $files) {
        $result = $this->addToClipboard($fromPath, $files);
        if (empty($result["error"])) {
            $result = $this->doInClipboard("move", $toPath);
            $this->doInClipboard("empty");
        }
        return $result;
    }
    
    /**
     * Copies the given Files/Directories from a path to another path. Requires user login
     * @param string   $fromPath
     * @param string   $toPath
     * @param string[] $files
     * @return array|null
     */
    public function copy($fromPath, $toPath, array $files) {
        $result = $this->addToClipboard($fromPath, $files);
        if (empty($result["error"])) {
            $result = $this->doInClipboard("copy", $toPath);
            $this->doInClipboard("empty");
        }
        return $result;
    }
    
    /**
     * Compresses the given Files/Directories. Requires user login
     * @param string   $path
     * @param string   $name
     * @param string[] $files
     * @return array|null
     */
    public function compress($path, $name, array $files) {
        $result = $this->addToClipboard($path, $files);
        if (empty($result["error"])) {
            $result = $this->adapter->query("/CMD_API_FILE_MANAGER", [
                "action" => "compress",
                "path"   => $path,
                "file"   => $name,
            ]);
            $this->doInClipboard("empty");
        }
        return $result;
    }
    
    /**
     * Deletes the given Files/Directories. Requires user login
     * @param string   $path
     * @param string[] $files
     * @return array|null
     */
    public function delete($path, array $files) {
        $fullPath = $this->adapter->getPublicPath($path);
        $fields   = [
            "action" => "multiple",
            "button" => "delete",
            "path"   => $path,
        ];
        foreach ($files as $index => $file) {
            $fields["select" . $index] = "$fullPath/$file";
        }
        return $this->adapter->query("/CMD_API_FILE_MANAGER", $fields);
    }
    
    
    
    /**
     * Adds the given Files/Directories to the clipboard. Requires user login
     * @param string   $path
     * @param string[] $files
     * @return array|null
     */
    public function addToClipboard($path, array $files) {
        $fullPath = $this->adapter->getPublicPath($path);
        $fields   = [
            "action" => "multiple",
            "add"    => "clipboard",
            "path"   => $path,
        ];
        foreach ($files as $index => $file) {
            $fields["select" . $index] = "$fullPath/$file";
        }
        return $this->adapter->query("/CMD_API_FILE_MANAGER", $fields);
    }
    
    /**
     * Moves/Copies/Deletes the Clipboard Files/Directories. Requires user login
     * @param string $action
     * @param string $path
     * @return array|null
     */
    public function doInClipboard($action, $path = "") {
        $fullPath = $this->adapter->getPublicPath($path);
        return $this->adapter->query("/CMD_API_FILE_MANAGER", [
            "action" => "multiple",
            $action  => "clipboard",
            "path"   => $fullPath,
        ]);
    }
}
