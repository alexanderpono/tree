<?php
namespace api\v1\components;

use api\v1\exceptions\InvalidParamsException;
use api\v1\exceptions\InvalidRequestException;

/**
 * Объект JSON-RPC запроса
 * @link http://www.jsonrpc.org/specification#request_object
 */
class Request
{
    /**
     * Версия протокола
     * A String specifying the version of the JSON-RPC protocol. MUST be exactly "2.0".
     * @var string
     * @access public
     */
    public $jsonrpc;

    /**
     * Название вызываемого метода
     * A String containing the name of the method to be invoked.
     * Method names that begin with the word rpc followed by a period character (U+002E or ASCII 46) are reserved
     * for rpc-internal methods and extensions and MUST NOT be used for anything else.
     * @var string
     * @access public
     */
    public $method;

    /**
     * A Structured value that holds the parameter values to be used during the invocation of the method. This member MAY be omitted.
     * @var array
     * @access public
     */
    public $params = array();

    /**
     * An identifier established by the Client that MUST contain a String, Number, or NULL value if included.
     * If it is not included it is assumed to be a notification.
     * The value SHOULD normally not be Null [1] and Numbers SHOULD NOT contain fractional parts
     * [1] The use of Null as a value for the id member in a Request object is discouraged,
     * because this specification uses a value of Null for Responses with an unknown id.
     * Also, because JSON-RPC 1.0 uses an id value of Null for Notifications this could cause confusion in handling.
     * [2] Fractional parts may be problematic, since many decimal fractions cannot be represented exactly as binary fractions
     * @var mixed
     * @access public
     */
    public $id = null;

    public static function parse($rawData)
    {
        if (is_object($rawData)) {
            $result = new static();
            $result->jsonrpc = static::parseJsonRpc($rawData);
            $result->method = static::parseMethod($rawData);
            $result->params = static::parseParams($rawData);
            $result->id = static::parseId($rawData);
        } else if (is_array($rawData)) {
            $result = array();
            foreach ($rawData as $rawDataRecord) {
                $result[] = static::parse($rawDataRecord);
            }
        } else {
            throw new InvalidRequestException(JsonRpcException::SYNTAX_ERROR);
        }
        return $result;
    }

    /**
     * Version parsing
     */
    protected static function parseJsonRpc($rawRequest)
    {
        if (!isset($rawRequest->jsonrpc) || $rawRequest->jsonrpc !== '2.0') {
            throw new InvalidRequestException(JsonRpcException::INVALID_RPC_FIELD, ['field' => $rawRequest->jsonrpc]);
        }
        return $rawRequest->jsonrpc;
    }

    /**
     * Method parsing
     */
    protected static function parseMethod($rawRequest)
    {
        if (!isset($rawRequest->method) || !is_string($rawRequest->method) ||
            $rawRequest->method == '' || substr($rawRequest->method, 0, 4) == 'rpc.'
        ) {
            throw new InvalidRequestException(JsonRpcException::INVALID_METHOD_FIELD, ['field' => $rawRequest->method]);
        }
        return $rawRequest->method;
    }

    /**
     * Params parsing
     */
    protected static function parseParams($rawRequest)
    {
        if (isset($rawRequest->params)) {

            if (!settype($rawRequest->params, 'array')) {
                throw new InvalidRequestException(JsonRpcException::INVALID_PARAMS_FIELD);
            }

            return $rawRequest->params;
        }
        return array();
    }

    /**
     * Id parsing
     */
    protected static function parseId($rawRequest)
    {
        if (isset($rawRequest->id)) {
            if ($rawRequest->id == null) {
                throw new InvalidRequestException(JsonRpcException::EMPTY_ID_FIELD);
            }
            if (isset($rawRequest->id) && !is_int($rawRequest->id)
                && !is_string($rawRequest->id))
            {
                throw new InvalidRequestException(JsonRpcException::INVALID_ID_FIELD);
            }
        }
        return $rawRequest->id;
    }

    /**
     * Магическая функция строкового представления объекта запроса
     * Отдаем json-изированный $rawRequest. Все равно он валиден, без валидации объект не создастся
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * Полумагическая функция, возвращает представление объекта в виде массива, пригодного для json-кодирования
     */
    public function toArray()
    {
        $data = array(
            'jsonrpc' => $this->jsonrpc,
            'method'  => $this->method,
        );
        if (!empty($this->params)) {
            $data['params'] = $this->params;
        }
        if (!empty($this->id)) {
            $data['id'] = $this->id;
        }
        return $data;
    }
}
