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
     * @param string  $path
     * @param boolean $inPublic Optional.
     * @return array{}[]
     */
    public function getAll(string $path, bool $inPublic = true): array {
        $fullPath = $this->context->getPublicPath($path, $inPublic);
        $response = $this->get(Context::User, "/CMD_API_FILE_MANAGER", [ "path" => $fullPath ]);
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
     * @param string  $path
     * @param string  $name
     * @param boolean $inPublic Optional.
     * @return boolean
     */
    public function exists(string $path, string $name, bool $inPublic = true): bool {
        $response = $this->getSize($path, $name, $inPublic);
        return !$response->hasError;
    }

    /**
     * Returns the Size of a File/Directory. Requires user login
     * @param string  $path
     * @param string  $name
     * @param boolean $inPublic Optional.
     * @return Response
     */
    public function getSize(string $path, string $name, bool $inPublic = true): Response {
        $fullPath = $this->context->getPublicPath($path, $inPublic);
        return $this->get(Context::User, "/CMD_API_FILE_MANAGER", [
            "action" => "filesize",
            "path"   => "$fullPath/$name",
        ]);
    }

    /**
     * Returns the Contents of the given File. Requires user login
     * @param string  $path
     * @param string  $name
     * @param boolean $inPublic Optional.
     * @return string
     */
    public function getContents(string $path, string $name, bool $inPublic = true): string {
        $fullPath = $this->context->getPublicPath($path, $inPublic);
        $response = $this->get(Context::User, "/CMD_FILE_MANAGER", [
            "action" => "edit",
            "path"   => "$fullPath/$name",
            "json"   => "yes",
        ], true);

        if (!$response->hasError) {
            return $response->data["TEXT"];
        }
        return "";
    }



    /**
     * Edits/Creates the given File. Requires user login
     * @param string          $path
     * @param string          $name
     * @param string|string[] $content
     * @param boolean         $inPublic Optional.
     * @return Response
     */
    public function edit(string $path, string $name, array|string $content, bool $inPublic = true): Response {
        $fullPath = $this->context->getPublicPath($path, $inPublic);
        $text     = is_array($content) ? implode("\n", $content) : $content;
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", [
            "action"   => "edit",
            "path"     => $fullPath,
            "text"     => $text,
            "filename" => $name,
        ]);
    }

    /**
     * Uploads a File to the Server. Requires user login
     * @param string $path
     * @param string $fileName
     * @param string $filePath
     * @param string $password
     * @return Response
     */
    public function upload(string $path, string $fileName, string $filePath, string $password): Response {
        $username = $this->context->user;
        return $this->uploadFile($path, $fileName, $filePath, $username, $password);
    }

    /**
     * Uploads a File to the Server creating an FTP user. Requires user login
     * @param string $path
     * @param string $fileName
     * @param string $filePath
     * @param string $password
     * @return Response
     */
    public function uploadFTP(string $path, string $fileName, string $filePath, string $password): Response {
        $ftp      = "ftp";
        $username = $this->context->user;
        $domain   = $this->context->domain;
        $response = $this->get(Context::User, "/CMD_API_FTP");
        $atDomain = "@" . str_replace(".", "_", $domain);

        if ($response->hasError) {
            return "";
        }

        $index  = 0;
        $fields = [];
        foreach ($response->keys as $name) {
            if (strpos($name, $atDomain) !== FALSE) {
                $fields["select$index"] = str_replace($atDomain, "", $name);
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
     * Renames a File/Directory from the old name to the new one. Requires user login
     * @param string  $path
     * @param string  $oldName
     * @param string  $newName
     * @param boolean $overwrite Optional.
     * @param boolean $inPublic  Optional.
     * @return Response
     */
    public function rename(string $path, string $oldName, string $newName, bool $overwrite = false, bool $inPublic = true): Response {
        $fullPath = $this->context->getPublicPath($path, $inPublic);
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
     * @param boolean $inPublic  Optional.
     * @return Response
     */
    public function duplicate(string $path, string $oldName, string $newName, bool $overwrite = false, bool $inPublic = true): Response {
        $fullPath = $this->context->getPublicPath($path, $inPublic);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", [
            "action"    => "copy",
            "path"      => $fullPath,
            "old"       => $oldName,
            "filename"  => $newName,
            "overwrite" => $overwrite ? "yes" : "no",
        ]);
    }

    /**
     * Extracts the given File. Requires user login
     * @param string  $path
     * @param string  $name
     * @param boolean $inPublic Optional.
     * @return Response
     */
    public function extract(string $path, string $name, bool $inPublic = true): Response {
        $fullPath = $this->context->getPublicPath($path, $inPublic);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", [
            "action"    => "extract",
            "path"      => "$fullPath/$name",
            "directory" => $fullPath,
            "page"      => 2,
        ]);
    }

    /**
     * Resets a File/Directory Owner. Requires user login
     * @param string  $path
     * @param string  $name
     * @param boolean $inPublic Optional.
     * @return Response
     */
    public function resetOwner(string $path, string $name, bool $inPublic = true): Response {
        $fullPath = $this->context->getPublicPath($path, $inPublic);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", [
            "action" => "resetowner",
            "path"   => "$fullPath/$name",
        ]);
    }

    /**
     * Sets the Permissions for the given Files/Directories. Requires user login
     * @param string          $path
     * @param string          $chmod
     * @param string[]|string $files
     * @param boolean         $inPublic Optional.
     * @return Response
     */
    public function setPermission(string $path, string $chmod, array|string $files, bool $inPublic = true): Response {
        $fields = $this->createFields([
            "button" => "permission",
            "chmod"  => $chmod,
        ], $path, $files, $inPublic);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", $fields);
    }

    /**
     * Moves the given Files/Directories from a path to another path. Requires user login
     * @param string          $fromPath
     * @param string          $toPath
     * @param string[]|string $files
     * @param boolean         $inPublic Optional.
     * @return Response
     */
    public function move(string $fromPath, string $toPath, array|string $files, bool $inPublic = true): Response {
        $response = $this->addToClipboard($fromPath, $files, $inPublic);
        if (!$response->hasError) {
            $this->doInClipboard("move", $toPath, $inPublic);
            return $this->doInClipboard("empty");
        }
        return $response;
    }

    /**
     * Copies the given Files/Directories from a path to another path. Requires user login
     * @param string          $fromPath
     * @param string          $toPath
     * @param string[]|string $files
     * @param boolean         $inPublic Optional.
     * @return Response
     */
    public function copy(string $fromPath, string $toPath, array|string $files, bool $inPublic = true): Response {
        $response = $this->addToClipboard($fromPath, $files, $inPublic);
        if (!$response->hasError) {
            $this->doInClipboard("copy", $toPath, $inPublic);
            return $this->doInClipboard("empty");
        }
        return $response;
    }

    /**
     * Compresses the given Files/Directories. Requires user login
     * @param string          $path
     * @param string          $name
     * @param string[]|string $files
     * @param boolean         $inPublic Optional.
     * @return Response
     */
    public function compress(string $path, string $name, array|string $files, bool $inPublic = true): Response {
        $response = $this->addToClipboard($path, $files, $inPublic);
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
     * @param boolean         $inPublic Optional.
     * @return Response
     */
    public function delete(string $path, array|string $files, bool $inPublic = true): Response {
        $fields = $this->createFields([
            "button" => "delete",
        ], $path, $files, $inPublic);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", $fields);
    }



    /**
     * Adds the given Files/Directories to the clipboard. Requires user login
     * @param string          $path
     * @param string[]|string $files
     * @param boolean         $inPublic Optional.
     * @return Response
     */
    public function addToClipboard(string $path, array|string $files, bool $inPublic = true): Response {
        $fields = $this->createFields([
            "add" => "clipboard",
        ], $path, $files, $inPublic);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", $fields);
    }

    /**
     * Moves/Copies/Deletes the Clipboard Files/Directories. Requires user login
     * @param string  $action
     * @param string  $path     Optional.
     * @param boolean $inPublic Optional.
     * @return Response
     */
    public function doInClipboard(string $action, string $path = "", bool $inPublic = true): Response {
        $fields = $this->createFields([ $action => "clipboard" ], $path, null, $inPublic);
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", $fields);
    }



    /**
     * Returns the Fields for multiple Files
     * @param array{}              $fields
     * @param string               $path
     * @param string[]|string|null $file     Optional.
     * @param boolean              $inPublic Optional.
     * @return array{}
     */
    private function createFields(array $fields, string $path, array|string|null $file = null, bool $inPublic = true): array {
        $fullPath = $this->context->getPublicPath($path, $inPublic);
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
