<?php
/**
 * Created by PhpStorm.
 * User: hfcorriez
 * Date: 2017/9/17
 * Time: 下午6:45
 */

namespace Pac;


class Process
{
    /**
     * @var array Execute command default options
     */
    public static $EXEC_OPTIONS = array(
        'timeout' => 3600,          // Max time(seconds) to execute, default is 3600
        'env' => array(),           // Env variables to pass
        'cwd' => null,              // Current working dir
        'stdio' => 'inherit'        // Support => inherit, pipe, ignore
    );

    /**
     * Copy string to system clipboard
     *
     * @param string $string
     * @return bool
     */
    public static function clip($string)
    {
        switch (strtoupper(PHP_OS)) {
            case 'DARWIN':
                $command = 'pbcopy';
                break;
            case 'LINUX':
                $command = 'xclip';
                break;
            case 'WINNT':
                $command = 'clip';
                break;
            default:
                return false;
        }
        $code = self::exec('printf ' . escapeshellarg($string) . ' | ' . $command);
        if ($code !== 0) return false;
        return true;
    }

    /**
     * Execute the command
     *
     * @param string $cmd
     * @param array $options
     * @return array
     */
    public static function exec($cmd, array $options = array())
    {
        $descriptors = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $options = $options + self::$EXEC_OPTIONS;

        $stdout = $stderr = $status = $write = $except = null;
        $process = proc_open($cmd, $descriptors, $pipes, $options['cwd'], $options['env']);
        $timeEnd = time() + $options['timeout'];
        if (is_resource($process)) {
            do {
                $timeLeft = $timeEnd - time();
                $read = array($pipes[1]);
                stream_select($read, $write, $except, $timeLeft);
                $output = fread($pipes[1], 10);
                if (!$options['stdio'] || $options['stdio'] === 'inherit') {
                    echo $output;
                } else if ($options['stdio'] = 'pipe') {
                    $stdout .= $output;
                }
            } while (!feof($pipes[1]) && $timeLeft > 0);
            fclose($pipes[1]);
            if ($timeLeft <= 0) {
                proc_terminate($process);
                $stderr = 'process terminated for timeout.';
                $status = -1;
            } else {
                while (!feof($pipes[2])) {
                    $error = fread($pipes[2], 10);
                    if (!$options['stdio'] || $options['stdio'] === 'inherit') {
                        echo $error;
                    } else if ($options['stdio'] = 'pipe') {
                        $stderr .= $error;
                    }
                }
                fclose($pipes[2]);
                $status = proc_close($process);
            }
        }
        return array($status, $stdout, $stderr);
    }

    /**
     * Execute the command daemonize
     *
     * @static
     * @param $cmd
     * @return int
     */
    public static function daemonize($cmd)
    {
        return pclose(popen($cmd . ' &', 'r'));
    }

    /**
     * Get username
     *
     * @return string
     */
    public static function pid() {
        return getmypid();
    }

    /**
     * Get username
     *
     * @return string
     */
    public static function user() {
        return get_current_user();
    }

    /**
     * Get home dir
     *
     * @return string
     */
    public static function home() {
        return idx($_SERVER, 'HOME') ? : idx($_SERVER, 'HOMEDRIVE') . idx($_SERVER, 'HOMEPATH');
    }
}
