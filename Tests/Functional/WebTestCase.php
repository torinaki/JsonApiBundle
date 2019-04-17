<?php

namespace Mango\Bundle\JsonApiBundle\Tests\Functional;

use Mango\Bundle\JsonApiBundle\Tests\Functional\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Taken from https://github.com/FriendsOfSymfony/FOSElasticaBundle/blob/master/tests/Functional/WebTestCase.php
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class WebTestCase extends BaseKernelTestCase
{
    protected static function getKernelClass()
    {
        return AppKernel::class;
    }

    public static function setUpBeforeClass()
    {
        static::deleteTmpDir();
    }

    public static function tearDownAfterClass()
    {
        static::deleteTmpDir();
    }

    protected static function deleteTmpDir()
    {
        if (!file_exists($dir = sys_get_temp_dir().'/'.static::getVarDir())) {
            return;
        }
        $fs = new Filesystem();
        $fs->remove($dir);
    }

    protected static function createKernel(array $options = [])
    {
        $class = self::getKernelClass();

        if (!isset($options['test_case'])) {
            throw new \InvalidArgumentException('The option "test_case" must be set.');
        }

        return new $class(
            static::getVarDir(),
            $options['test_case'],
            isset($options['root_config']) ? $options['root_config'] : 'config.yml',
            isset($options['environment']) ? $options['environment'] : strtolower(static::getVarDir().$options['test_case']),
            isset($options['debug']) ? $options['debug'] : true
        );
    }

    protected static function getVarDir()
    {
        return substr(strrchr(get_called_class(), '\\'), 1);
    }
}
