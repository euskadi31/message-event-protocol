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
namespace Euskadi31\MessageEventProtocol\Target;

use Euskadi31\MessageEventProtocol\Definition\FileDefinition;
use Euskadi31\MessageEventProtocol\Definition\MessageDefinition;
use Euskadi31\MessageEventProtocol\Definition\InterfaceDefinition;
use Euskadi31\MessageEventProtocol\Definition\PropertyDefinition;

class Php5Target implements TargetInterface
{
    protected $genericTypes = [
        'String',
        'Boolean',
        'Bool',
        'Integer',
        'Float'
    ];

    protected $filename;

    public function getName()
    {
        return 'php5';
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function generate(FileDefinition $definition)
    {
        $this->filename = $definition->getSrcFile()->getBasename('.mep') . '.php';

        $content  = '<?php' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'namespace ' . $definition->getPackage() . ';' . PHP_EOL;
        $content .= PHP_EOL;

        $imports = array_map(function($item) {
            return 'use ' . $item . ';';
        }, $definition->getImports());

        $imports = array_filter($imports, function($item) {
            return strpos($item, '\\') !== false;
        });

        if (!empty($imports)) {
            $content .= implode(PHP_EOL, $imports) . PHP_EOL;
            $content .= PHP_EOL;
        }

        $classes = array_map(function($item) {
            if ($item instanceof MessageDefinition) {
                return $this->generateClass($item);
            } else {
                return $this->generateInterface($item);
            }
        }, $definition->getClasses());

        if (!empty($classes)) {
            $content .= implode(PHP_EOL, $classes);
        }

        return $content;
    }

    protected function generateProperty(PropertyDefinition $definition)
    {
        return '    private $' . $definition->getName() . ';';
    }

    protected function generateParameter(PropertyDefinition $definition)
    {
        $type = '';

        if (!in_array($definition->getType(), $this->genericTypes)) {
            $type = $definition->getType(). ' ';
        }

        return $type . '$' . $definition->getName();
    }

    protected function generateMethod(PropertyDefinition $definition)
    {
        $name = ucfirst($definition->getName());

        $content  = '    public function set' . $name . '(' . $this->generateParameter($definition) . ')' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '        $this->' . $definition->getName() . ' = $' . $definition->getName() . ';' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '        return $this;' . PHP_EOL;
        $content .= '    }' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '    public function get' . $name . '()' . PHP_EOL;
        $content .= '    {' . PHP_EOL;
        $content .= '        return $this->' . $definition->getName() . ';' . PHP_EOL;
        $content .= '    }';

        return $content;
    }

    protected function generatePrototype(PropertyDefinition $definition)
    {
        $name = ucfirst($definition->getName());

        $content  = '    public function set' . $name . '(' . $this->generateParameter($definition) . ');' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '    public function get' . $name . '();' . PHP_EOL;

        return $content;
    }

    protected function generateClass(MessageDefinition $definition)
    {
        $content  = 'class ' . $definition->getName();

        $implements = $definition->getImplementsName();

        if (!empty($implements)) {
            $content .= ' implements ' . $implements;
        }

        $content .= PHP_EOL;

        $content .= '{' . PHP_EOL;

        $properties = array_map(function($item) {
            return $this->generateProperty($item);
        }, $definition->getProperties());

        if (!empty($properties)) {
            $content .= implode(PHP_EOL, $properties) . PHP_EOL;
            $content .= PHP_EOL;
        }

        $methods = array_map(function($item) {
            return $this->generateMethod($item);
        }, $definition->getProperties());

        if (!empty($methods)) {
            $content .= implode(PHP_EOL . PHP_EOL, $methods) . PHP_EOL;
        }

        $content .= '}';

        return $content;
    }

    protected function generateInterface(InterfaceDefinition $definition)
    {
        $content  = 'interface ' . $definition->getName() . PHP_EOL;
        $content .= '{' . PHP_EOL;

        $methods = array_map(function($item) {
            return $this->generatePrototype($item);
        }, $definition->getProperties());

        if (!empty($methods)) {
            $content .= implode(PHP_EOL, $methods) . PHP_EOL;
        }

        $content .= '}' . PHP_EOL;

        return $content;
    }
}
