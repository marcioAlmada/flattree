<?php declare(strict_types=1);

namespace marc\flattree;

const CONTAINS = '>';

function unfold(array $data, $schema) : array {

    if (is_array($schema)) return unfold_adjacent($data, $schema);

    if (is_string($schema)) return unfold_adjacent_recursive($data, $schema);

    throw new \InvalidArgumentException('Invalid flat tree schema. Schema should be array or string');
}

function unfold_adjacent(array $data, array $levels) : array {

    (function (string ...$_) {})(...$levels); // levels must contain only strings

    if ((count($data) === 0) || null === ($level = array_shift($levels))) return $data;

    $tree = [];
    $uniqueValues = array_unique(array_column($data, $level));
    foreach ($uniqueValues as $filter) {
        $filtered = array_filter($data, function ($r) use ($filter, $level) {
            if (is_array($r))
                return $r[$level] === $filter;
            else if (is_object($r))
                return $r->{$level} === $filter;

            throw new \InvalidArgumentException('Invalid flat tree data. Levels must only be array or object<\stdclass>.');
        });
        $tree[$filter][CONTAINS] = array_merge($tree[$filter][CONTAINS] ?? [], (__FUNCTION__)($filtered, $levels));
    }

    return $tree;
}

function unfold_adjacent_recursive(array $data, string $schema) : array {

    $schema = explode('=', $schema);

    if (2 !== count($schema))
        throw new InvalidArgumentException(
            "Invalid recursive flat tree schema. Schema should be string.\nEx: 'unique_id=related_id' or 'name=parent_name'.");

    list($index, $relation) = $schema;

    if ((count($data) === 0)) return $data;

    $tree = [];

    foreach ($data as $item) {
         $tree[$item[$relation]] = array_merge($tree[$item[$relation]] ?? [], $item);
         $tree[$item[$index]][CONTAINS][$item[$relation]] = &$tree[$item[$relation]];
    }

    // Nodes with 0 reference count are root and will be kept. Nodes with higher
    // reference counts are discarded because they are leaf or branch nodes.
    $tree = array_filter($tree, function(&$var) : bool {
        ob_start();
        echo debug_zval_dump($var);
        preg_match('~refcount\((\d+)\)~', ob_get_clean(), $matches);

        return ($matches[1] - 3) === 0;
    });

    return $tree;
}

function debug(array $tree, $template, int $ident = 0) : string {
    $levelTemplate = is_array($template) ? ($template[$ident] ?? '<?>') : $template;

    $view = function ($scope) use ($levelTemplate) {
        return preg_replace_callback(
            '/\{(?P<tag>.+?)\}/',
            function ($match) use ($scope) { return $scope[$match['tag']] ?? '<null>';},
            $levelTemplate
        );
    };

    $buffer = '';
    foreach ($tree as $level => $subtree) {
        $subtree = (array) $subtree;
        $subtree[':level'] = $level;
        if (isset($subtree[CONTAINS])) {
            $buffer .=  str_repeat('│  ', $ident) . '├─ ' . $view($subtree) . "\n";
            $buffer .= (__FUNCTION__)($subtree[CONTAINS], $template, $ident+1);
        } else {
            $buffer .= str_repeat('│  ', $ident) . '└─ ' . $view($subtree) . "\n";
        }
    }

    return $buffer;
}
