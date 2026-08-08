<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\MvcAuth;

use Laminas\ApiTools\MvcAuth\MvcAuthEvent;
use Laminas\ApiTools\MvcAuth\MvcRouteListener;
use Laminas\Authentication\AuthenticationService;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\Test\EventListenerIntrospectionTrait;
use Laminas\Mvc\MvcEvent;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class MvcRouteListenerTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    /** @var AuthenticationService&Stub */
    private $auth;

    /** @var MvcAuthEvent&Stub */
    private $event;

    /** @var EventManager */
    private $events;

    /** @var MvcRouteListener */
    private $listener;

    #[Override]
    public function setUp(): void
    {
        $this->events = new EventManager();
        $this->auth   = $this->createStub(AuthenticationService::class);
        $this->event  = $this->createStub(MvcAuthEvent::class);

        $this->listener = new MvcRouteListener(
            $this->event,
            $this->events,
            $this->auth
        );
    }

    public function testRegistersAuthenticationListenerOnExpectedPriority(): void
    {
        $this->listener->attach($this->events);
        $this->assertListenerAtPriority(
            [$this->listener, 'authentication'],
            -50,
            MvcEvent::EVENT_ROUTE,
            $this->events
        );
    }

    public function testRegistersPostAuthenticationListenerOnExpectedPriority(): void
    {
        $this->listener->attach($this->events);
        $this->assertListenerAtPriority(
            [$this->listener, 'authenticationPost'],
            -51,
            MvcEvent::EVENT_ROUTE,
            $this->events
        );
    }

    public function testRegistersAuthorizationListenerOnExpectedPriority(): void
    {
        $this->listener->attach($this->events);
        $this->assertListenerAtPriority(
            [$this->listener, 'authorization'],
            -600,
            MvcEvent::EVENT_ROUTE,
            $this->events
        );
    }

    public function testRegistersPostAuthorizationListenerOnExpectedPriority(): void
    {
        $this->listener->attach($this->events);
        $this->assertListenerAtPriority(
            [$this->listener, 'authorizationPost'],
            -601,
            MvcEvent::EVENT_ROUTE,
            $this->events
        );
    }
}
