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

use Hoa;
use SplFileInfo;

class Parser
{
    protected $engine;

    public function __construct()
    {
        $this->engine = Hoa\Compiler\Llk\Llk::load(
            new Hoa\File\Read(__DIR__ . '/MessageEventProtocol.pp')
        );
    }

    public function parseFile(SplFileInfo $file)
    {
        $definition = new Definition\FileDefinition();
        $definition->setSrcFile($file);

        $ast = $this->engine->parse(file_get_contents($file));

        $visitor = new Visitor\DefinitionVisitor();

        $visitor->visit($ast, $definition);

        return $definition;
    }

    public function parse(array $files)
    {
        $definitions = [];

        foreach ($files as $file) {
            $definitions[] = $this->parseFile($file);
        }

        return $definitions;
    }
}
