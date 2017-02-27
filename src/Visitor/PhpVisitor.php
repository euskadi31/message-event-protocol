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
use Euskadi31\MessageEventProtocol\Source;

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

class PhpVisitor implements Visit
{
    public function visit(Element $element, &$handle = null, $eldnah = null)
    {
        //var_dump($element->getId());

        switch ($element->getId()) {
            case '#root':
                $content  = '<?php' . PHP_EOL;
                $content .= PHP_EOL;

                foreach ($element->getChildren() as $e => $child) {
                    $content .= $child->accept($this, $handle, $eldnah);
                }

                $handle->setContent($content);

                return $content;
            case '#package':
                return sprintf(
                    'namespace %s;',
                    $element->getChild(0)->accept($this, $handle, $eldnah)
                ) . PHP_EOL . PHP_EOL;
            case '#import':
                $className = $element->getChild(0)->accept($this, $handle, $eldnah);

                if (strpos($className, '\\') !== false) {
                    return sprintf('use %s;', $className) . PHP_EOL;
                }
                break;
            case '#message':
                $class  = 'class ' . $element->getChild(0)->accept($this, $handle, $eldnah) . PHP_EOL;
                $class .= '{' . PHP_EOL;

                foreach ($element->getChildren() as $e => $child) {
                    if ($e == 0) {
                        continue;
                    }

                    $class .= $child->accept($this, $handle, $eldnah);
                }

                $class .= '}' . PHP_EOL;

                return $class;
            case '#property':
                return sprintf(
                    '    private $%s;',
                    $element->getChild(2)->accept($this, $handle, $eldnah)
                ) . PHP_EOL;
            case 'token':
                //var_dump($element->getValueToken());

                switch ($element->getValueToken()) {
                    case 'package_name_t':
                        return $element->getValueValue();
                        break;
                    case 'import_name_t':
                        return $element->getValueValue();
                        break;
                    case 'message_name_t':
                        return $element->getValueValue();
                        break;
                    case 'property_name_t':
                        return $element->getValueValue();
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
