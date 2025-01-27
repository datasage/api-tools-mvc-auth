<?php

declare(strict_types=1);

namespace Laminas\ApiTools\MvcAuth\Authorization;

use function array_key_exists;
use function is_array;

// phpcs:ignore WebimpressCodingStandard.NamingConventions.AbstractClass.Prefix
abstract class AclAuthorizationFactory
{
    /**
     * Create and return an AclAuthorization instance populated with provided privileges.
     *
     * @return AclAuthorization
     */
    public static function factory(array $config)
    {
        // Determine whether we are whitelisting or blacklisting
        $denyByDefault = false;
        if (array_key_exists('deny_by_default', $config)) {
            $denyByDefault = (bool) $config['deny_by_default'];
            unset($config['deny_by_default']);
        }

        // By default, create an open ACL
        $acl = new AclAuthorization();
        $acl->addRole('guest');
        $acl->allow();

        $grant = 'deny';
        if ($denyByDefault) {
            $acl->deny('guest', null, null);
            $grant = 'allow';
        }

        if (! empty($config)) {
            return self::injectGrants($acl, $grant, $config);
        }

        return $acl;
    }

    /**
     * Inject the ACL with the grants specified in the collection of rules.
     *
     * @param string $grantType Either "allow" or "deny".
     * @return AclAuthorization
     */
    private static function injectGrants(AclAuthorization $acl, $grantType, array $rules)
    {
        foreach ($rules as $set) {
            if (! is_array($set) || ! isset($set['resource'])) {
                continue;
            }

            self::injectGrant($acl, $grantType, $set);
        }

        return $acl;
    }

    /**
     * Inject the ACL with the grant specified by a single rule set.
     *
     * @param string $grantType
     * @return void
     */
    private static function injectGrant(AclAuthorization $acl, $grantType, array $ruleSet)
    {
        // Add new resource to ACL
        $resource = $ruleSet['resource'];
        $acl->addResource($ruleSet['resource']);

        // Deny guest specified privileges to resource
        $privileges = $ruleSet['privileges'] ?? null;

        // null privileges means no permissions were setup; nothing to do
        if (null === $privileges) {
            return;
        }

        $acl->$grantType('guest', $resource, $privileges);
    }
}
