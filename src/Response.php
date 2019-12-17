<?php
namespace DirectAdmin;

/**
 * The Adapter Response
 */
class Response {
    
    public $hasError = true;
    public $error    = "";
    public $list     = [];
    public $data     = [];
    public $keys     = [];


    /**
     * Creates a new Response instance
     * @param array $data Optional.
     */
    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->hasError = !empty($data["error"]);
            $this->error    = !empty($data["details"]) ? $data["details"] : "";
            $this->list     = !empty($data["list"])    ? $data["list"]    : [];
            $this->data     = $data;
            $this->keys     = array_keys($data);
        }
    }

    /**
     * Creates an Errror Response
     * @param string $error
     * @return Response
     */
    public static function error(string $error): Response {
        return new Response([
            "error"   => 1,
            "details" => $error,
        ]);
    }
}
