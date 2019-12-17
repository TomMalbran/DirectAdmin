<?php
namespace DirectAdmin\Email;

use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Email Responders
 */
class Responder {
    
    private $adapter;
    
    /**
     * Creates a new Responder instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns a list with all the Email Autoresponders for the given domain. Requires user login
     * @return array
     */
    public function getAll(): array {
        $index    = 0;
        $result   = [ "data" => [], "list" => [] ];
        $response = $this->adapter->query("/CMD_API_EMAIL_AUTORESPONDER", [
            "domain" => $this->adapter->getDomain(),
        ]);
        
        foreach ($response->data as $user => $cc) {
            $data = $this->adapter->query("/CMD_API_EMAIL_AUTORESPONDER_MODIFY", [
                "domain" => $this->adapter->getDomain(),
                "user"   => $user,
            ]);
            
            $result["data"][$index] = [
                "index" => $index,
                "user"  => $user,
                "cc"    => $cc,
                "text"  => $data["text"],
            ];
            $result["list"][] = $user;
            $index += 1;
        }
        return $result;
    }
    
    
    
    /**
     * Creates an Email Autoresponder. Requires user login
     * @param string $user
     * @param string $text
     * @param string $cc   Optional.
     * @return Response
     */
    public function create(string $user, string $text, string $cc = null): Response {
        $fields = $this->getFields($user, $text, $cc);
        $fields["action"] = "create";
        return $this->adapter->query("/CMD_API_EMAIL_AUTORESPONDER", $fields);
    }
    
    /**
     * Edits an Email Autoresponder. Requires user login
     * @param string $user
     * @param string $text
     * @param string $cc   Optional.
     * @return Response
     */
    public function edit(string $user, string $text, string $cc = null): Response {
        $fields = $this->getFields($domain, $user, $text, $cc);
        $fields["action"] = "modify";
        return $this->adapter->query("/CMD_API_EMAIL_AUTORESPONDER", $fields);
    }
    
    /**
     * Returns the fields to create or edit an Email Autoresponder
     * @param string $user
     * @param string $text
     * @param string $cc   Optional.
     * @return array
     */
    private function getFields(string $user, string $text, string $cc = null): array {
        $fields = [
            "domain" => $this->adapter->getDomain(),
            "user"   => $user,
            "text"   => $text,
        ];
        if (!empty($cc)) {
            $fields += [
                "cc"    => "ON",
                "email" => $cc,
            ];
        }
        return $fields;
    }
    
    
    
    /**
     * Deletes the Email Autoresponder with the given user in the given domain. Requires user login
     * @param string $user
     * @return Response
     */
    public function delete(string $user): Response {
        return $this->adapter->query("/CMD_API_EMAIL_AUTORESPONDER", [
            "action"  => "delete",
            "domain"  => $this->adapter->getDomain(),
            "select0" => $user,
        ]);
    }
}