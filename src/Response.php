<?php
namespace DirectAdmin;

/**
 * The Query Response
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
            $this->error    = !empty($data["error"]) ? $data["error"] : "";
            $this->list     = !empty($data["list"])  ? $data["list"]  : [];
            $this->data     = $data;
            $this->keys     = array_keys($data);
        }
    }
}
