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

/**
 * Returns the first element of an array. Exactly like reset(), but doesn't
 * choke if you pass it some non-referenceable value like the return value of
 * a function.
 *
 * @param    array Array to retrieve the first element from.
 * @return   wild  The first value of the array.
 */
function first(array $arr) {
    return reset($arr);
}

/**
 * Returns the last element of an array. This is exactly like `end()` except
 * that it won't warn you if you pass some non-referencable array to
 * it -- e.g., the result of some other array operation.
 *
 * @param    array Array to retrieve the last element from.
 * @return   wild  The last value of the array.
 */
function last(array $arr) {
    return end($arr);
}

/**
 * Returns the first key of an array.
 *
 * @param    array       Array to retrieve the first key from.
 * @return   int|string  The first key of the array.
 */
function head_key(array $arr) {
    reset($arr);
    return key($arr);
}

/**
 * Returns the last key of an array.
 *
 * @param    array       Array to retrieve the last key from.
 * @return   int|string  The last key of the array.
 */
function last_key(array $arr) {
    end($arr);
    return key($arr);
}
