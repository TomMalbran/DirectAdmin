<?php
namespace DirectAdmin\Admin;

use DirectAdmin\Adapter;

/**
 * The Mail Queue
 */
class MailQueue {
    
    private $adapter;
    
    /**
     * Creates a new MailQueue instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    

    /**
     * Returns the list with all the mail queue for the current server
     * @return array
     */
    public function getAll() {
        $request = $this->adapter->query("/CMD_API_MAIL_QUEUE");
        $result  = [];
        $index   = 0;
        
        if (!empty($request) && empty($request["error"])) {
            foreach ($request as $data) {
                parse_str($data, $data);
                $result[$index] = [
                    "index"      => $index,
                    "recipients" => [],
                ] + $data;
                
                $recipient = 0;
                while (!empty($data["r$recipient"])) {
                    $result[$index]["recipients"][] = $data["r$recipient"];
                    unset($result[$index]["r$recipient"]);
                    $recipient += 1;
                }
                $index += 1;
            }
        }
        return $result;
    }
    
    /**
     * Returns the data for a single mail, with the given id
     * @param integer $mailID
     * @return array
     */
    public function getOne($mailID) {
        return $this->adapter->query("/CMD_API_MAIL_QUEUE", [
            "id" => $mailID,
        ]);
    }
    

    
    /**
     * Does the given operation over the given mail ids
     * @param string[] $mailIDs
     * @param string   $operation
     * @return array|null
     */
    public function batch(array $mailIDs, $operation) {
        $fields = [ "action" => "select" ];
        $fields[$operation] = 1;
        
        foreach ($mailIDs as $index => $value) {
            $fields["select$index"] = $value;
        }
        return $this->adapter->query("/CMD_API_MAIL_QUEUE", $fields);
    }
}
