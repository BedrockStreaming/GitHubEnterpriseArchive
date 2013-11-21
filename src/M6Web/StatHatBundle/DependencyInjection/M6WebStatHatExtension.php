<?php

namespace M6Web\StatHatBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class M6WebStatHatExtension
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class M6WebStatHatExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');


        $container->setParameter('m6_web_stat_hat.ez_key', $config['ez_key']);

        $i = 0;
        foreach ($config['counts'] as $countConfig) {
            $definition = new Definition(
                'M6Web\StatHatBundle\Listener\CountEventListener',
                array(
                    new Reference('m6_web_stat_hat.client'),
                    new Reference('m6_web_stat_hat.expression_language'),
                    $countConfig['stat_key'],
                    $countConfig['count'],
                    $countConfig['timestamp']
                )
            );
            $definition->addTag('kernel.event_listener', ['event' => $countConfig['event'], 'method' => 'onEvent']);
            $container->addDefinitions(['m6_web_stat_hat.event_listener.'.$i => $definition]);
            $i++;
        }
    }
}
