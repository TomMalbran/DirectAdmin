<?php
namespace DirectAdmin\Domain;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Server Redirects
 */
class Redirect extends Adapter {

    /**
     * Returns a list of Redirects. Requires user login
     * @return array
     */
    public function getAll(): array {
        $response = $this->get(Context::User, "/CMD_API_REDIRECT");
        $result   = [];

        foreach ($response->data as $from => $to) {
            $result[] = [
                "from" => $from,
                "to"   => $to,
            ];
        }
        return $result;
    }



    /**
     * Adds a new Redirect. Requires user login
     * @param string $from
     * @param string $to
     * @return Response
     */
    public function create(string $from, string $to): Response {
        return $this->post(Context::User, "/CMD_API_REDIRECT", [
            "action" => "add",
            "from"   => $from,
            "to"     => $to,
        ]);
    }

    /**
     * Deletes the given Redirect. Requires user login
     * @param string $from
     * @return Response
     */
    public function delete(string $from): Response {
        return $this->post(Context::User, "/CMD_API_REDIRECT", [
            "action"  => "delete",
            "select0" => $from,
        ]);
    }
}
