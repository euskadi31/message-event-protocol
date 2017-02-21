<?php
/*
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

class ClassDefinition
{
    protected $name;

    protected $properties = [];

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addProperty(PropertyDefinition $property)
    {
        $this->properties[] = $property;

        return $this;
    }

    public function getProperties()
    {
        return $this->properties;
    }
}
