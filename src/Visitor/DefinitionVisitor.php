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
namespace Euskadi31\MessageEventProtocol\Visitor;

use Hoa\Visitor\Visit;
use Hoa\Visitor\Element;
use Euskadi31\MessageEventProtocol\Definition;

/*
>  #root
>  >  #package
>  >  >  token(package_ns:package_name_t, Acme\Event)
>  >  #import
>  >  >  token(import_ns:import_name_t, PayloadMeta)
>  >  #import
>  >  >  token(import_ns:import_name_t, PayloadData)
>  >  #message
>  >  >  token(message_ns:message_name_t, Payload)
>  >  >  #property
>  >  >  >  token(required_t, required)
>  >  >  >  token(type_t, PayloadMeta)
>  >  >  >  token(property_name_t, meta)
>  >  >  #property
>  >  >  >  token(required_t, required)
>  >  >  >  token(type_t, PayloadData)
>  >  >  >  token(property_name_t, data)
 */

class DefinitionVisitor implements Visit
{
    public function visit(Element $element, &$handle = null, $eldnah = null)
    {
        //var_dump($element->getId());

        switch ($element->getId()) {
            case '#root':
                foreach ($element->getChildren() as $e => $child) {
                    $child->accept($this, $handle, $eldnah);
                }
                break;
            case '#package':
                $element->getChild(0)->accept($this, $handle, $eldnah);
                break;
            case '#import':
                $element->getChild(0)->accept($this, $handle, $eldnah);
                break;
            case '#message':
                $definition = new Definition\MessageDefinition();

                $element->getChild(0)->accept($this, $definition, $eldnah);

                foreach ($element->getChildren() as $e => $child) {
                    if ($e == 0) {
                        continue;
                    }

                    $child->accept($this, $definition, $eldnah);
                }

                $handle->addClass($definition);
                break;
            case '#implements':
                $element->getChild(0)->accept($this, $handle, $eldnah);
                break;
            case '#extend':
                $element->getChild(0)->accept($this, $handle, $eldnah);
                break;
            case '#interface':
                $definition = new Definition\InterfaceDefinition();

                $element->getChild(0)->accept($this, $definition, $eldnah);

                foreach ($element->getChildren() as $e => $child) {
                    if ($e == 0) {
                        continue;
                    }

                    $child->accept($this, $definition, $eldnah);
                }

                $handle->addClass($definition);
                break;
            case '#property':
                $definition = new Definition\PropertyDefinition();

                foreach ($element->getChildren() as $e => $child) {
                    $child->accept($this, $definition, $eldnah);
                }

                $handle->addProperty($definition);
                break;
            case '#options':
                $definition = new Definition\OptionDefinition();

                foreach ($element->getChildren() as $e => $child) {
                    $child->accept($this, $definition, $eldnah);
                }

                $handle->addOption($definition);
                break;
            case '#property_type':
                $definition = new Definition\TypeDefinition();

                foreach ($element->getChildren() as $e => $child) {
                    $child->accept($this, $definition, $eldnah);
                }

                $handle->setType($definition);
                break;
            case '#property_set':
                $definition = new Definition\SetTypeDefinition();

                foreach ($element->getChildren() as $e => $child) {
                    $child->accept($this, $definition, $eldnah);
                }

                $handle->setType($definition);
                break;
            case '#property_map':
                $definition = new Definition\MapTypeDefinition();

                foreach ($element->getChildren() as $e => $child) {
                    $child->accept($this, $definition, $eldnah);
                }

                //var_dump($definition);

                $handle->setType($definition);
                break;
            case 'token':
                // var_dump($element->getValueToken());

                switch ($element->getValueToken()) {
                    case 'package_name_t':
                        $handle->setPackage($element->getValueValue());
                        break;
                    case 'import_name_t':
                        $handle->addImport($element->getValueValue());
                        break;
                    case 'message_name_t':
                        $handle->setName($element->getValueValue());
                        break;
                    case 'interface_name_t':
                        $handle->setName($element->getValueValue());
                        break;
                    case 'required_t':
                        $handle->setRequired(true);
                        break;
                    case 'type_t':
                        $part = explode('\\', get_class($handle));
                        $type = array_pop($part);

                        switch ($type) {
                            case 'TypeDefinition':
                                $handle->setType($element->getValueValue());
                                break;
                            case 'SetTypeDefinition':
                                $handle->setValueType($element->getValueValue());
                                break;
                            case 'MapTypeDefinition':
                                if (!$handle->hasKeyType()) {
                                    $handle->setKeyType($element->getValueValue());
                                }

                                if (!$handle->hasValueType()) {
                                    $handle->setValueType($element->getValueValue());
                                }
                                break;
                        }

                        break;
                    case 'property_name_t':
                        $handle->setName($element->getValueValue());
                        break;
                    case 'implements_name_t':
                        $handle->setImplementsName($element->getValueValue());
                        break;
                    case 'extend_name_t':
                        $handle->setExtend($element->getValueValue());
                        break;
                    case 'option_name_t':
                        $handle->setName($element->getValueValue());
                        break;
                    case 'string_t':
                        $handle->setValue($element->getValueValue());
                        break;
                    default:
                        # code...
                        break;
                }
                break;
            default:
                //var_dump($element);
                break;
        }
    }
}
