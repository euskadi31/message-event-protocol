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

class Ts2Target implements TargetInterface
{
    protected $genericTypes = [
        'String' => 'string',
        'Boolean' => 'boolean',
        'Integer' => 'number',
        'Float' => 'number'
    ];

    protected $filename;

    public function getName()
    {
        return 'ts2';
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function generate(FileDefinition $definition)
    {
        $this->filename = $definition->getSrcFile()->getBasename('.mep') . '.ts';

        $content = '';

        $imports = array_map(function($item) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $item);

            return 'import ' . $item . ' from \'./' . $file. '\';';
        }, $definition->getImports());

        if (!empty($imports)) {
            $content .= implode(PHP_EOL, $imports) . PHP_EOL;
            $content .= PHP_EOL;
        }

        $content .= 'namespace ' . str_replace('\\', '.', $definition->getPackage()) . ' {' . PHP_EOL;
        $content .= PHP_EOL;

        $classes = array_map(function($item) {
            if ($item instanceof MessageDefinition) {
                return $this->generateClass($item);
            } else {
                return $this->generateInterface($item);
            }
        }, $definition->getClasses());

        if (!empty($classes)) {
            $content .= implode(PHP_EOL, $classes) . PHP_EOL;
        }

        $content .= '}' . PHP_EOL;

        return $content;
    }

    protected function generateProperty(PropertyDefinition $definition)
    {
        return '        private ' . $definition->getName() . $this->getType($definition) . ';';
    }

    protected function getType(PropertyDefinition $definition)
    {
        $type = $definition->getType();

        if (isset($this->genericTypes[$type])) {
            $type = $this->genericTypes[$type];
        }

        if (!$definition->isRequired()) {
            return '?: ' . $type;
        } else {
            return ': ' . $type;
        }
    }

    protected function generateParameter(PropertyDefinition $definition)
    {
        return $definition->getName() . $this->getType($definition);
    }

    protected function generateMethod(MessageDefinition $messageDef, PropertyDefinition $propertyDef)
    {
        $name = ucfirst($propertyDef->getName());

        $content  = '        public set' . $name . '(' . $this->generateParameter($propertyDef) . '): ' . $messageDef->getName() . ' {' . PHP_EOL;
        $content .= '            this.' . $propertyDef->getName() . ' = ' . $propertyDef->getName() . ';' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '            return this;' . PHP_EOL;
        $content .= '        }' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '        public get' . $name . '()' . $this->getType($propertyDef) . ' {' . PHP_EOL;
        $content .= '            return this.' . $propertyDef->getName() . ';' . PHP_EOL;
        $content .= '        }';

        return $content;
    }

    protected function generatePrototype(PropertyDefinition $definition)
    {
        $name = ucfirst($definition->getName());

        $content  = '        public set' . $name . '(' . $this->generateParameter($definition) . ');' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '        public get' . $name . '();' . PHP_EOL;

        return $content;
    }

    protected function generateClass(MessageDefinition $definition)
    {
        $content  = '    export class ' . $definition->getName();

        $implements = $definition->getImplementsName();

        if (!empty($implements)) {
            $content .= ' implements ' . $implements;
        }

        $content .= ' {'. PHP_EOL;

        $properties = array_map(function($item) {
            return $this->generateProperty($item);
        }, $definition->getProperties());

        if (!empty($properties)) {
            $content .= implode(PHP_EOL, $properties) . PHP_EOL;
            $content .= PHP_EOL;
        }

        $methods = array_map(function($item) use ($definition) {
            return $this->generateMethod($definition, $item);
        }, $definition->getProperties());

        if (!empty($methods)) {
            $content .= implode(PHP_EOL . PHP_EOL, $methods) . PHP_EOL;
        }

        $content .= '    }';

        return $content;
    }

    protected function generateInterface(InterfaceDefinition $definition)
    {
        $content  = 'interface ' . $definition->getName() . ' {' . PHP_EOL;

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
