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
namespace Euskadi31\MessageEventProtocol;


class NamingPolicy
{
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function toCamelCase($capitalizeFirstCharacter = false)
    {
        $name = str_replace('-', '', ucwords($this->name, '_'));

        if (!$capitalizeFirstCharacter) {
            $name = lcfirst($name);
        }

        return $name;
    }

    public function toSnakeCase()
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $this->name, $matches);

        $ret = $matches[0];

        foreach ($ret as &$match) {
            $match = ($match == strtoupper($match)) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }
}
