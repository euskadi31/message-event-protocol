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

class PropertyDefinition
{
    protected $required;

    protected $type;

    protected $name;

    public function setRequired($required)
    {
        $this->required = (bool) $required;

        return $this;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function setType(TypeDefinition $type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
}
