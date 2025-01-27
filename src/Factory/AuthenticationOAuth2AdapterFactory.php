<?php

declare(strict_types=1);

namespace Laminas\ApiTools\MvcAuth\Factory;

use Laminas\ApiTools\MvcAuth\Authentication\OAuth2Adapter;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Psr\Container\ContainerInterface;

use function is_array;

final class AuthenticationOAuth2AdapterFactory
{
    /**
     * Intentionally empty and private to prevent instantiation
     */
    private function __construct()
    {
    }

    /**
     * Create and return an OAuth2Adapter instance.
     *
     * @param string|array $type
     * @param ContainerInterface $services
     * @return OAuth2Adapter
     */
    public static function factory($type, array $config, ContainerInterface $container)
    {
        if (! isset($config['storage']) || ! is_array($config['storage'])) {
            throw new ServiceNotCreatedException('Missing storage details for OAuth2 server');
        }

        return new OAuth2Adapter(
            OAuth2ServerFactory::factory($config['storage'], $container),
            $type
        );
    }
}
