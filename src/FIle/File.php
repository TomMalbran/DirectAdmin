<?php
namespace DirectAdmin\File;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Server Files
 */
class File extends Adapter {
    
    /**
     * Returns the data for the files and a list of them. Requires user login
     * @param string $path
     * @return array
     */
    public function getAll(string $path): array {
        $response = $this->get(Context::User, "/CMD_API_FILE_MANAGER", [ "path" => $path ]);
        $parent   = str_replace(".", "_", substr($path, 0, strrpos($path, "/")));
        $result   = [];
        
        foreach ($response->data as $filePath => $fileData) {
            if ($filePath != $parent && $filePath != "/") {
                $result[] = $fileData;
            }
        }
        return $result;
    }
    
    /**
     * Returns true if the given File/Directory exists. Requires user login
     * @param string $path
     * @param string $name
     * @return boolean
     */
    public function exists(string $path, string $name): bool {
        $response = $this->getSize($path, $name);
        return !$response->hasError;
    }

    /**
     * Returns the Size of a File/Directory. Requires user login
     * @param string $path
     * @param string $name
     * @return Response
     */
    public function getSize(string $path, string $name): Response {
        $fullPath = $this->context->getPublicPath($path);
        return $this->get(Context::User, "/CMD_API_FILE_MANAGER", [
            "action" => "filesize",
            "path"   => "$fullPath/$name",
        ]);
    }
    

    
    /**
     * Edits/Creates the given File. Requires user login
     * @param string $path
     * @param string $name
     * @param string $text
     * @return Response
     */
    public function edit(string $path, string $name, string $text): Response {
        $fullPath = $this->context->getPublicPath($path);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", [
            "action"   => "edit",
            "path"     => $fullPath,
            "text"     => $text,
            "filename" => $name,
        ]);
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
    public function upload(string $path, string $fileName, string $filePath, string $username, string $password): string {
        return $this->uploadFile($path, $fileName, $filePath, $username, $password);
    }

    /**
     * Uploads a File to the server creating an FTP user
     * @param string $username
     * @param string $path
     * @param string $fileName
     * @param string $filePath
     * @param string $password
     * @return string
     */
    public function uploadFTP(string $username, string $path, string $fileName, string $filePath, string $password): string {
        $ftp      = "kappa";
        $domain   = $this->context->domain;
        $response = $this->get(Context::User, "/CMD_API_FTP");
        $pdom     = "@" . str_replace(".", "_", $domain);
        
        if ($response->hasError) {
            return "";
        }
        
        $index  = 0;
        $fields = [];
        foreach ($response->keys as $name) {
            if (strpos($name, $pdom) !== FALSE) {
                $fields["select$index"] = str_replace($pdom, "", $name);
                $index += 1;
            }
        }
        if (!empty($fields)) {
            $fields["action"] = "delete";
            $this->post(Context::User, "/CMD_API_FTP", $fields);
        }
        
        $this->post(Context::User, "/CMD_API_FTP", [
            "action"     => "create",
            "user"       => $ftp,
            "type"       => "custom",
            "custom_val" => "/home/$username",
            "passwd"     => $password,
            "passwd2"    => $password,
        ]);
        $result = $this->uploadFile($path, $fileName, $filePath, "$ftp@$domain", $password);
        
        $this->post(Context::User, "/CMD_API_FTP", [
            "action"  => "delete",
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
    public function download(string $path, string $file): string {
        $response = $this->get(Context::User, "/CMD_FILE_MANAGER", [
            "path" => "$path/$file",
        ]);
        return $response->raw;
    }
    
    /**
     * Extracts the given File. Requires user login
     * @param string $path
     * @param string $file
     * @return Response
     */
    public function extract(string $path, string $file): Response {
        $fullPath = $this->context->getPublicPath($path);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", [
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
     * @return Response
     */
    public function rename(string $path, string $oldName, string $newName, bool $overwrite = false): Response {
        $fullPath = $this->context->getPublicPath($path);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", [
            "action"    => "rename",
            "path"      => $fullPath,
            "old"       => $oldName,
            "filename"  => $newName,
            "overwrite" => $overwrite ? "yes" : "no",
        ]);
    }
    
    /**
     * Copies a File/Directory from the old name to the new one. Requires user login
     * @param string  $path
     * @param string  $oldName
     * @param string  $newName
     * @param boolean $overwrite Optional.
     * @return Response
     */
    public function duplicate(string $path, string $oldName, string $newName, bool $overwrite = false): Response {
        $fullPath = $this->context->getPublicPath($path);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", [
            "action"    => "copy",
            "path"      => $fullPath,
            "old"       => $oldName,
            "filename"  => $newName,
            "overwrite" => $overwrite ? "yes" : "no",
        ]);
    }
    
    /**
     * Resets a File's/Directory's owner. Requires user login
     * @param string $path
     * @param string $file
     * @return Response
     */
    public function resetOwner(string $path, string $file): Response {
        $fullPath = $this->context->getPublicPath($path);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", [
            "action" => "resetowner",
            "path"   => "$fullPath/$file",
        ]);
    }
    
    /**
     * Sets the Permissions for the given Files/Directories. Requires user login
     * @param string          $path
     * @param string          $chmod
     * @param string[]|string $files
     * @return Response
     */
    public function setPermission(string $path, string $chmod, $files): Response {
        $fields = $this->createFields([
            "button" => "permission",
            "chmod"  => $chmod,
        ], $path, $files);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", $fields);
    }
    
    /**
     * Moves the given Files/Directories from a path to another path. Requires user login
     * @param string          $fromPath
     * @param string          $toPath
     * @param string[]|string $files
     * @return Response
     */
    public function move(string $fromPath, string $toPath, $files): Response {
        $response = $this->addToClipboard($fromPath, $files);
        if (!$response->hasError) {
            $this->doInClipboard("move", $toPath);
            return $this->doInClipboard("empty");
        }
        return $response;
    }
    
    /**
     * Copies the given Files/Directories from a path to another path. Requires user login
     * @param string          $fromPath
     * @param string          $toPath
     * @param string[]|string $files
     * @return Response
     */
    public function copy(string $fromPath, string $toPath, $files): Response {
        $response = $this->addToClipboard($fromPath, $files);
        if (!$response->hasError) {
            $this->doInClipboard("copy", $toPath);
            return $this->doInClipboard("empty");
        }
        return $response;
    }
    
    /**
     * Compresses the given Files/Directories. Requires user login
     * @param string          $path
     * @param string          $name
     * @param string[]|string $files
     * @return Response
     */
    public function compress(string $path, string $name, $files): Response {
        $response = $this->addToClipboard($path, $files);
        if (!$response->hasError) {
            $response = $this->post(Context::User, "/CMD_API_FILE_MANAGER", [
                "action" => "compress",
                "path"   => $path,
                "file"   => $name,
            ]);
            return $this->doInClipboard("empty");
        }
        return $response;
    }
    
    /**
     * Deletes the given Files/Directories. Requires user login
     * @param string          $path
     * @param string[]|string $files
     * @return Response
     */
    public function delete(string $path, $files): Response {
        $fields = $this->createFields([
            "button" => "delete",
            "chmod"  => $chmod,
        ], $path, $files);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", $fields);
    }
    
    
    
    /**
     * Adds the given Files/Directories to the clipboard. Requires user login
     * @param string          $path
     * @param string[]|string $files
     * @return Response
     */
    public function addToClipboard(string $path, $files): Response {
        $fields = $this->createFields([
            "add"   => "clipboard",
            "chmod" => $chmod,
        ], $path, $files);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", $fields);
    }
    
    /**
     * Moves/Copies/Deletes the Clipboard Files/Directories. Requires user login
     * @param string $action
     * @param string $path   Optional.
     * @return Response
     */
    public function doInClipboard(string $action, string $path = ""): Response {
        $fields = $this->createFields([ $action => "clipboard" ], $path);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", $fields);
    }



    /**
     * Returns the Fields for multiple Files
     * @param array           $fields
     * @param string          $path
     * @param string[]|string $file   Optional.
     * @return array
     */
    private function createFields(array $fields, string $path, $file = null): array {
        $fullPath = $this->context->getPublicPath($path);
        $files    = !is_array($file) ? [ $file ] : $file;

        $fields["action"] = "multiple";
        $fields["path"]   = $fullPath;
        
        $index = 0;
        foreach ($files as $file) {
            if (!empty($file)) {
                $fields["select$index"] = "$fullPath/$file";
                $index += 1;
            }
        }
        return $fields;
    }
}
