<?php
namespace DirectAdmin\Admin;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The Service Monitor
 */
class Service extends Adapter {

    /**
     * Returns the list with the status of all the services
     * @return array{}[]
     */
    public function getAll(): array {
        $response = $this->get(Context::Admin, "/CMD_API_SHOW_SERVICES");
        return $response->data;
    }



    /**
     * Starts the given service
     * @param string $service
     * @return Response
     */
    public function start(string $service): Response {
        return $this->post(Context::Admin, "/CMD_API_SERVICE", [
            "action"  => "start",
            "service" => $service,
        ]);
    }

    /**
     * Stops the given service
     * @param string $service
     * @return Response
     */
    public function stop(string $service): Response {
        return $this->post(Context::Admin, "/CMD_API_SERVICE", [
            "action"  => "stop",
            "service" => $service,
        ]);
    }

    /**
     * Restarts the given service
     * @param string $service
     * @return Response
     */
    public function restart(string $service): Response {
        return $this->post(Context::Admin, "/CMD_API_SERVICE", [
            "action"  => "restart",
            "service" => $service,
        ]);
    }

    /**
     * Reloads the given service
     * @param string $service
     * @return Response
     */
    public function reload(string $service): Response {
        return $this->post(Context::Admin, "/CMD_API_SERVICE", [
            "action"  => "reload",
            "service" => $service,
        ]);
    }

    /**
     * Reboots the server
     * @param string $adminPass
     * @return Response
     */
    public function rebootServer(string $adminPass): Response {
        return $this->post(Context::Admin, "/CMD_API_REBOOT", [
            "passwd" => $adminPass,
        ]);
    }
}
