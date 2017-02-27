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
use Euskadi31\MessageEventProtocol\Definition\MessageDefinition;
use Euskadi31\MessageEventProtocol\Definition\InterfaceDefinition;
use Euskadi31\MessageEventProtocol\Definition\PropertyDefinition;
use Euskadi31\MessageEventProtocol\NamingPolicy;

class GoTarget implements TargetInterface
{
    protected $genericTypes = [
        'String' => 'string',
        'Boolean' => 'boolean',
        'Integer' => 'int',
        'Float' => 'float64',
        'DateTime' => 'time.Time'
    ];

    protected $imports = [
        'DateTime' => 'time'
    ];

    protected $filename;

    protected $def;

    public function getName()
    {
        return 'go';
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function generate(FileDefinition $definition)
    {
        $this->def = $definition;
        $this->filename = $definition->getSrcFile()->getBasename('.mep') . '.go';

        $content  = '// Code generated by message-event-protocol' . PHP_EOL;
        $content .= '// source: ' . (string) $definition->getSrcFile() . PHP_EOL;
        $content .= '// DO NOT EDIT!' . PHP_EOL;
        $content .= PHP_EOL;

        $package = explode('\\', $definition->getPackage());

        $content .= 'package ' . $package[count($package)-1] . PHP_EOL;
        $content .= PHP_EOL;

        $imports = [];

        foreach ($definition->getClasses() as $class) {
            $imports = array_merge($imports, array_unique(array_filter(array_map(function($property) {
                if (isset($this->imports[$property->getType()])) {
                    return 'import "' . $this->imports[$property->getType()] . '"';
                }

                return null;
            }, $class->getProperties()))));
        }

        if (!empty($imports)) {
            $content .= implode(PHP_EOL, $imports) . PHP_EOL;
            $content .= PHP_EOL;
        }

        if ($definition->getOption('go_extends') == 'no') {
            $defClass = [];

            foreach ($definition->getClasses() as $class) {
                $defClass[$class->getName()] = $class;
            }

            foreach ($definition->getClasses() as $class) {
                $extend = $class->getExtend();
                if (!empty($extend) && isset($defClass[$extend])) {
                    $class->merge($defClass[$extend]);
                }
            }
        }

        $classes = array_map(function($item) {
            if ($item instanceof MessageDefinition) {
                return $this->generateClass($item) . PHP_EOL;
            } else {
                return $this->generateInterface($item). PHP_EOL;
            }
        }, $definition->getClasses());

        if (!empty($classes)) {
            $content .= implode(PHP_EOL, $classes);
        }

        return $content;
    }

    protected function generateProperty(PropertyDefinition $definition)
    {
        $type = $definition->getType();

        if (isset($this->genericTypes[$type])) {
            $type = $this->genericTypes[$type];
        }

        $naming = new NamingPolicy($definition->getName());

        $content  = '    ' . $naming->toCamelCase(true) . ' ' . $type;

        $content .= ' `json:"' . $naming->toSnakeCase();

        if (!$definition->isRequired()) {
            $content .= ',omitempty';
        }

        $content .= '"`' . PHP_EOL;

        return $content;
    }

    protected function generateClass(MessageDefinition $definition)
    {
        $content  = 'type ' . $definition->getName() . ' struct {' . PHP_EOL;

        if ($this->def->getOption('go_extends') != 'no') {
            $extend = $definition->getExtend();

            if (!empty($extend)) {
                $content .= '    *' . $extend . PHP_EOL;
            }
        }

        $properties = array_map(function($item) {
            return $this->generateProperty($item);
        }, $definition->getProperties());

        if (!empty($properties)) {
            $content .= implode(PHP_EOL, $properties);
        }

        $content .= '}';

        return $content;
    }

    protected function generateInterface(InterfaceDefinition $definition)
    {
        $content  = 'type ' . $definition->getName() . ' interface {' . PHP_EOL;

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
