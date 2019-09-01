<?php
if (!function_exists('try_define')) {
    /**
     * @param mixed $value
     * @psalm-suppress DuplicateFunction Already checking whether it exists.
     */
    function try_define(string $name, $value) : void {
        if (defined($name)) {
            return;
        }
        define($name, $value);
    }
}
