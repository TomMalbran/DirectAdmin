<?php
namespace DirectAdmin;

use DirectAdmin\Context;

/**
 * The Adapter Class
 */
class Adapter {

    protected Context $context;


    /**
     * Creates a new Adapter instance
     * @param Context $context
     */
    public function __construct(Context $context) {
        $this->context = $context;
    }



    /**
     * Does a get query to the server
     * @param string  $context
     * @param string  $endPoint
     * @param array{} $params   Optional.
     * @param boolean $isJSON   Optional.
     * @return Response
     */
    protected function get(string $context, string $endPoint, array $params = [], bool $isJSON = false): Response {
        return $this->query($context, $endPoint, $params, "GET", $isJSON);
    }

    /**
     * Does a post query to the server
     * @param string  $context
     * @param string  $endPoint
     * @param array{} $params   Optional.
     * @param boolean $isJSON   Optional.
     * @return Response
     */
    protected function post(string $context, string $endPoint, array $params = [], bool $isJSON = false): Response {
        return $this->query($context, $endPoint, $params, "POST", $isJSON);
    }

    /**
     * Does a query to the server
     * @param string  $context
     * @param string  $endPoint
     * @param array{} $params
     * @param string  $method
     * @param boolean $isJSON
     * @return Response
     */
    private function query(string $context, string $endPoint, array $params, string $method, bool $isJSON): Response {
        $userpwd = $this->context->getUserpwd($context);
        $url     = $this->context->getUrl($endPoint);
        $params  = $this->context->addParams($context, $params);

        if ($method == "GET") {
            $url .= "?" . http_build_query($params);
        }
        $options = [
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_URL             => $url,
			CURLOPT_USERPWD         => $userpwd,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => false,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_FORBID_REUSE    => true,
            CURLOPT_TIMEOUT         => 100,
            CURLOPT_CONNECTTIMEOUT  => 10,
            CURLOPT_LOW_SPEED_LIMIT => 512,
            CURLOPT_LOW_SPEED_TIME  => 120,
        ];
        if ($method == "POST") {
            $options += [
                CURLOPT_POST       => 1,
                CURLOPT_POSTFIELDS => http_build_query($params),
            ];
        }

        // Execute the query and parse the Result
        [ $result, $error, $errno ] = $this->execute($options);

        if (!empty($error)) {
            return Response::error("CURL ERROR: $error");
        }
        if (!empty($result) && strpos($result, "<title>DirectAdmin Login</title>") !== false) {
            return Response::error("WRONG USERNAME OR PASSWORD");
        }
        if ($isJSON) {
            return Response::parseJSON($result);
        }
        return Response::parse($result);
    }



    /**
     * Uploads a File using FTP
     * @param string $path
     * @param string $fileName
     * @param string $filePath
     * @param string $user
     * @param string $password
     * @return Response
     */
    protected function uploadFile(string $path, string $fileName, string $filePath, string $user, string $password): Response {
        [ $result, $error, $errno ] = $this->execute([
            CURLOPT_URL        => $this->context->getFtp($path, $fileName),
            CURLOPT_USERPWD    => "{$user}:{$password}",
            CURLOPT_HTTPAUTH   => CURLAUTH_BASIC,
            CURLOPT_UPLOAD     => 1,
            CURLOPT_INFILE     => fopen($filePath, "r"),
            CURLOPT_INFILESIZE => filesize($filePath),
        ]);

        if (!empty($errno)) {
            return Response::error("CURL ERROR: $errno $error");
        }
        return Response::parse($result);
    }

    /**
     * Execute a Curl request
     * @param array{} $options
     * @return mixed[]
     */
    private function execute(array $options): array {
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        $error  = curl_error($curl);
        $errno  = curl_errno($curl);
        curl_close($curl);
        return [ $result, $error, $errno ];
    }
}
