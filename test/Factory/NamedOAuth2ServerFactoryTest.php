<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\MvcAuth\Factory;

use Laminas\ApiTools\MvcAuth\Authentication\OAuth2Adapter;
use Laminas\ApiTools\MvcAuth\Factory\NamedOAuth2ServerFactory;
use Laminas\ServiceManager\ServiceManager;
use OAuth2\Storage\Memory as MemoryStorage;
use PHPUnit\Framework\TestCase;

class NamedOAuth2ServerFactoryTest extends TestCase
{
    protected ServiceManager $services;
    protected NamedOAuth2ServerFactory $factory;

    public function setUp(): void
    {
        $this->services = $this->setUpConfig(new ServiceManager());
        $this->factory  = new NamedOAuth2ServerFactory();
    }

    public function setUpConfig(ServiceManager $services): ServiceManager
    {
        $services->setService('config', [
            'api-tools-oauth2'   => [
                'storage'                    => 'LaminasTest\ApiTools\OAuth2\TestAsset\MockAdapter',
                'grant_types'                => [
                    'client_credentials' => true,
                    'authorization_code' => true,
                    'password'           => true,
                    'refresh_token'      => true,
                    'jwt'                => true,
                ],
                'api_problem_error_response' => true,
            ],
            'api-tools-mvc-auth' => [
                'authentication' => [
                    'adapters' => [
                        'test'  => [
                            'adapter' => OAuth2Adapter::class,
                            'storage' => [
                                'storage' => 'LaminasTest\ApiTools\OAuth2\TestAsset\MockAdapter',
                                'route'   => 'test',
                            ],
                        ],
                        'test2' => [
                            'adapter' => OAuth2Adapter::class,
                            'storage' => [
                                'storage' => 'LaminasTest\ApiTools\OAuth2\TestAsset\MockAdapter',
                                'route'   => 'test2',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $oauth2StorageAdapter = $this->getMockBuilder(MemoryStorage::class)
            ->disableOriginalConstructor(true)
            ->getMock();

        $services->setService(
            'LaminasTest\ApiTools\OAuth2\TestAsset\MockAdapter',
            $oauth2StorageAdapter
        );
        return $services;
    }

    public function testCallingReturnedFactoryMultipleTimesWithNoArgumentReturnsSameServerInstance(): void
    {
        $factory = $this->factory->__invoke($this->services);
        $server  = $factory();
        $this->assertSame($server, $factory());
    }

    public function testCallingReturnedFactoryMultipleTimesWithSameArgumentReturnsSameServerInstance(): void
    {
        $factory = $this->factory->__invoke($this->services);
        $server  = $factory('test');
        $this->assertSame($server, $factory('test'));
    }

    public function testCallingReturnedFactoryMultipleTimesWithDifferentArgumentsReturnsDifferentInstances(): void
    {
        $factory = $this->factory->__invoke($this->services);
        $server  = $factory('test');
        $this->assertNotSame($server, $factory());
        $this->assertNotSame($server, $factory('test2'));
    }

    public function testCallingReturnedFactoryWithUnrecognizedArgumentReturnsApplicationWideInstance(): void
    {
        $factory = $this->factory->__invoke($this->services);
        $server  = $factory();
        $this->assertSame($server, $factory('unknown'));
    }
}
