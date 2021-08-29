<?php

/*
 * This file is part of the Nurschool project.
 *
 * (c) Nurschool <https://github.com/abbarrasa/nurschool>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nurschool\Bundle\NurschoolSendgridBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('nurschool_sendgrid');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('api_key')
            ->isRequired()
            ->end()
            ->booleanNode('disable_delivery')
            ->defaultFalse()
            ->end()
            ->scalarNode('redirect_to')
            ->defaultFalse()
            ->end()
            ->booleanNode('sandbox')
            ->defaultFalse()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}