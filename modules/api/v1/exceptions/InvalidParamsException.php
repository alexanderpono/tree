<?php
namespace api\v1\exceptions;

class InvalidParamsException extends JsonRpcException
{
    protected $code = -32602;
    protected $httpCode = 400;
    protected $message = 'Invalid params';
}
