<?php

declare(strict_types=1);

namespace ZfcTwig\Twig;

use Twig\Error;
use Twig\Loader;
use Twig\Source;

use function array_key_exists;
use function file_exists;
use function file_get_contents;
use function filemtime;
use function sprintf;

class MapLoader implements Loader\LoaderInterface
{
    /**
     * Array of templates to filenames.
     *
     * @var array
     */
    protected $map = [];

    /**
     * Add to the map.
     *
     * @throws Error\LoaderError
     */
    public function add(string $name, string $path): MapLoader
    {
        if ($this->exists($name)) {
            throw new Error\LoaderError(sprintf(
                'Name "%s" already exists in map',
                $name
            ));
        }
        $this->map[$name] = $path;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $name): bool
    {
        return array_key_exists($name, $this->map);
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceContext(string $name): Source
    {
        if (! $this->exists($name)) {
            throw new Error\LoaderError(sprintf(
                'Unable to find template "%s" from template map',
                $name
            ));
        }
        if (! file_exists($this->map[$name])) {
            throw new Error\LoaderError(sprintf(
                'Unable to open file "%s" from template map',
                $this->map[$name]
            ));
        }
        return new Source(file_get_contents($this->map[$name]), $name, $this->map[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheKey(string $name): string
    {
        return $name;
    }

    /**
     * {@inheritDoc}
     */
    public function isFresh(string $name, int $time): bool
    {
        return filemtime($this->map[$name]) <= $time;
    }
}
