<?php
namespace DirectAdmin\Admin;

use DirectAdmin\Adapter;

/**
 * The Service Monitor
 */
class Service {
    
    private $adapter;
    
    /**
     * Creates a new Service instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
    
    /**
     * Returns the list with the status of all the services
     * @return array
     */
    public function getAll() {
        return $this->adapter->query("/CMD_API_SHOW_SERVICES");
    }
    
    

    /**
     * Starts the given service
     * @param string $service
     * @return array|null
     */
    public function start($service) {
        return $this->adapter->query("/CMD_API_SERVICE", [
            "action"  => "start",
            "service" => $service,
        ]);
    }
    
    /**
     * Stops the given service
     * @param string $service
     * @return array|null
     */
    public function stop($service) {
        return $this->adapter->query("/CMD_API_SERVICE", [
            "action"  => "stop",
            "service" => $service,
        ]);
    }
    
    /**
     * Restarts the given service
     * @param string $service
     * @return array|null
     */
    public function restart($service) {
        return $this->adapter->query("/CMD_API_SERVICE", [
            "action"  => "restart",
            "service" => $service,
        ]);
    }
    
    /**
     * Reloads the given service
     * @param string $service
     * @return array|null
     */
    public function reload($service) {
        return $this->adapter->query("/CMD_API_SERVICE", [
            "action"  => "reload",
            "service" => $service,
        ]);
    }
    
    /**
     * Reboots the server
     * @param string $adminpass
     * @return array|null
     */
    public function rebootServer($adminpass) {
        return $this->adapter->query("/CMD_API_REBOOT", [
            "passwd" => $adminpass,
        ], "POST");
    }
}
