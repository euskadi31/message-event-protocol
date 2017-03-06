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

class MapTypeDefinition extends TypeDefinition
{
    protected $keyType;

    protected $valueType;

    public function __construct()
    {
        $this->setType('Map');
    }

    public function setKeyType($type)
    {
        $this->keyType = $type;

        return $this;
    }

    public function getKeyType()
    {
        return $this->keyType;
    }

    public function hasKeyType()
    {
        return !empty($this->keyType);
    }

    public function setValueType($type)
    {
        $this->valueType = $type;

        return $this;
    }

    public function getValueType()
    {
        return $this->valueType;
    }

    public function hasValueType()
    {
        return !empty($this->valueType);
    }
}
