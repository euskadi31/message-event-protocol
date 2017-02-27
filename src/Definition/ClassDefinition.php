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

    public function hasProperty($name)
    {
        foreach ($this->properties as $property) {
            if ($property->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function merge(ClassDefinition $other)
    {
        $properties = [];

        foreach ($other->getProperties() as $property) {
            if (!$this->hasProperty($property->getName())) {
                $properties[] = $property;
            }
        }

        $this->properties = array_merge($properties, $this->properties);
    }
}
