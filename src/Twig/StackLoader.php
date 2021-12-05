<?php

declare(strict_types=1);

namespace ZfcTwig\Twig;

use Twig\Error;
use Twig\Loader;

use function explode;
use function implode;
use function is_file;
use function ltrim;
use function pathinfo;
use function preg_replace;
use function sprintf;
use function strpos;
use function strtr;
use function substr;

use const PATHINFO_EXTENSION;

class StackLoader extends Loader\FilesystemLoader
{
    /**
     * Default suffix to use
     *
     * Appends this suffix if the template requested does not use it.
     *
     * @var string
     */
    protected $defaultSuffix = 'twig';

    /**
     * Set default file suffix
     *
     * @return StackLoader
     */
    public function setDefaultSuffix(string $defaultSuffix)
    {
        $this->defaultSuffix = (string) $defaultSuffix;
        $this->defaultSuffix = ltrim($this->defaultSuffix, '.');

        return $this;
    }

    /**
     * Get default file suffix
     */
    public function getDefaultSuffix(): string
    {
        return $this->defaultSuffix;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Error\LoaderError
     */
    protected function findTemplate(string $name, bool $throw = true): ?string
    {
        $name = (string) $name;

        // normalize name
        $name = preg_replace('#/{2,}#', '/', strtr($name, '\\', '/'));

        // Ensure we have the expected file extension
        $defaultSuffix = $this->getDefaultSuffix();
        if (pathinfo($name, PATHINFO_EXTENSION) !== $defaultSuffix) {
            $name .= '.' . $defaultSuffix;
        }

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (isset($this->errorCache[$name])) {
            if (! $throw) {
                return $this->cache[$name] = null;
            }
            throw new Error\LoaderError($this->errorCache[$name]);
        }

        $this->validateName($name);

        $namespace = '__main__';
        if (isset($name[0]) && '@' === $name[0]) {
            if (false === $pos = strpos($name, '/')) {
                $this->errorCache[$name] = sprintf(
                    'Malformed namespaced template name "%s" (expecting "@namespace/template_name").',
                    $name
                );

                if (! $throw) {
                    return $this->cache[$name] = null;
                }

                throw new Error\LoaderError($this->errorCache[$name]);
            }

            $namespace = substr($name, 1, $pos - 1);

            $name = substr($name, $pos + 1);
        }

        if (! isset($this->paths[$namespace])) {
            $this->errorCache[$name] = sprintf('There are no registered paths for namespace "%s".', $namespace);

            if (! $throw) {
                return $this->cache[$name] = null;
            }
            throw new Error\LoaderError($this->errorCache[$name]);
        }

        foreach ($this->paths[$namespace] as $path) {
            if (is_file($path . '/' . $name)) {
                return $this->cache[$name] = $path . '/' . $name;
            }
        }

        $this->errorCache[$name] = sprintf(
            'Unable to find template "%s" (looked into: %s).',
            $name,
            implode(
                ', ',
                $this->paths[$namespace]
            )
        );

        if (! $throw) {
            return $this->cache[$name] = null;
        }

        throw new Error\LoaderError($this->errorCache[$name]);
    }

    /**
     * @throws Error\LoaderError
     */
    private function validateName(string $name): void
    {
        if (false !== strpos($name, "\0")) {
            throw new Error\LoaderError('A template name cannot contain NUL bytes.');
        }

        $name  = ltrim($name, '/');
        $parts = explode('/', $name);
        $level = 0;
        foreach ($parts as $part) {
            if ('..' === $part) {
                --$level;
            } elseif ('.' !== $part) {
                ++$level;
            }

            if ($level < 0) {
                throw new Error\LoaderError(
                    sprintf(
                        'Looks like you try to load a template outside configured directories (%s).',
                        $name
                    )
                );
            }
        }
    }
}
