<?php
namespace DirectAdmin\User;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The User Cron Jobs
 */
class CronJob extends Adapter {

    /**
     * Returns a list of Cron Jobs. Requires user login
     * @return array{}[]
     */
    public function getAll(): array {
        $response = $this->get(Context::User, "/CMD_CRON_JOBS", [
            "json" => "yes",
        ], true);
        if (empty($response->data["crons"])) {
            return [];
        }

        $result = [];
        foreach ($response->data["crons"] as $key => $job) {
            if (is_numeric($key)) {
                $result[] = [
                    "cronID"     => $job["id"],
                    "command"    => $job["command"],
                    "minute"     => $job["minute"],
                    "hour"       => $job["hour"],
                    "dayOfMonth" => $job["day_of_month"],
                    "month"      => $job["month"],
                    "dayOfWeek"  => $job["day_of_week"],
                ];
            }
        }
        return $result;
    }



    /**
     * Creates a Cron Jon. Requires user login
     * @param string         $command
     * @param string|integer $minute     Optional.
     * @param string|integer $hour       Optional.
     * @param string|integer $dayOfMonth Optional.
     * @param string|integer $month      Optional.
     * @param string|integer $dayOfWeek  Optional.
     * @return Response
     */
    public function create(
        string $command,
        string|int $minute = "*",
        string|int $hour = "*",
        string|int $dayOfMonth = "*",
        string|int $month = "*",
        string|int $dayOfWeek = "*"
    ): Response {
        return $this->post(Context::User, "/CMD_API_CRON_JOBS", [
            "action"     => "create",
            "command"    => $command,
            "minute"     => $minute,
            "hour"       => $hour,
            "dayofmonth" => $dayOfMonth,
            "month"      => $month,
            "dayofweek"  => $dayOfWeek,
        ]);
    }

    /**
     * Edits a Cron Jon. Requires user login
     * @param string         $cronID
     * @param string         $command
     * @param string|integer $minute     Optional.
     * @param string|integer $hour       Optional.
     * @param string|integer $dayOfMonth Optional.
     * @param string|integer $month      Optional.
     * @param string|integer $dayOfWeek  Optional.
     * @return Response
     */
    public function edit(
        string $cronID,
        string $command,
        string|int $minute = "*",
        string|int $hour = "*",
        string|int $dayOfMonth = "*",
        string|int $month = "*",
        string|int $dayOfWeek = "*"
    ): Response {
        return $this->post(Context::User, "/CMD_API_CRON_JOBS", [
            "save"       => "Save",
            "id"         => $cronID,
            "command"    => $command,
            "minute"     => $minute,
            "hour"       => $hour,
            "dayofmonth" => $dayOfMonth,
            "month"      => $month,
            "dayofweek"  => $dayOfWeek,
        ]);
    }

    /**
     * Deletes the given FTP Account. Requires user login
     * @param string $cronID
     * @return Response
     */
    public function delete(string $cronID): Response {
        return $this->post(Context::User, "/CMD_API_CRON_JOBS", [
            "action"  => "delete",
            "select0" => $cronID,
        ]);
    }
}
