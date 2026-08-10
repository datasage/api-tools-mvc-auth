<?php

declare(strict_types=1);

namespace Laminas\ApiTools\MvcAuth\Factory;

use Laminas\ApiTools\MvcAuth\Authentication\DefaultAuthenticationListener;
use Laminas\ApiTools\MvcAuth\Authentication\HttpAdapter;
use Laminas\ApiTools\MvcAuth\Authentication\OAuth2Adapter;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Override;
use Psr\Container\ContainerInterface;

use function is_array;
use function is_string;

class AuthenticationAdapterDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * Decorate the DefaultAuthenticationListener.
     *
     * Attaches adapters as listeners if present in configuration.
     *
     * @param  string             $name
     * @param  null|array         $options
     * @return DefaultAuthenticationListener
     */
    #[Override]
    public function __invoke(ContainerInterface $container, $name, callable $callback, ?array $options = null)
    {
        $listener = $callback();

        $config = $container->get('config');
        if (
            ! isset($config['api-tools-mvc-auth']['authentication']['adapters'])
            || ! is_array($config['api-tools-mvc-auth']['authentication']['adapters'])
        ) {
            return $listener;
        }

        foreach ($config['api-tools-mvc-auth']['authentication']['adapters'] as $type => $data) {
            $this->attachAdapterOfType($type, $data, $container, $listener);
        }

        return $listener;
    }

    /**
     * Attach an adaper to the listener as described by $type and $data.
     */
    private function attachAdapterOfType(
        string $type,
        array $adapterConfig,
        ContainerInterface $container,
        DefaultAuthenticationListener $listener
    ): void {
        if (
            ! isset($adapterConfig['adapter'])
            || ! is_string($adapterConfig['adapter'])
        ) {
            return;
        }

        switch ($adapterConfig['adapter']) {
            case HttpAdapter::class:
                $adapter = AuthenticationHttpAdapterFactory::factory($type, $adapterConfig, $container);
                break;
            case OAuth2Adapter::class:
                $adapter = AuthenticationOAuth2AdapterFactory::factory($type, $adapterConfig, $container);
                break;
            default:
                $adapter = false;
                break;
        }

        if (! $adapter) {
            return;
        }

        $listener->attach($adapter);
    }
}
