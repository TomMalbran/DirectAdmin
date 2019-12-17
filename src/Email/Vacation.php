<?php
namespace DirectAdmin\Email;

use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Email Vacations
 */
class Vacation {
    
    private $adapter;
    
    /**
     * Creates a new Vacation instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns a list with all the Vacations Messages for the given domain. Requires user login
     * @return array
     */
    public function getAll(): array {
        $index    = 0;
        $result   = [ "data" => [], "list" => [] ];
        $response = $this->adapter->query("/CMD_API_EMAIL_VACATION", [
            "domain" => $this->adapter->getDomain(),
        ]);
        
        foreach ($response->keys as $user) {
            $data = $this->adapter->query("/CMD_API_EMAIL_VACATION_MODIFY", [
                "domain" => $this->adapter->getDomain(),
                "user"   => $user,
            ]);
            
            $result["data"][$index] = [
                "index" => $index,
                "user"  => $user,
            ] + $data;
            $result["list"][] = $user;
            $index += 1;
        }
        return $result;
    }
    
    
    
    /**
     * Creates an Email Vacations Message. Requires user login
     * @param string  $user
     * @param string  $text
     * @param integer $fromTime
     * @param integer $toTime
     * @param boolean $isEdit
     * @return Response
     */
    public function create(string $user, string $text, int $fromTime, int $toTime, bool $isEdit): Response {
        $fields = $this->getFields($user, $text, $fromTime, $toTime);
        $fields["action"] = "create";
        return $this->adapter->query("/CMD_API_EMAIL_VACATION", $fields);
    }
    
    /**
     * Edits an Email Vacations Message. Requires user login
     * @param string  $user
     * @param string  $text
     * @param integer $fromTime
     * @param integer $toTime
     * @param boolean $isEdit
     * @return Response
     */
    public function edit(string $user, string $text, int $fromTime, int $toTime, bool $isEdit): Response {
        $fields = $this->getFields($user, $text, $fromTime, $toTime);
        $fields["action"] = "modify";
        return $this->adapter->query("/CMD_API_EMAIL_VACATION", $fields);
    }
    
    /**
     * Returns the fields to create or edit an Email Vacations Message
     * @param string  $user
     * @param string  $text
     * @param integer $fromTime
     * @param integer $toTime
     * @return array
     */
    private function getFields(string $user, string $text, int $fromTime, int $toTime): array {
        return [
            "domain"     => $this->adapter->getDomain(),
            "user"       => $user,
            "text"       => $text,
            "starttime"  => "morning",
            "startday"   => date("j", $fromTime),
            "startmonth" => date("n", $fromTime),
            "startyear"  => date("Y", $fromTime),
            "endtime"    => "evening",
            "endday"     => date("j", $toTime),
            "endmonth"   => date("n", $toTime),
            "endyear"    => date("Y", $toTime),
        ];
    }
    
    
    
    /**
     * Deletes the Vacations Message with the given user in the given domain. Requires user login
     * @param string $user
     * @return Response
     */
    public function delete(string $user): Response {
        return $this->adapter->query("/CMD_API_EMAIL_VACATION", [
            "action"  => "delete",
            "domain"  => $this->adapter->getDomain(),
            "select0" => $user,
        ]);
    }
}