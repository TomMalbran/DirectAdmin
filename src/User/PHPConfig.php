<?php
namespace DirectAdmin\User;

use DirectAdmin\Context;
use DirectAdmin\Adapter;
use DirectAdmin\Response;

/**
 * The User PHP Config
 */
class PHPConfig extends Adapter {

    /**
     * Retrieves the PHP safe mode and open basedir config
     * @return array
     */
    public function getAll(): array {
        $response = $this->get(Context::User, "/CMD_API_PHP_SAFE_MODE");
        $domain   = $this->context->domain;
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
        return $result;
    }



    /**
     * Activates/Deactivates PHP Safe Mode
     * @param boolean $enable Optional.
     * @return Response
     */
    public function setSafeMode(bool $enable = true): Response {
        $fields = [
            "action"  => "set",
            "select0" => $this->context->domain,
        ];
        if ($enable) {
            $fields["enable"] = 1;
        } else {
            $fields["disable"] = 1;
        }
        return $this->post(Context::User, "/CMD_API_PHP_SAFE_MODE", $fields);
    }

    /**
     * Activates/Deactivates PHP Open Basedir
     * @param boolean $enable Optional.
     * @return Response
     */
    public function setOpenBasedir(bool $enable = true): Response {
        $fields = [
            "action"  => "set",
            "select0" => $this->context->domain,
        ];
        if ($enable) {
            $fields["enable_obd"] = 1;
        } else {
            $fields["disable_obd"] = 1;
        }
        return $this->post(Context::User, "/CMD_API_PHP_SAFE_MODE", $fields);
    }
}
