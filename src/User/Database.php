<?php
namespace DirectAdmin\User;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The User Databases
 */
class Database extends Adapter {

    /**
     * Returns a list of Databases. Requires user login
     * @return string[]
     */
    public function getAll(): array {
        $response = $this->get(Context::User, "/CMD_API_DATABASES");
        return $response->list;
    }

    /**
     * Returns a list of Databases with Users. Requires user login
     * @return array
     */
    public function getWithUsers(): array {
        $response = $this->get(Context::User, "/CMD_API_DATABASES");
        $result   = [
            "data"  => [],
            "names" => [],
            "users" => [],
        ];

        foreach ($response->list as $index => $name) {
            $users = $this->get(Context::User, "/CMD_API_DB_USER", [ "name" => $name ]);
            array_shift($users->list);

            $result["data"][$index] = [
                "index" => $index,
                "name"  => $name,
                "users" => $users->list,
            ];
            $result["names"][] = $name;
            $result["users"]   = array_merge($result["users"], $users->list);
        }
        return $result;
    }



    /**
     * Creates a new Database with the given name and user. Requires user login
     * @param string $name
     * @param string $user
     * @param string $password
     * @return Response
     */
    public function create(string $name, string $user, string $password): Response {
        return $this->post(Context::User, "/CMD_API_DATABASES", [
            "action"  => "create",
            "name"    => $name,
            "user"    => $user,
            "passwd"  => $password,
            "passwd2" => $password,
        ]);
    }

    /**
     * Deletes the Database with the given name. Requires user login
     * @param string $name
     * @return Response
     */
    public function delete(string $name): Response {
        return $this->post(Context::User, "/CMD_API_DATABASES", [
            "action"  => "delete",
            "select0" => $name,
        ]);
    }



    /**
     * Creates a Database User. Requires user login
     * @param string $name
     * @param string $user
     * @param string $password
     * @return Response
     */
    public function createUser(string $name, string $user, string $password): Response {
        return $this->post(Context::User, "/CMD_API_DB_USER", [
            "action"  => "create",
            "name"    => $name,
            "user"    => $user,
            "passwd"  => $password,
            "passwd2" => $password,
        ]);
    }

    /**
     * Edits a Database User. Requires user login
     * @param string $name
     * @param string $user
     * @param string $password
     * @return Response
     */
    public function editUser(string $name, string $user, string $password): Response {
        return $this->post(Context::User, "/CMD_API_DB_USER", [
            "action"  => "modify",
            "name"    => $name,
            "user"    => $user,
            "passwd"  => $password,
            "passwd2" => $password,
        ]);
    }

    /**
     * Deletes the given Database User. Requires user login
     * @param string $name
     * @param string $user
     * @return Response
     */
    public function deleteUser(string $name, string $user): Response {
        return $this->post(Context::User, "/CMD_API_DB_USER", [
            "action"  => "delete",
            "name"    => $name,
            "select0" => $user,
        ]);
    }



    /**
     * Creates a new Access Host. Requires user login
     * @param string $db
     * @param string $host
     * @return Response
     */
    public function createHost(string $db, string $host): Response {
        return $this->post(Context::User, "/CMD_API_DATABASES", [
            "action" => "accesshosts",
            "create" => "yes",
            "db"     => $db,
            "host"   => $host,
        ]);
    }

    /**
     * Deletes an Access Host. Requires user login
     * @param string $db
     * @param string $host
     * @return Response
     */
    public function deleteHost(string $db, string $host): Response {
        return $this->post(Context::User, "/CMD_API_DATABASES", [
            "action"  => "accesshosts",
            "delete"  => "yes",
            "db"      => $db,
            "select0" => $host,
        ]);
    }
}
