<?php

namespace Cravler\MaxMindGeoIpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('cravler_max_mind_geo_ip');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('cravler_max_mind_geo_ip');
        }

        $rootNode
            ->children()
                ->arrayNode('client')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('user_id')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('license_key')
                            ->defaultValue(null)
                        ->end()
                        ->arrayNode('options')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('path')
                    ->defaultValue('%kernel.root_dir%/Resources/MaxMind')
                ->end()
                ->arrayNode('db')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('country')
                            ->cannotBeEmpty()
                            ->defaultValue('GeoIP2-Country.mmdb')
                        ->end()
                        ->scalarNode('city')
                            ->cannotBeEmpty()
                            ->defaultValue('GeoIP2-City.mmdb')
                        ->end()
                        ->scalarNode('asn')
                            ->cannotBeEmpty()
                            ->defaultValue('GeoIP2-ASN.mmdb')
                        ->end()
                        ->scalarNode('connection_type')
                            ->cannotBeEmpty()
                            ->defaultValue('GeoIP2-Connection-Type.mmdb')
                        ->end()
                        ->scalarNode('domain')
                            ->cannotBeEmpty()
                            ->defaultValue('GeoIP2-Domain.mmdb')
                        ->end()
                        ->scalarNode('isp')
                            ->cannotBeEmpty()
                            ->defaultValue('GeoIP2-ISP.mmdb')
                        ->end()
                        ->scalarNode('anonymous_ip')
                            ->cannotBeEmpty()
                            ->defaultValue('GeoIP2-Anonymous-IP.mmdb')
                        ->end()
                        ->scalarNode('enterprise')
                            ->cannotBeEmpty()
                            ->defaultValue('GeoIP2-Enterprise.mmdb')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('source')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('country')
                            ->defaultValue('https://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.tar.gz')
                        ->end()
                        ->scalarNode('city')
                            ->defaultValue('https://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz')
                        ->end()
                        ->scalarNode('asn')
                            ->defaultValue('https://geolite.maxmind.com/download/geoip/database/GeoLite2-ASN.tar.gz')
                        ->end()
                        ->scalarNode('connection_type')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('domain')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('isp')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('anonymous_ip')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('enterprise')
                            ->defaultValue(null)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('md5_check')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('country')
                            ->defaultValue('https://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.tar.gz.md5')
                        ->end()
                        ->scalarNode('city')
                            ->defaultValue('https://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz.md5')
                        ->end()
                        ->scalarNode('asn')
                            ->defaultValue('https://geolite.maxmind.com/download/geoip/database/GeoLite2-ASN.tar.gz.md5')
                        ->end()
                        ->scalarNode('connection_type')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('domain')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('isp')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('anonymous_ip')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('enterprise')
                            ->defaultValue(null)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
