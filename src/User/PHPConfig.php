<?php
namespace DirectAdmin\User;

use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The User PHP Config
 */
class PHPConfig {
    
    private $adapter;
    
    /**
     * Creates a new PHPConfig instance
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    

    /**
     * Retrieves the PHP safe mode and open basedir config for the given domain
     * @return array
     */
    public function getAll(): array {
        $response = $this->adapter->get("/CMD_API_PHP_SAFE_MODE");
        $domain   = $this->adapter->getDomain();
        $domain   = str_replace(".", "_", $domain);
        $result   = [];
        
        $result = [
            "safeMode"    => false,
            "openBasedir" => false,
        ];
        if (!empty($response->data[$domain])) {
            $result = [
                "safeMode"    => $response->data["safemode"]     == "ON",
                "openBasedir" => $response->data["open_basedir"] == "ON",
            ];
        }
        return $response;
    }
    
    

    /**
     * Activates/Deactivates PHP Safe Mode
     * @param boolean $enable Optional.
     * @return Response
     */
    public function setSafeMode(bool $enable = true): Response {
        $fields = [
            "action"  => "set",
            "select0" => $this->adapter->getDomain(),
        ];
        if ($enable) {
            $fields["enable"] = 1;
        } else {
            $fields["disable"] = 1;
        }
        return $this->adapter->post("/CMD_API_PHP_SAFE_MODE", $fields);
    }
    
    /**
     * Activates/Deactivates PHP Open Basedir
     * @param boolean $enable Optional.
     * @return Response
     */
    public function setOpenBasedir(bool $enable = true): Response {
        $fields = [
            "action"  => "set",
            "select0" => $this->adapter->getDomain(),
        ];
        if ($enable) {
            $fields["enable_obd"] = 1;
        } else {
            $fields["disable_obd"] = 1;
        }
        return $this->adapter->post("/CMD_API_PHP_SAFE_MODE", $fields);
    }
}
