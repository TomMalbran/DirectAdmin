<?php
namespace DirectAdmin\File;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Server Directories
 */
class Directory extends Adapter {
    
    /**
     * Returns the protections for the given Directory. Requires user login
     * @param string $path
     * @param string $name
     * @return Response
     */
    public function getProtections(string $path, string $name): Response {
        $fullPath = $this->context->getPublicPath($path);
        $response = $this->get(Context::User, "/CMD_API_FILE_MANAGER", [
            "action" => "protect",
            "path"   => "$fullPath/$name",
        ]);
        
        if (!$response->error) {
            return new Response([
                "text"      => $response->data["name"],
                "username"  => !empty($response->data[0]) ? $response->data[0] : "",
                "isEnabled" => $response->data["enabled"] == "yes",
            ]);
        }
        return $response;
    }
    

    
    /**
     * Makes a new Directory. Requires user login
     * @param string $path
     * @param string $name
     * @return Response
     */
    public function create(string $path, string $name): Response {
        $fullPath = $this->context->getPublicPath($path);
        return $this->get(Context::User, "/CMD_API_FILE_MANAGER", [
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
     * @return Response
     */
    public function protect(string $path, string $name, string $text, string $username, string $password, bool $isEnabled): Response {
        $fullPath = $this->context->getPublicPath($path);
        $fields   = [
            "action"  => "protect",
            "path"    => "$fullPath/$name",
            "name"    => $text,
            "user"    => $username,
            "passwd"  => $password,
            "passwd2" => $password,
        ];
        if ($isEnabled) {
            $fields["enabled"] = "yes";
        }
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", $fields);
    }
    
    /**
     * Removes the protection from a Directory. Requires user login
     * @param string $path
     * @param string $name
     * @param string $username
     * @return Response
     */
    public function unprotect(string $path, string $name, string $username): Response {
        $fullPath = $this->context->getPublicPath($path);
        $response = $this->get(Context::User, "/CMD_API_FILE_MANAGER", [
            "action"  => "delete",
            "path"    => "$fullPath/$name",
            "select0" => $username,
        ]);
        if ($response->hasError) {
            return $response;
        }
        
        return $this->post(Context::User, "/CMD_API_FILE_MANAGER", [
            "action" => "protect",
            "path"   => "$fullPath/$name",
            "name"   => " ",
        ]);
    }
}
