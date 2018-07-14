<?php

namespace api\v1\components;

use api\v1\exceptions\InvalidRequestException;
use api\v1\exceptions\JsonRpcException;
use api\v1\exceptions\InternalErrorException;
use api\v1\components\Helper;
use api\v1\components\Response;
use api\v1\components\Request;

class JsonRpcIncomingCall
{
    private $missingParams = [];
    private $errors = [];

    public function process($api)
    {
        $rawPostData = \Yii::$app->getRequest()->getRawBody();
        \Yii::info('Request: ' . $rawPostData, 'api');

        try {

            if (!$rawPostData) {
                throw new InvalidRequestException(JsonRpcException::EMPTY_JSON_BODY);
            }
            $rawRequest = json_decode($rawPostData);

            if (is_object($rawRequest)) {
                $jsonRpcRequest = Request::parse($rawRequest);
                $response = $this->executeRequest($jsonRpcRequest, $api);
            }
            else {
                throw new InvalidRequestException(JsonRpcException::SYNTAX_ERROR);
            }



        } catch (InvalidRequestException $e) {
            $response = new Response();
            $response->setError($e);
        } catch (\Exception $e) {
            \Yii::error((string)$e, 'api');
            $response = new Response();

            $response->setError(new InternalErrorException(
                    JsonRpcException::UNEXPECTED_ERROR, [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ])
            );
        }

        $this->sendResponse($response);
        \Yii::$app->state = \Yii\base\Application::STATE_SENDING_RESPONSE;    // TODO needs checking
        \Yii::$app->end();

    }


    protected function sendResponse($response)
    {
        header('Content-type: application/json');

        if (is_array($response)) {
            $responseBody = array();
            foreach ($response as $resp) {
                $responseBody[] = $resp->toArray();
            }
        } else {
            Helper::httpResponseCode($response->getHttpCode());
            $responseBody = $response->toArray();
        }

        if (!empty($responseBody)) {
            $responseString = json_encode($responseBody);
            echo $responseString;
        }
        \Yii::info('Response: ' . json_encode($responseBody, JSON_PRETTY_PRINT), 'api');
    }


    protected function executeRequest(Request $request, $api)
    {
        $response = new Response();
        $response->setId($request->id);

        $method = $request->method;
        $params = $request->params;

        $rc = new \ReflectionClass($api);
        if (!$rc->hasMethod($method)) {
            throw new InvalidRequestException(InvalidRequestException::MSG_METHOD_NOT_FOUND, ['method' => $method]);
        }
        $methodParams = $rc->getMethod($method)->getParameters();

        $callParamsData = $this->getCallParamsData($params, $methodParams);
        $callParams             = $callParamsData['callParams'];
        $this->errors           = array_merge($this->errors, $callParamsData['errors']);
        $this->missingParams    = array_merge($this->missingParams, $callParamsData['missingParams']);

        if ($this->missingParams) {
            $this->errors[] = JsonRpcException::message(
                JsonRpcException::PARAMS_MISSING,
                ['list' => implode(', ', $this->missingParams)]
            );
        }

        if ($this->errors) {
            throw new InvalidRequestException($this->errors);
        }

        try {
            $result = call_user_func_array(
                array(new $api($callParams), $method),
                $callParams
            );
            $response->setResult($result);
        } catch (JsonRpcException $e) {
            $response->setError($e);
        }

        return $response;
    }


    private function getCallParamsData($jsonRpcParams, $methodParams)
    {
        $callParamsData = ['callParams'=>[], 'errors'=>[], 'missingParams'=>[]];

        if (empty($methodParams)) {
            return $callParamsData;
        }

        $missingParams = [];
        $errors = [];
        $callParams = [];

        foreach ($methodParams as $methodParam) {
            if (isset($jsonRpcParams[$methodParam->name])) {
                $errorMessage = $this->checkParamType($methodParam, $jsonRpcParams[$methodParam->name]);
                $paramTypeIsCorrect = ($errorMessage === '');
                if ($paramTypeIsCorrect) {
                    $callParams[$methodParam->name] = $jsonRpcParams[$methodParam->name];
                }
                else {
                    $errors[] = $errorMessage;
                }
            } elseif ($methodParam->isDefaultValueAvailable()) {
                $callParams[$methodParam->name] = $methodParam->getDefaultValue();
            } else {
                $missingParams[] = $methodParam->name;
            }
        };

        $callParamsData['callParams'] = $callParams;
        $callParamsData['missingParams'] = $missingParams;
        $callParamsData['errors'] = $errors;
        return $callParamsData;
    }


    protected function checkParamType(\ReflectionParameter $methodParam, $param)
    {
        $requiredType = (string)$methodParam->getType();
        if ($requiredType === '') return true;

        $givenType = gettype($param);;

        switch ($requiredType) {
            case 'int':
            case 'integer':
                $requiredType = 'integer';
                break;
            case 'double':
            case 'float':
                $requiredType = 'double';
                break;
            case 'bool':
            case 'boolean':
                $requiredType = 'boolean';
                break;
        }

        if ($requiredType !== $givenType) {
            return JsonRpcException::message(
                JsonRpcException::INVALID_PARAM_TYPE, [
                'param'  => $methodParam->name,
                'requiredType' => $requiredType,
                'givenType' => $givenType
            ]);
        }

        return '';
    }

}