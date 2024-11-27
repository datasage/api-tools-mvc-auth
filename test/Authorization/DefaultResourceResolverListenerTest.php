<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\MvcAuth\Authorization;

use Laminas\ApiTools\MvcAuth\Authorization\AuthorizationInterface;
use Laminas\ApiTools\MvcAuth\Authorization\DefaultResourceResolverListener;
use Laminas\ApiTools\MvcAuth\MvcAuthEvent;
use Laminas\Http\Request as HttpRequest;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\MvcEvent;
use LaminasTest\ApiTools\MvcAuth\RouteMatchFactoryTrait;
use LaminasTest\ApiTools\MvcAuth\TestAsset;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultResourceResolverListenerTest extends TestCase
{
    use RouteMatchFactoryTrait;

    protected DefaultResourceResolverListener $listener;
    protected array $restControllers;
    protected MvcAuthEvent $mvcAuthEvent;
    protected TestAsset\AuthenticationService $authentication;
    protected MockObject $authorization;

    public function setUp(): void
    {
        $routeMatch = $this->createRouteMatch([]);
        $request    = new HttpRequest();
        $response   = new HttpResponse();
        $mvcEvent   = new MvcEvent();
        $mvcEvent->setRequest($request)
            ->setResponse($response)
            ->setRouteMatch($routeMatch);
        $this->mvcAuthEvent = $this->createMvcAuthEvent($mvcEvent);

        $this->restControllers = [
            'LaminasCon\V1\Rest\Session\Controller' => 'session_id',
        ];
        $this->listener        = new DefaultResourceResolverListener($this->restControllers);
    }

    public function createMvcAuthEvent(MvcEvent $mvcEvent): MvcAuthEvent
    {
        $this->authentication = new TestAsset\AuthenticationService();
        $this->authorization  = $this->getMockBuilder(AuthorizationInterface::class)
            ->getMock();
        return new MvcAuthEvent($mvcEvent, $this->authentication, $this->authorization);
    }

    public function testBuildResourceStringReturnsFalseIfControllerIsMissing(): void
    {
        $mvcEvent   = $this->mvcAuthEvent->getMvcEvent();
        $routeMatch = $mvcEvent->getRouteMatch();
        $request    = $mvcEvent->getRequest();
        $this->assertFalse($this->listener->buildResourceString($routeMatch, $request));
    }

    public function testBuildResourceStringReturnsEntityWhenIdentifierIsZero(): void
    {
        $mvcEvent   = $this->mvcAuthEvent->getMvcEvent();
        $routeMatch = $mvcEvent->getRouteMatch();
        $routeMatch->setParam('controller', 'LaminasCon\V1\Rest\Session\Controller');
        $routeMatch->setParam('action', 'foo');
        $routeMatch->setParam('session_id', '0');
        $request = $mvcEvent->getRequest();
        $this->assertEquals(
            'LaminasCon\V1\Rest\Session\Controller::entity',
            $this->listener->buildResourceString($routeMatch, $request)
        );
    }

    public function testBuildResourceStringReturnsControllerActionFormattedStringForNonRestController(): void
    {
        $mvcEvent   = $this->mvcAuthEvent->getMvcEvent();
        $routeMatch = $mvcEvent->getRouteMatch();
        $routeMatch->setParam('controller', 'Foo\Bar\Controller');
        $routeMatch->setParam('action', 'foo');
        $request = $mvcEvent->getRequest();
        $this->assertEquals('Foo\Bar\Controller::foo', $this->listener->buildResourceString($routeMatch, $request));
    }

    public function testBuildResourceStringReturnsControllerNameAndCollectionIfNoIdentifierAvailable(): void
    {
        $mvcEvent   = $this->mvcAuthEvent->getMvcEvent();
        $routeMatch = $mvcEvent->getRouteMatch();
        $routeMatch->setParam('controller', 'LaminasCon\V1\Rest\Session\Controller');
        $request = $mvcEvent->getRequest();
        $this->assertEquals(
            'LaminasCon\V1\Rest\Session\Controller::collection',
            $this->listener->buildResourceString($routeMatch, $request)
        );
    }

    public function testBuildResourceStringReturnsControllerNameAndResourceIfIdentifierInRouteMatch(): void
    {
        $mvcEvent   = $this->mvcAuthEvent->getMvcEvent();
        $routeMatch = $mvcEvent->getRouteMatch();
        $routeMatch->setParam('controller', 'LaminasCon\V1\Rest\Session\Controller');
        $routeMatch->setParam('session_id', 'foo');
        $request = $mvcEvent->getRequest();
        $this->assertEquals(
            'LaminasCon\V1\Rest\Session\Controller::entity',
            $this->listener->buildResourceString($routeMatch, $request)
        );
    }

    public function testBuildResourceStringReturnsControllerNameAndResourceIfIdentifierInQueryString(): void
    {
        $mvcEvent   = $this->mvcAuthEvent->getMvcEvent();
        $routeMatch = $mvcEvent->getRouteMatch();
        $routeMatch->setParam('controller', 'LaminasCon\V1\Rest\Session\Controller');
        $request = $mvcEvent->getRequest();
        $request->getQuery()->set('session_id', 'bar');
        $this->assertEquals(
            'LaminasCon\V1\Rest\Session\Controller::entity',
            $this->listener->buildResourceString($routeMatch, $request)
        );
    }
}
