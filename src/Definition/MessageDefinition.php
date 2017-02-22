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

class MessageDefinition extends ClassDefinition
{
    protected $implements;

    public function setImplementsName($name)
    {
        $this->implements = $name;

        return $this;
    }

    public function getImplementsName()
    {
        return $this->implements;
    }
}
