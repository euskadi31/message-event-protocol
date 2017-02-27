<?php
/**
 * This file is part of the message-event-protocol.
 *
 * (c) Axel Etcheverry
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @namespace
 */
namespace Euskadi31\MessageEventProtocol\Definition;

use SplFileInfo;

class FileDefinition
{
    protected $src;

    protected $dist;

    protected $package;

    protected $imports = [];

    protected $classes = [];

    protected $options = [];

    public function setSrcFile(SplFileInfo $src)
    {
        $this->src = $src;

        return $this;
    }

    public function getSrcFile()
    {
        return $this->src;
    }

    public function setDistFile(SplFileInfo $dist)
    {
        $this->dist = $dist;

        return $this;
    }

    public function getDistFile()
    {
        return $this->dist;
    }

    public function setPackage($package)
    {
        $this->package = $package;

        return $this;
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function addImport($name)
    {
        $this->imports[] = $name;

        return $this;
    }

    public function getImports()
    {
        return $this->imports;
    }

    public function addClass(ClassDefinition $class)
    {
        $this->classes[] = $class;

        return $this;
    }

    public function getClasses()
    {
        return $this->classes;
    }

    public function addOption(OptionDefinition $option)
    {
        $this->options[$option->getName()] = $option->getValue();

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    public function getOption($name, $default = null)
    {
        if ($this->hasOption($name)) {
            return $this->options[$name];
        }

        return $default;
    }
}
