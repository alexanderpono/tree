<?php
namespace api\v1\exceptions;

class InvalidRequestException extends JsonRpcException
{
    protected $code = -32600;
    protected $httpCode = 400;
    protected $message = 'Invalid Request';
}
