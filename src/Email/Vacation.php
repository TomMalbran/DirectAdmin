<?php
namespace DirectAdmin\Email;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Email Vacations
 */
class Vacation extends Adapter {
    
    /**
     * Returns a list with all the Vacations Messages. Requires user login
     * @return array
     */
    public function getAll(): array {
        $response = $this->get(Context::User, "/CMD_API_EMAIL_VACATION");
        $result   = [];
        $index    = 0;

        foreach ($response->keys as $user) {
            $data = $this->get(Context::User, "/CMD_API_EMAIL_VACATION_MODIFY", [
                "user" => $user,
            ]);
            
            $result[] = [
                "index" => $index,
                "user"  => $user,
            ] + $data->data;
            $index += 1;
        }
        return $result;
    }

    /**
     * Returns a list with all the Vacations Messages usernames. Requires user login
     * @return string[]
     */
    public function getUsers(): array {
        $response = $this->get(Context::User, "/CMD_API_EMAIL_VACATION");
        return $response->keys;
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
        $fields = $this->createFields("create", $user, $text, $fromTime, $toTime);
        return $this->post(Context::User, "/CMD_API_EMAIL_VACATION", $fields);
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
        $fields = $this->getFields("modify", $user, $text, $fromTime, $toTime);
        return $this->post(Context::User, "/CMD_API_EMAIL_VACATION", $fields);
    }
    
    /**
     * Returns the fields to create or edit an Email Vacations Message
     * @param string  $action
     * @param string  $user
     * @param string  $text
     * @param integer $fromTime
     * @param integer $toTime
     * @return array
     */
    private function getFields(string $action, string $user, string $text, int $fromTime, int $toTime): array {
        return [
            "action"     => $action,
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
        return $this->post(Context::User, "/CMD_API_EMAIL_VACATION", [
            "action"  => "delete",
            "select0" => $user,
        ]);
    }
}
