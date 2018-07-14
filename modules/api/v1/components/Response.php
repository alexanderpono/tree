<?php
namespace api\v1\components;

use api\v1\exceptions\JsonRpcException;
/**
 * Объект JSON-RPC ответа
 * @link http://www.jsonrpc.org/specification#response_object
 * @link http://www.simple-is-better.org/json-rpc/JsonRpc0-over-http.html
 */
class Response
{
    /**
     * @var string
     * @access public
     */
    public $jsonrpc = '2.0';

    /**
     * @var string
     * @access public
     */
    public $id = null;

    /**
     * @var mixed
     */
    public $result = null;

    /**
     * @var mixed
     */
    public $error = null;

    /**
     * HTTP-код возврата
     * @var int
     * @access protected
     */
    protected $httpCode = 200;

    /**
     * @param JsonRpcException $e
     * @return $this
     */
    public function setError(JsonRpcException $e)
    {
        $this->error = $e;
        $this->setHttpCode($e->getHTTPStatusCode());
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'jsonrpc' => $this->jsonrpc,
            'id'      => $this->id,
        ];
        if (!empty($this->error)) {
            $result['error'] = [
                'code'    => $this->error->getRPCCode(),
                'message' => $this->error->getMessage(),
                'data'    => $this->error->getData(),
            ];
        } elseif (!empty($this->result)) {
            $result['result'] = $this->result;
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @param $httpCode
     * @return $this
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }


    /**
     * @param $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
