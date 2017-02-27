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
namespace Euskadi31\MessageEventProtocol\Target;

use Euskadi31\MessageEventProtocol\Definition\FileDefinition;

interface TargetInterface
{
    public function getName();

    public function getFilename();

    public function generate(FileDefinition $definition);
}
