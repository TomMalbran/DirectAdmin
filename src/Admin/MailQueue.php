<?php
namespace DirectAdmin\Admin;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Mail Queue
 */
class MailQueue extends Adapter {

    /**
     * Returns the list with all the mail queue for the current server
     * @return array{}[]
     */
    public function getAll(): array {
        $response = $this->get(Context::Admin, "/CMD_API_MAIL_QUEUE");
        $result   = [];
        $index    = 0;

        foreach ($response->data as $data) {
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
        return $result;
    }

    /**
     * Returns the data for a single mail, with the given id
     * @param integer $mailID
     * @return array{}
     */
    public function getOne(int $mailID): array {
        $response = $this->get(Context::Admin, "/CMD_API_MAIL_QUEUE", [
            "id" => $mailID,
        ]);
        return $response->data;
    }



    /**
     * Does the given operation over the given mail ids
     * @param string[] $mailIDs
     * @param string   $operation
     * @return Response
     */
    public function batch(array $mailIDs, string $operation): Response {
        $fields = [ "action" => "select" ];
        $fields[$operation] = 1;

        foreach ($mailIDs as $index => $value) {
            $fields["select$index"] = $value;
        }
        return $this->post(Context::Admin, "/CMD_API_MAIL_QUEUE", $fields);
    }
}
