<?php
namespace api\v1\exceptions;

class InternalErrorException extends JsonRpcException
{
    protected $code = -32603;
    protected $httpCode = 500;
    protected $message = 'Internal error';
}
