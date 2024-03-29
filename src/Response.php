<?php
namespace DirectAdmin;

/**
 * The Adapter Response
 */
class Response {

    /** @var array{} */
    public array  $parsed   = [];
    public string $raw      = "";

    public bool   $hasError = true;
    public string $error    = "";
    public string $details  = "";

    /** @var mixed[] */
    public array  $data     = [];

    /** @var string[] */
    public array  $keys     = [];

    /** @var string[] */
    public array  $list     = [];


    /**
     * Creates a new Response instance
     * @param array{} $parsed Optional.
     * @param string  $raw    Optional.
     */
    public function __construct(array $parsed = [], string $raw = "") {
        if (!empty($parsed)) {
            $this->parsed   = $parsed;
            $this->raw      = $raw;

            $this->hasError = !empty($parsed["error"]);
            $this->error    = !empty($parsed["text"])    ? $parsed["text"]    : "";
            $this->details  = !empty($parsed["details"]) ? $parsed["details"] : "";

            if (!$this->hasError) {
                $this->data = $parsed;
                $this->keys = array_keys($parsed);
                $this->list = !empty($parsed["list"]) ? $parsed["list"] : [];
            }
        }
    }



    /**
     * Creates a Parsed Response
     * @param string $raw
     * @return Response
     */
    public static function parse(string $raw): Response {
        if (empty($raw)) {
            return new Response();
        }

        parse_str($raw, $data);
        if (!empty($data) && empty($data["error"])) {
            $parsed = [];
            foreach ($data as $index => $value) {
                if (!empty($value) && is_string($value) && strstr($value, "=") !== FALSE) {
                    parse_str($value, $array);
                    $parsed[$index] = $array;
                } else {
                    $parsed[$index] = $value;
                }
            }
        } else {
            $parsed = $data;
        }
        return new Response($parsed, $raw);
    }

    /**
     * Creates a Parsed JSON Response
     * @param string $raw
     * @return Response
     */
    public static function parseJSON(string $raw): Response {
        if (empty($raw)) {
            return new Response();
        }
        $parsed = json_decode($raw, true);
        return new Response($parsed, $raw);
    }

    /**
     * Creates an Error Response
     * @param string $error
     * @return Response
     */
    public static function error(string $error): Response {
        return new Response([
            "error" => 1,
            "text"  => $error,
        ]);
    }

    /**
     * Creates a Success Response
     * @param string $success
     * @return Response
     */
    public static function success(string $success): Response {
        return new Response([
            "error" => 0,
            "text"  => $success,
        ]);
    }
}
