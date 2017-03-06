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
use Euskadi31\MessageEventProtocol\Definition\TypeDefinition;
use Euskadi31\MessageEventProtocol\NamingPolicy;

class Php5Target implements TargetInterface
{
    protected $genericTypes = [
        'String',
        'Boolean',
        'Bool',
        'Integer',
        'Float',
        'DateTime',
        'Date',
        'Any'
    ];

    protected $filename;

    protected $def;

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
        $this->def = $definition;

        $this->filename = $definition->getSrcFile()->getBasename('.mep') . '.php';

        $content  = '<?php' . PHP_EOL;
        $content .= '// Code generated by message-event-protocol' . PHP_EOL;
        $content .= '// source: ' . (string) $definition->getSrcFile() . PHP_EOL;
        $content .= '// DO NOT EDIT!' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= PHP_EOL;
        $content .= 'namespace ' . $definition->getPackage() . ';' . PHP_EOL;
        $content .= PHP_EOL;

        $uses = $definition->getImports();
        if ($this->def->getOption('php_serializer') == 'jms') {
            $uses[] = 'JMS\Serializer\Annotation\SerializedName';
            $uses[] = 'JMS\Serializer\Annotation\Type';
        }

        $imports = array_map(function($item) {
            return 'use ' . $item . ';';
        }, $uses);

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
        $naming = new NamingPolicy($definition->getName());

        $content = '';

        if ($this->def->getOption('php_serializer') == 'jms') {
            $content .= '    /**' . PHP_EOL;
            $content .= '     * @SerializedName("' . $naming->toSnakeCase() . '")' . PHP_EOL;

            if ($definition->getType()->getType() == 'DateTime') {
                $content .= '     * @Type("DateTime<Y-m-d\TH:i:sO>")' . PHP_EOL;
            }

            $content .= '     */' . PHP_EOL;
        }

        $content .= '    private $' . $definition->getName() . ';' . PHP_EOL;

        return $content;
    }

    protected  function generateType(TypeDefinition $definition)
    {
        $type = '';

        //var_dump($definition->getType());

        if ($definition->getType() == 'Map' || $definition->getType() == 'Set') {
            return 'array ';
        }

        if (!in_array($definition->getType(), $this->genericTypes)) {
            $type = $definition->getType() . ' ';
        }

        if ($definition->getType() == 'DateTime') {
            $type = '\DateTime ';
        } elseif ($definition->getType() == 'Date') {
            $type = '\DateTime ';
        }

        return $type;
    }

    protected function generateParameter(PropertyDefinition $definition)
    {
        $type = $this->generateType($definition->getType());

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

        $extend = $definition->getExtend();

        if (!empty($extend)) {
            $content .= ' extends ' . $extend;
        }

        $implements = ['\JsonSerializable'];

        $implementName = $definition->getImplementsName();

        if (!empty($implementName)) {
            $implements[] = $implementName;
        }

        if (!empty($implements)) {
            $content .= ' implements ' . join(', ', $implements);
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

        $content .= PHP_EOL;

        $content .= $this->generateSerializeMethod($definition);

        $content .= '}' . PHP_EOL;

        return $content;
    }

    protected  function  generateSerializeMethod(MessageDefinition $definition)
    {
        $fields = array_map(function($property) {
            $naming = new NamingPolicy($property->getName());

            $ref = sprintf('$this->%s', $property->getName());

            if ($property->getType() == 'DateTime') {
                $ref = sprintf('(empty($this->%s)) ? null : $this->%s->format(\DateTime::ISO8601)', $property->getName(), $property->getName());
            } elseif ($property->getType() == 'Date') {
                $ref = sprintf('(empty($this->%s)) ? null : $this->%s->format(\'Y-m-d\')', $property->getName(), $property->getName());
            }

            return sprintf('            \'%s\' => %s', $naming->toSnakeCase(), $ref);
        }, $definition->getProperties());



        $content  = '    public function jsonSerialize()' . PHP_EOL;
        $content .= '    {' . PHP_EOL;

        $content .= '        return [' . PHP_EOL;

        $content .= implode(',' . PHP_EOL, $fields) . PHP_EOL;

        $content .= '        ];' . PHP_EOL;

        $content .= '    }' . PHP_EOL;

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
