<?php
namespace DirectAdmin\Domain;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Server Subdomains
 */
class Subdomain extends Adapter {

    /**
     * Returns a list of Subdomains. Requires user login
     * @param string $domain Optional.
     * @return string[]
     */
    public function getAll(string $domain = ""): array {
        $response = $this->get(Context::User, "/CMD_API_SUBDOMAINS", [
            "domain" => $domain,
        ]);
        return $response->list;
    }



    /**
     * Creates a new Subdomain. Requires user login
     * @param string $subdomain
     * @return Response
     */
    public function create(string $subdomain): Response {
        return $this->post(Context::User, "/CMD_API_SUBDOMAINS", [
            "action"    => "create",
            "subdomain" => $subdomain,
        ]);
    }

    /**
     * Deletes the given Subdomain. Requires user login
     * @param string  $subdomain
     * @param boolean $delContents Optional.
     * @return Response
     */
    public function delete(string $subdomain, bool $delContents = false): Response {
        return $this->post(Context::User, "/CMD_API_SUBDOMAINS", [
            "action"   => "delete",
            "select0"  => $subdomain,
            "contents" => $delContents ? "yes" : "no",
        ]);
    }
}
