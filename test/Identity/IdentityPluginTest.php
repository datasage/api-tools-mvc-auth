<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\MvcAuth\Identity;

use Laminas\ApiTools\MvcAuth\Identity\AuthenticatedIdentity;
use Laminas\ApiTools\MvcAuth\Identity\GuestIdentity;
use Laminas\ApiTools\MvcAuth\Identity\IdentityPlugin;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\MvcEvent;
use Override;
use PHPUnit\Framework\TestCase;

class IdentityPluginTest extends TestCase
{
    protected IdentityPlugin $plugin;
    protected MvcEvent $event;

    #[Override]
    public function setUp(): void
    {
        $this->event = $event = new MvcEvent();

        $controller = $this->createStub(AbstractController::class);
        $controller
            ->method('getEvent')
            ->willReturnCallback(function () use ($event) {
                return $event;
            });

        $this->plugin = new IdentityPlugin();
        $this->plugin->setController($controller);
    }

    public function testMissingIdentityParamInEventCausesPluginToYieldGuestIdentity(): void
    {
        $this->assertInstanceOf(GuestIdentity::class, $this->plugin->__invoke());
    }

    public function testInvalidTypeInEventIdentityParamCausesPluginToYieldGuestIdentity(): void
    {
        $this->event->setParam('Laminas\ApiTools\MvcAuth\Identity', (object) ['foo' => 'bar']);
        $this->assertInstanceOf(GuestIdentity::class, $this->plugin->__invoke());
    }

    public function testValidIdentityInEventIsReturnedByPlugin(): void
    {
        $identity = new AuthenticatedIdentity('mwop');
        $this->event->setParam('Laminas\ApiTools\MvcAuth\Identity', $identity);
        $this->assertSame($identity, $this->plugin->__invoke());
    }
}
