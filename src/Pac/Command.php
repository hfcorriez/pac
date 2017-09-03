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
        'auto' => true,
        'options' => []
    );

    /**
     * Instance
     *
     * @return Command
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set Config
     *
     * @param array $config
     * @return $this
     */
    public static function config(array $config)
    {
        return self::instance()->setConfig($config);
    }

    /**
     * Parse the args array or string
     *
     * @param string|array $args
     * @return array
     */
    public static function parse($args)
    {
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
     * Set config
     *
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
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
        $unknowns = [];
        $invalids = [];

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

                $name = $optionConfig['name'];
                $type = $optionConfig['type'];

                if ($type === 'string') {
                    if (!$value && $nextArg) $value = $nextArg;
                    $options[$name] = $value;
                    continue;
                } else if ($type === 'number') {
                    if (!$value && $nextArg) $value = $nextArg;
                    if (!is_numeric($value)) $invalids[$name] = $value;
                    else $options[$name] = (float)$value;
                    continue;
                } else if ($type === 'bool') {
                    if ($value !== 'true' && $value !== 'false') {
                        $invalids[$name] = $value;
                        continue;
                    } else if ($value === 'false') $options[$name] = false;
                    else $options[$name] = true;
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
                        $options[$name] = static::autoValue($value);
                    } else if ($nextArg && static::isNotOption($nextArg)) {
                        $options[$name] = static::autoValue($nextArg);
                        ++$i;
                    } else {
                        $options[$name] = true;
                    }

                    if (!isset($optionConfigs[$key]) && !$this->config['auto']) {
                        $unknowns[$name] = $options[$name];
                        unset($options[$name]);
                    }
                }
            } else {
                $commands[] = $arg;
            }
        }

        return [
            'program' => $program,
            'options' => $options,
            'commands' => $commands,
            'unknowns' => $unknowns,
            'invalids' => $invalids
        ];
    }

    /**
     * Convert to safe value
     *
     * @param string $value
     * @return bool
     */
    protected static function autoValue($value)
    {
        if ($value === 'true' || $value === '1') return true;
        else if ($value === 'false' || $value === '0') return false;
        else if (is_numeric($value)) return $value + 0;
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
