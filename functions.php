<?php declare(strict_types=1);

namespace marc\flattree;

function unfold(array $data, array $levels) : array {

    (function (string ...$_) {})(...$levels); // levels must contain only strings

    if ((count($data) === 0) || null === ($level = array_shift($levels))) return $data;

    $tree = [];

    $uniqueValues = array_unique(array_column($data, $level));
    foreach ($uniqueValues as $filter) {
        $filtered = array_filter($data, function ($r) use ($filter, $level) { return $r[$level] === $filter; });
        $tree[$filter]['>'] = array_merge($tree[$filter]['>'] ?? [], (__FUNCTION__)($filtered, $levels));
    }

    return $tree;
}

function unfold_recursive(array $data, string $index, string $relation) : array {

    if ((count($data) === 0)) return $data;

    $tree = [];

    foreach ($data as $item) {
         $tree[$item[$relation]] = array_merge($tree[$item[$relation]] ?? [], $item);
         $tree[$item[$index]]['>'][$item[$relation]] = &$tree[$item[$relation]];
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
        $subtree[':level'] = $level;
        if (isset($subtree['>'])) {
            $buffer .=  str_repeat('│  ', $ident) . '├─ ' . $view($subtree) . "\n";
            $buffer .= (__FUNCTION__)($subtree['>'], $template, $ident+1);
        } else {
            $buffer .= str_repeat('│  ', $ident) . '└─ ' . $view($subtree) . "\n";
        }
    }

    return $buffer;
}
