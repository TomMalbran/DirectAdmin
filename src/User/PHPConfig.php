<?php
namespace DirectAdmin\User;

use DirectAdmin\Adapter;

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
     * @param string $domain
     * @return array
     */
    public function getAll($domain) {
        $request = $this->adapter->query("/CMD_API_PHP_SAFE_MODE");
        $domain  = str_replace(".", "_", $domain);
        $data    = [];
        
        if (empty($request["error"])) {
            if (!empty($request[$domain])) {
                parse_str($request[$domain], $data);
                return [
                    "safeMode"    => $data["safemode"]     == "ON",
                    "openBasedir" => $data["open_basedir"] == "ON",
                ];
            }
            return [
                "safeMode"    => false,
                "openBasedir" => false,
            ];
        }
        return $request;
    }
    
    

    /**
     * Activates/Deactivates PHP Safe Mode
     * @param string  $domain
     * @param boolean $enable Optional.
     * @return array|null
     */
    public function setSafeMode($domain, $enable = true) {
        $fields = [
            "action"  => "set",
            "select0" => $domain,
        ];
        if ($enable) {
            $fields["enable"] = 1;
        } else {
            $fields["disable"] = 1;
        }
        
        return $this->adapter->query("/CMD_API_PHP_SAFE_MODE", $fields);
    }
    
    /**
     * Activates/Deactivates PHP Open Basedir
     * @param string  $domain
     * @param boolean $enable Optional.
     * @return array|null
     */
    public function setOpenBasedir($domain, $enable = true) {
        $fields = [
            "action"  => "set",
            "select0" => $domain,
        ];
        if ($enable) {
            $fields["enable_obd"] = 1;
        } else {
            $fields["disable_obd"] = 1;
        }
        
        return $this->adapter->query("/CMD_API_PHP_SAFE_MODE", $fields);
    }
}
