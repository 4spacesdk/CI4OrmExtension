<?php namespace OrmExtension\ModelParser;

use Config\Services;

/**
 * Where the classes of a namespace are, by CodeIgniter's autoloader - the app's own, and those of
 * the composer packages it discovers. So entities and interfaces of a package are exported along
 * with the app's.
 */
class Namespaces {

    /**
     * The directories of a namespace, by its longest registered prefix: App\Entities is
     * APPPATH/Entities, Spant\Entities is the package's src/Entities.
     *
     * @param string $namespace
     * @return string[]
     */
    public static function directories($namespace) {
        $namespace = trim($namespace, '\\');
        $best = null;
        foreach (Services::autoloader()->getNamespace() as $prefix => $paths) {
            $prefix = trim($prefix, '\\');
            if (($namespace === $prefix || strpos($namespace, $prefix . '\\') === 0) && ($best === null || strlen($prefix) > strlen($best))) {
                $best = $prefix;
            }
        }
        if ($best === null) {
            return [];
        }
        $rest = str_replace('\\', DIRECTORY_SEPARATOR, ltrim(substr($namespace, strlen($best)), '\\'));
        $directories = [];
        foreach ((array)Services::autoloader()->getNamespace($best) as $path) {
            $directory = rtrim($path, '/\\') . ($rest === '' ? '' : DIRECTORY_SEPARATOR . $rest);
            if (is_dir($directory)) {
                $directories[] = $directory;
            }
        }
        return $directories;
    }

    /**
     * The classes in the top level of each namespace's directories, as full class names. One
     * name found in two namespaces is the first namespace's.
     *
     * @param string[] $namespaces
     * @return string[]
     */
    public static function classes(array $namespaces) {
        $classes = [];
        $seen = [];
        foreach ($namespaces as $namespace) {
            $namespace = trim($namespace, '\\');
            foreach (self::directories($namespace) as $directory) {
                foreach (scandir($directory) as $file) {
                    if ($file[0] == '_' || substr($file, -4) != '.php') {
                        continue;
                    }
                    $name = substr($file, 0, -4);
                    if (isset($seen[$name])) {
                        continue;
                    }
                    $seen[$name] = true;
                    $classes[] = $namespace . '\\' . $name;
                }
            }
        }
        return $classes;
    }

}
