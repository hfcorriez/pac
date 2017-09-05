<?php

/**
 * Identity function, returns its argument unmodified.
 *
 * This is useful almost exclusively as a workaround to an oddity in the PHP
 * grammar -- this is a syntax error:
 *
 *    COUNTEREXAMPLE
 *    new Thing()->doStuff();
 *
 * ...but this works fine:
 *
 *    id(new Thing())->doStuff();
 *
 * @param   wild Anything.
 * @return  wild Unmodified argument.
 */
function id($x) {
    return $x;
}


/**
 * Access an array index, retrieving the value stored there if it exists or
 * a default if it does not. This function allows you to concisely access an
 * index which may or may not exist without raising a warning.
 *
 * @param   array   Array to access.
 * @param   scalar  Index to access in the array.
 * @param   wild    Default value to return if the key is not present in the
 *                  array.
 * @return  wild    If `$array[$key]` exists, that value is returned. If not,
 *                  $default is returned without raising a warning.
 */
function idx(array $array, $key, $default = null) {
    if (is_array($key)) {
        if (!$key) {
            return $default;
        }

        $last = end($key);
        $key = array_slice($key, 0, -1);

        $cursor = $array;
        foreach ($key as $k) {
            $cursor = idx($cursor, $k);
            if (!is_array($cursor)) {
                return $default;
            }
        }

        return idx($cursor, $last, $default);
    } else {
        // isset() is a micro-optimization - it is fast but fails for null values.
        if (isset($array[$key])) {
            return $array[$key];
        }

        // Comparing $default is also a micro-optimization.
        if ($default === null || array_key_exists($key, $array)) {
            return null;
        }
    }

    return $default;
}
