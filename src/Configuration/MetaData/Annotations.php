<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Illuminate\Support\Arr;

class Annotations extends MetaData
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param array $settings
     *
     * @return \Doctrine\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        $useSimpleAnnotationReader = Arr::get($settings, 'simple', false);
        $paths                     = Arr::get($settings, 'paths', []);

        $annotationDriverFilename = (new \ReflectionClass(AnnotationDriver::class))->getFileName();
        AnnotationRegistry::registerFile(dirname($annotationDriverFilename) . '/DoctrineAnnotations.php');

        if ($useSimpleAnnotationReader) {
            $reader = new SimpleAnnotationReader();
            $reader->addNamespace('Doctrine\ORM\Mapping');
            $cachedReader = new CachedReader($reader, $this->getCache());

            return new AnnotationDriver($cachedReader, (array) $paths);
        }

        return new AnnotationDriver(new CachedReader(new AnnotationReader(), $this->getCache()), (array) $paths);
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        if ($this->cache === null) {
            $this->cache = new ArrayCache();
        }

        return $this->cache;
    }

    /**
     * @param Cache $cache
     *
     * @return $this
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;

        return $this;

    }
}
