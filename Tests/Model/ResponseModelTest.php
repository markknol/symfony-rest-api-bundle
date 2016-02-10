<?php

namespace MediaMonks\RestApiBundle\Tests\Model;

use MediaMonks\RestApiBundle\Model\ResponseModel;
use MediaMonks\RestApiBundle\Response\OffsetPaginatedResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use \Mockery as m;

class ResponseModelTest extends \PHPUnit_Framework_TestCase
{
    public function testDataGettersSetter()
    {
        $data              = ['foo', 'bar'];
        $responseContainer = new ResponseModel();
        $responseContainer->setData($data);
        $this->assertEquals($data, $responseContainer->getData());
    }

    public function testExceptionGettersSetter()
    {
        $exception         = new \Exception;
        $responseContainer = new ResponseModel();
        $responseContainer->setException($exception);
        $this->assertEquals($exception, $responseContainer->getException());
    }

    public function testLocationGettersSetter()
    {
        $location          = 'http://www.mediamonks.com';
        $redirect          = new RedirectResponse($location);
        $responseContainer = new ResponseModel();
        $responseContainer->setRedirect($redirect);
        $this->assertEquals($redirect, $responseContainer->getRedirect());
    }

    public function testPaginationGettersSetter()
    {
        $pagination        = new OffsetPaginatedResponse('foo', 1, 2, 3);
        $responseContainer = new ResponseModel();
        $responseContainer->setPagination($pagination);
        $this->assertEquals($pagination, $responseContainer->getPagination());
    }

    public function testReturnStatusCodeGetterSetter()
    {
        $statusCode        = Response::HTTP_NOT_MODIFIED;
        $responseContainer = new ResponseModel();
        $responseContainer->setReturnStatusCode($statusCode);
        $this->assertEquals($statusCode, $responseContainer->getReturnStatusCode());
    }

    public function testStatusCodeGetterSetter()
    {
        $statusCode        = Response::HTTP_OK;
        $responseContainer = new ResponseModel();
        $responseContainer->setData('OK');
        $responseContainer->setStatusCode($statusCode);
        $this->assertEquals($statusCode, $responseContainer->getStatusCode());
    }

    public function testGetCodeFromStatusCode()
    {
        $statusCode = Response::HTTP_BAD_REQUEST;
        $code       = 400;
        $exception  = new \Exception('', $code);

        $responseContainer = new ResponseModel();
        $responseContainer->setStatusCode($statusCode);
        $responseContainer->setException($exception);

        $this->assertEquals($code, $responseContainer->getStatusCode());
    }

    public function testToArrayStatusCode()
    {
        $responseContainer = new ResponseModel();
        $responseContainer->setReturnStatusCode(Response::HTTP_OK);

        $this->assertEquals(['statusCode' => Response::HTTP_OK], $responseContainer->toArray());
    }

    public function testValidationExceptionToArrayFormValidationException()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('This test fails on HHVM, see issue #8');
        }

        $error = ['code' => 0, 'message' => '', 'fields' => null];

        $mockException = m::mock('\MediaMonks\RestApiBundle\Exception\ValidationException, \MediaMonks\RestApiBundle\Exception\ExceptionInterface');
        $mockException->shouldReceive('toArray')->andReturn($error);
        $mockException->shouldReceive('getFields');

        $responseContainer = new ResponseModel();
        $responseContainer->setException($mockException);

        $this->assertEquals(['error' => $error], $responseContainer->toArray());
    }

    /**
     * @param $content
     * @return ResponseModel
     */
    protected function createResponseModel($content)
    {
        return ResponseModel::createAutoDetect($content);
    }
}
