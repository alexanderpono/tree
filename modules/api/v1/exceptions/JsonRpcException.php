<?php

namespace api\v1\exceptions;

use yii\web\HttpException;

/**
 * @link http://www.jsonrpc.org/specification#error_object
 *
 * -32700  Parse error Invalid JSON was received by the server. An error occurred on the server while parsing the JSON text.
 * -32600  Invalid Request The JSON sent is not a valid Request object.
 * -32601  Method not found    The method does not exist / is not available.
 * -32602  Invalid params  Invalid method parameter(s).
 * -32603  Internal error  Internal JSON-RPC error.
 * -32000 to -32099    Server error    Reserved for implementation-defined server-errors.
 * In addition, the range -32099 .. -32000, inclusive is reserved for implementation defined server errors.
 * Server errors which do not cleanly map to a specific error defined by this spec should be assigned to a number in this range.
 * This leaves the remainder of the space available for application defined errors.
 */
class JsonRpcException extends HttpException
{
    const SERVER_ERROR            = -32000;

    const DATA_NOT_FOUND_ERROR    = -32001;
    const PERMISSION_DENIED_ERROR = -32002;
    const ALREADY_EXECUTE_ERROR   = -32003;
    const EXECUTE_ERROR           = -32004;

    const INVALID_REQUEST         = -32600;
    const METHOD_NOT_FOUND        = -32601;
    const INVALID_PARAMS          = -32602;
    const INTERNAL_ERROR          = -32603;

    const PARSE_ERROR             = -32700;

    const EMPTY_JSON_BODY               = "Received an empty JSON request";
    const SYNTAX_ERROR                  = "Error in JSON syntax";
    const INVALID_RPC_FIELD             = "Invalid jsonrpc field '{field}'. Expected '2.0'";
    const INVALID_METHOD_FIELD          = "Invalid method field '{field}'";
    const INVALID_PARAMS_FIELD          = "Invalid params field";
    const EMPTY_ID_FIELD                = "Empty id field";
    const INVALID_ID_FIELD              = "Invalid id field";
    const MSG_METHOD_NOT_FOUND          = "Method not found: '{method}'";
    const UNEXPECTED_ERROR              = "Unexpected error: {message}";
    const PARAMS_MISSING                = "Params missing: {list}";
    const INVALID_PARAM_TYPE            = "Param {param} must be of type {requiredType}, {givenType} given";

    protected $code     = -32603;   // default = INTERNAL_ERROR
    protected $httpCode = 500;
    protected $message  = 'Internal error';
    private $data = [];

    public function __construct($messageTemplate = null, $messageParams = null)
    {
        $message = self::message($messageTemplate, $messageParams);
        if ($message !== '') {
            $this->data[] = $message;
        };

        parent::__construct($this->httpCode, $this->message, $this->getRPCCode());
    }

    public static function message($messageTemplate = '', $messageParams = null)
    {
        $message = $messageTemplate;

        if (($messageTemplate !== null) && ($messageParams !== null)) {
            foreach ($messageParams as $name => $value) {
                if (!is_string($value)) {
                    continue;
                };

                $name = '{' . $name . '}';
                $message = str_replace($name, $value, $message);
            };
        }
        return $message;
    }

    public function getRPCCode()
    {
        return $this->code;
    }

    public function getHTTPStatusCode()
    {
        return $this->httpCode;
    }

    public function getData()
    {
        return $this->data;
    }
}

