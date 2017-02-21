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
namespace Euskadi31\MessageEventProtocol;

use Hoa;
use SplFileInfo;
use InvalidArgumentException;

class Builder
{
    protected $target;

    protected $output;

    public function __construct($target, $output)
    {
        $this->output = $output;

        $className = sprintf('Euskadi31\MessageEventProtocol\Target\\%sTarget', $target);

        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf('Invalid Target "%s".', $target));
        }

        $this->target = new $className();
    }

    public function build(Definition\FileDefinition $definition)
    {
        $content = $this->target->generate($definition);

        $path = implode(DIRECTORY_SEPARATOR, [
            rtrim($this->output, DIRECTORY_SEPARATOR),
            $this->target->getName(),
            str_replace('\\', DIRECTORY_SEPARATOR, $definition->getPackage())
        ]);

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        file_put_contents(
            $path . DIRECTORY_SEPARATOR . $this->target->getFilename(),
            $content
        );
    }


}
