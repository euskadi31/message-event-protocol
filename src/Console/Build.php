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
namespace Euskadi31\MessageEventProtocol\Console;

use Hoa\Console;
use SplFileInfo;
use Euskadi31\MessageEventProtocol\Parser;
use Euskadi31\MessageEventProtocol\Builder;

class Build extends Console\Dispatcher\Kit
{
    /**
     * Options description.
     *
     * @var array
     */
    protected $options = [
        ['target',  Console\GetOption::REQUIRED_ARGUMENT, 't'],
        ['out',     Console\GetOption::REQUIRED_ARGUMENT, 'o'],
        ['help',    Console\GetOption::NO_ARGUMENT,       'h'],
        ['help',    Console\GetOption::NO_ARGUMENT,       '?']
    ];

    /**
     * The entry method.
     *
     * @return  int
     */
    public function main()
    {
        $target = null;

        $output = null;

        while (false !== $c = $this->getOption($v)) {
            switch ($c) {
                case 't':
                    $target = $v;
                    break;
                case 'o':
                    $output = $v;
                    break;

                case '__ambiguous':
                    $this->resolveOptionAmbiguity($v);

                    break;

                case 'h':
                case '?':
                default:
                    return $this->usage();
            }
        }

        $files = array_map(function($file) {
            return new SplFileInfo($file);
        }, $this->parser->getInputs());

        $files = array_filter($files, function($file) {
            return $file->getExtension() == 'mep';
        });

        if (empty($files)) {
            return $this->usage();
        }

        $builder = new Builder($target, $output);

        $parser = new Parser();
        $definitions = $parser->parse($files);

        foreach ($definitions as $definition) {
            $builder->build($definition);
        }
    }

    /**
     * The command usage.
     *
     * @return  int
     */
    public function usage()
    {
        echo
            'Usage   : build <options> [Message.mep]', PHP_EOL,
            'Options :', PHP_EOL,
            $this->makeUsageOptionsList([
                't'    => 'Target name (“php5”, “php7”, “es3”, “es5”, “es6”, “ts2”, “java8” and “go” is supported).',
                'help' => 'This help.'
            ]), PHP_EOL;

        return;
    }
}
