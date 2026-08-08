<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\MvcAuth\Factory;

use Laminas\ApiTools\MvcAuth\Authentication\OAuth2Adapter;
use Laminas\ApiTools\MvcAuth\Factory\AuthenticationOAuth2AdapterFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class AuthenticationOAuth2AdapterFactoryTest extends TestCase
{
    protected ServiceLocatorInterface&Stub $services;

    #[Override]
    public function setUp(): void
    {
        $this->services = $this->createStub(ServiceLocatorInterface::class);
    }

    /** @psalm-return array<string, array{0: array<array-key, mixed>}> */
    public static function invalidConfiguration(): array
    {
        return [
            'empty'  => [[]],
            'null'   => [['storage' => null]],
            'bool'   => [['storage' => true]],
            'int'    => [['storage' => 1]],
            'float'  => [['storage' => 1.1]],
            'string' => [['storage' => 'options']],
            'object' => [['storage' => (object) ['storage']]],
        ];
    }

    /**
     * @psalm-param array<array-key, mixed> $config
     */
    #[DataProvider('invalidConfiguration')]
    public function testRaisesExceptionForMissingOrInvalidStorage(array $config): void
    {
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Missing storage');
        AuthenticationOAuth2AdapterFactory::factory('foo', $config, $this->services);
    }

    public function testCreatesInstanceFromValidConfiguration(): void
    {
        // This is the only test that asserts on how the factory queries the container,
        // so it is the only one that needs a mock rather than the shared stub.
        $services       = $this->createMock(ServiceLocatorInterface::class);
        $this->services = $services;

        $config = [
            'adapter' => 'pdo',
            'storage' => [
                'adapter' => 'pdo',
                'dsn'     => 'sqlite::memory:',
            ],
        ];

        $services->expects($this->atLeastOnce())
            ->method('get')
            ->with($this->stringContains('Config'))
            ->willReturn([
                'api-tools-oauth2' => [
                    'grant_types'                => [
                        'client_credentials' => true,
                        'authorization_code' => true,
                        'password'           => true,
                        'refresh_token'      => true,
                        'jwt'                => true,
                    ],
                    'api_problem_error_response' => true,
                ],
            ]);

        $adapter = AuthenticationOAuth2AdapterFactory::factory('foo', $config, $this->services);
        $this->assertInstanceOf(OAuth2Adapter::class, $adapter);
        $this->assertEquals(['foo'], $adapter->provides());
    }
}
