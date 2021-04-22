<?php

function class_uses_deep($class, bool $autoload = true): array 
{
    $traits = class_uses($class, $autoload);
    if ($parent = get_parent_class($class)) {
        $traits = array_merge($traits, class_uses_deep($parent, $autoload));
    }
    foreach ($traits as $trait) {
        $traits = array_merge($traits, class_uses_deep($trait, $autoload));
    }
    return $traits;
}
