<?php
namespace DirectAdmin\Domain;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Server Domain Pointers
 */
class DomainPtr extends Adapter {

    /**
     * Returns a list of Domain Pointers. Requires user login
     * @return array
     */
    public function getAll(): array {
        $response = $this->get(Context::User, "/CMD_API_DOMAIN_POINTER");
        $result   = [];

        foreach ($response->data as $from => $alias) {
            $result[] = [
                "name"    => str_replace("_", ".", $from),
                "isAlias" => $alias == "alias",
            ];
        }
        return $result;
    }



    /**
     * Creates a new Domain Pointer. Requires user login
     * @param string  $from
     * @param boolean $isAlias Optional.
     * @return Response
     */
    public function create(string $from, bool $isAlias = true): Response {
        $fields = [
            "action" => "add",
            "from"   => $from,
        ];
        if ($isAlias) {
            $fields["alias"] = "yes";
        }
        return $this->post(Context::User, "/CMD_API_DOMAIN_POINTER", $fields);
    }

    /**
     * Deletes the given Domain Pointer. Requires user login
     * @param string $from
     * @return Response
     */
    public function delete(string $from): Response {
        return $this->post(Context::User, "/CMD_API_DOMAIN_POINTER", [
            "action"  => "delete",
            "select0" => $from,
        ]);
    }
}
