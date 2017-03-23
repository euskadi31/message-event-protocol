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

class GoTarget implements TargetInterface
{
    protected $genericTypes = [
        'String' => 'string',
        'Boolean' => 'bool',
        'Integer' => 'int',
        'Float' => 'float64',
        'DateTime' => 'DateTime',
        'Date' => 'Date',
        'Any' => 'interface{}'
    ];

    protected $imports = [
        'DateTime' => 'time',
        'Any' => 'encoding/json'
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

    public function getLibrary()
    {
        return <<<EOF
// ISO8601 format
const dateTimeFormat = "2006-01-02T15:04:05-0700"

// DateTime represents a date ISO-8601
type DateTime struct {
	time.Time
}

// MarshalText implement the json.Marshaler interface
func (t DateTime) MarshalText() ([]byte, error) {
	return []byte(t.Format(dateTimeFormat)), nil
}

// MarshalJSON implement the json.Marshaler interface
func (t DateTime) MarshalJSON() ([]byte, error) {
    if t.IsZero() {
        return []byte("null"), nil
    }

	b, err := t.MarshalText()
	if err != nil {
		return b, err
	}

	dt := []byte{}
	dt = append(dt, 0x22) // 0x22 => "
	dt = append(dt, b...)
	dt = append(dt, 0x22) // 0x22 => "

	return dt, nil
}

// UnmarshalJSON allows ISO8601Time to implement the json.Unmarshaler interface
func (t *DateTime) UnmarshalJSON(b []byte) error {
	if b[0] == '"' && b[len(b)-1] == '"' {
		b = b[1 : len(b)-1]
	}

	return t.UnmarshalText(b)
}

// UnmarshalText allows ISO8601Time to implement the TextUnmarshaler interface
func (t *DateTime) UnmarshalText(b []byte) error {
	var err error

	if string(b) == "null" {
		t.Time = time.Time{}

		return nil
	}

	t.Time, err = time.Parse(dateTimeFormat, string(b))

	return err
}

// ISO8601 format
const dateFormat = "2006-01-02"

// Date represents a date ISO-8601
type Date struct {
	time.Time
}

// MarshalText implement the json.Marshaler interface
func (t Date) MarshalText() ([]byte, error) {
	return []byte(t.Format(dateFormat)), nil
}

// MarshalJSON implement the json.Marshaler interface
func (t Date) MarshalJSON() ([]byte, error) {
    if t.IsZero() {
        return []byte("null"), nil
    }

	b, err := t.MarshalText()
	if err != nil {
		return b, err
	}

	dt := []byte{}
	dt = append(dt, 0x22) // 0x22 => "
	dt = append(dt, b...)
	dt = append(dt, 0x22) // 0x22 => "

	return dt, nil
}

// UnmarshalJSON allows ISO8601Time to implement the json.Unmarshaler interface
func (t *Date) UnmarshalJSON(b []byte) error {
	if b[0] == '"' && b[len(b)-1] == '"' {
		b = b[1 : len(b)-1]
	}

	return t.UnmarshalText(b)
}

// UnmarshalText allows ISO8601Time to implement the TextUnmarshaler interface
func (t *Date) UnmarshalText(b []byte) error {
	var err error

	if string(b) == "null" {
		t.Time = time.Time{}

		return nil
	}

	t.Time, err = time.Parse(dateFormat, string(b))

	return err
}

EOF;

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

        $imports = [
            'time'
        ];

        foreach ($definition->getClasses() as $class) {
            $imports = array_unique(array_merge($imports, array_filter(array_map(function($property) {
                if (isset($this->imports[$property->getType()->getType()])) {
                    return $this->imports[$property->getType()->getType()];
                }

                return null;
            }, $class->getProperties()))));
        }


        $imports = array_map(function($import) {
            return 'import "' . $import . '"';
        }, $imports);


        if (!empty($imports)) {
            $content .= implode(PHP_EOL, $imports) . PHP_EOL;
            $content .= PHP_EOL;
        }

        $content .= $this->getLibrary() . PHP_EOL;

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

    protected function getType($type)
    {
        if (isset($this->genericTypes[$type])) {
            $type = $this->genericTypes[$type];
        } else {
            $type = '*' . $type;
        }

        return $type;
    }

    protected function generateType(TypeDefinition $definition)
    {
        $type = $definition->getType();

        if ($type == 'Set') {
            return sprintf('[]%s', $this->getType($definition->getValueType()));
        } elseif ($type == 'Map') {
            return sprintf('map[%s]%s', $this->getType($definition->getKeyType()), $this->getType($definition->getValueType()));
        }

        return $this->getType($type);

    }

    protected function generateProperty(PropertyDefinition $definition)
    {
        $type = $this->generateType($definition->getType());

        $naming = new NamingPolicy($definition->getName());

        $content  = str_repeat(' ', 4) . $naming->toCamelCase(true) . ' ' . $type;

        $content .= ' `json:"' . $naming->toSnakeCase();

        if (!$definition->isRequired()) {
            $content .= ',omitempty';
        }

        $content .= '"`';

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
            $content .= implode(PHP_EOL, $properties) . PHP_EOL;
        }

        $content .= '}';

        $methods = $this->generateMethods($definition);

        if (!empty($methods)) {
            $content .= PHP_EOL;
            $content .= PHP_EOL;

            $content .= $methods;
        }

        return $content;
    }

    protected  function generateMethods(MessageDefinition $definition)
    {
        $name = $definition->getName();
        $self = strtolower($name[0]);

        $methods = array_filter(array_map(function($property) use ($self, $name) {
            if ($property->getType()->getType() == 'Any') {
                $naming = new NamingPolicy($property->getName());

                $content  = sprintf('func (%s %s) Decode%s(v interface{}) error {', $self, $name, $naming->toCamelCase(true)) . PHP_EOL;
                $content .= sprintf('    b, err := json.Marshal(e.%s)', $naming->toCamelCase(true)) . PHP_EOL;
                $content .= '    if err != nil {' . PHP_EOL;
                $content .= '        return err' . PHP_EOL;
                $content .= '    }' . PHP_EOL;
                $content .= PHP_EOL;
                $content .= '    if err := json.Unmarshal(b, v); err != nil {' . PHP_EOL;
                $content .= '        return err' . PHP_EOL;
                $content .= '    }' . PHP_EOL;
                $content .= PHP_EOL;
                $content .= '    return nil' . PHP_EOL;
                $content .= '}' . PHP_EOL;

                return $content;
            }

            return null;
        }, $definition->getProperties()));

        return implode(PHP_EOL, $methods);
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
