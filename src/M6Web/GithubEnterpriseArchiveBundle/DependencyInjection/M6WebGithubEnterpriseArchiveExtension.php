<?php

namespace M6Web\GithubEnterpriseArchiveBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class M6WebGithubEnterpriseArchiveExtension
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class M6WebGithubEnterpriseArchiveExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('m6_web_github_enterprise_archive.base_url', $config['base_url']);
        $container->setParameter('m6_web_github_enterprise_archive.data_dir', $config['data_dir']);
    }
}
