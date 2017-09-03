<?php

namespace Pac;

class Command
{
    /**
     * @var $this
     */
    protected static $instance;

    /**
     * @var array Default options
     */
    protected $config = array(
        'options' => []
    );

    /**
     * Instance
     *
     * @return Command
     */
    public static function instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Parse the args array or string
     *
     * @param string|array $args
     * @return array
     */
    public static function parse($args) {
        return self::instance()->parseCommand($args);
    }

    /**
     * @param array|null $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Add option
     *
     * @param $name
     * @param $option
     *  name        Name for option
     *  aliases     Alias names
     *  type        Option type: string, bool
     * @return $this
     */
    public function addOption($name, $option)
    {
        $this->config['options'][] = array_merge([
            'name' => $name,
            'type' => null
        ], $option);
        return $this;
    }

    /**
     * Parse the args array or string
     *
     * @param string|array $args
     * @return array
     *      program     Program name
     *      options     Options with key
     *      commands    Commands
     */
    public function parseCommand($args)
    {
        if (!isset($this)) {
            return self::instance()->parse($args);
        }

        if (!is_array($args)) {
            $args = explode(" ", $args);
        }

        $optionConfigs = [];
        foreach ($this->config['options'] as $optionKey => $optionConfig) {
            $optionConfigs[$optionConfig['name']] = $optionConfig;
            if (isset($optionConfig['alias'])) {
                $optionConfigs[$optionConfig['alias']] = $optionConfig;
            }
        }

        $program = array_shift($args);
        $commands = [];
        $options = [];

        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];
            $nextArg = isset($args[$i + 1]) ? $args[$i + 1] : null;
            $process = false;
            $key = null;
            $value = null;

            if (static::isLongOption($arg)) {
                $argArr = explode('=', substr($arg, 2));
                $key = $argArr[0];
                $value = isset($argArr[1]) ? $argArr[1] : null;
                $process = true;
            } else if (static::isShortOption($arg)) {
                $key = $arg[1];
                $value = substr($arg, 2);
                $process = true;
            }

            if ($process) {
                $optionConfig = isset($optionConfigs[$key]) ? $optionConfigs[$key] : [
                    'name' => $key,
                    'type' => null
                ];

                if ($optionConfig['type'] === 'string') {
                    if (!$value && $nextArg) $value = $nextArg;
                    $options[$optionConfig['name']] = $value;
                    continue;
                } else if ($optionConfig['type'] === 'bool') {
                    if ($value !== 'true' && $value !== 'false') continue;
                    else if ($value === 'false') $options[$optionConfig['name']] = false;
                    else $options[$optionConfig['name']] = true;
                    continue;
                } else {
                    /**
                     * Auto detect
                     *
                     * program -a       =        a: true
                     * program -ab      =        a: b
                     * program -a b     =        a: b
                     * program -atrue   =        a: true
                     * program -a true  =        a: true
                     * program -a false  =       a: false
                     */
                    if ($value) {
                        $options[$optionConfig['name']] = static::safeValue($value);
                    } else if ($nextArg && static::isNotOption($nextArg)) {
                        $options[$optionConfig['name']] = static::safeValue($nextArg);
                        ++$i;
                    } else {
                        $options[$optionConfig['name']] = true;
                    }
                }
            } else {
                $commands[] = $arg;
            }
        }

        return [
            'program' => $program,
            'options' => $options,
            'commands' => $commands
        ];
    }

    /**
     * Convert to safe value
     *
     * @param string $value
     * @return bool
     */
    protected static function safeValue($value)
    {
        if ($value === 'true') return true;
        else if ($value === 'false') return false;
        return $value;
    }

    /**
     * Check if is not a option
     *
     * @param string $arg
     * @return bool
     */
    protected static function isNotOption($arg)
    {
        return $arg[0] !== '-';
    }

    /**
     * Check if is long option
     *
     * @param string $arg
     * @return bool
     */
    protected static function isLongOption($arg)
    {
        return $arg[0] === '-' && $arg[1] === '-';
    }

    /**
     * Check if is short option
     *
     * @param string $arg
     * @return bool
     */
    protected static function isShortOption($arg)
    {
        return $arg[0] === '-' && $arg[1] !== '-';
    }

    /**
     * Check has option value
     *
     * @param string $arg
     * @return bool
     */
    protected static function hasOptionValue($arg)
    {
        return strpos($arg, '=') !== false;
    }

}
