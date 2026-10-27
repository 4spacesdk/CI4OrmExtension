<?php namespace OrmExtension\ModelParser;

/**
 * OpenAPI 3.0 from what the export reads: the schema of a PHPDoc type, the text of a doc comment,
 * and the schemas a document needs, by the names its schemas refer to.
 *
 * RestExtension builds its OpenAPI document with this too, so a type means the same in a model,
 * an interface and a parameter.
 */
class OpenApi {

    const SCHEMAS = '#/components/schemas/';

    /**
     * The schema of a PHPDoc type: `int`, `?string`, `string|null`, `int[]`, `string|double`, or the
     * name of a model or interface, which is a `$ref` to its schema.
     *
     * @param string $type
     * @param string|null $description
     * @return array
     */
    public static function schema($type, $description = null) {
        $schema = self::typeSchema(trim((string)$type));
        $description = trim((string)$description);
        if($description !== '') {
            $schema = self::withSibling($schema, 'description', $description);
        }
        return $schema;
    }

    /**
     * The summary and description of a doc comment, as PHPDoc has them: the summary is the text up
     * to the first empty line or the first line that ends a sentence, the description the text
     * after it, up to the tags. Either is null when there is none.
     *
     * @param string|false $docComment
     * @return array [summary, description]
     */
    public static function docText($docComment) {
        $lines = [];
        foreach(explode("\n", (string)$docComment) as $line) {
            $line = preg_replace('/^\s*\/\*\*\s?|\s*\*\/\s*$|^\s*\*\s?/', '', rtrim($line));
            if(substr(ltrim($line), 0, 1) == '@') break;
            $lines[] = rtrim($line);
        }

        $summary = [];
        while(count($lines)) {
            $line = array_shift($lines);
            if(trim($line) === '') {
                if(count($summary)) break;
                continue;
            }
            $summary[] = trim($line);
            if(substr($line, -1) == '.') break;
        }

        $summary = implode(' ', $summary);
        $description = trim(implode("\n", $lines));
        return [$summary === '' ? null : $summary, $description === '' ? null : $description];
    }

    /**
     * The names of the schemas a schema refers to, at any depth.
     *
     * @param array|object $schema
     * @return string[]
     */
    public static function references($schema) {
        $names = [];
        $schema = self::toArray($schema);
        array_walk_recursive($schema, function($value, $key) use(&$names) {
            if($key === '$ref' && is_string($value) && strpos($value, self::SCHEMAS) === 0) {
                $names[substr($value, strlen(self::SCHEMAS))] = true;
            }
        });
        return array_keys($names);
    }

    /**
     * The schemas of $pool a document needs: those of $names and those they refer to, in turn - or
     * all of them when $names is null. A name no schema has is an object of that name, so every
     * `$ref` points at something.
     *
     * @param array $pool schemas by name
     * @param string[]|null $names
     * @return array schemas by name
     */
    public static function closure(array $pool, $names = null) {
        $queue = $names === null ? array_keys($pool) : array_values($names);
        $schemas = [];
        while(count($queue)) {
            $name = (string)array_shift($queue);
            if($name === '' || isset($schemas[$name])) continue;
            $schemas[$name] = isset($pool[$name]) ? $pool[$name] : ['title' => $name, 'type' => 'object'];
            foreach(self::references($schemas[$name]) as $reference) {
                if(!isset($schemas[$reference])) $queue[] = $reference;
            }
        }
        return $schemas;
    }

    /**
     * An object schema of a model's or an interface's properties.
     *
     * @param string $name
     * @param array $properties schemas by property name
     * @return array
     */
    public static function objectSchema($name, array $properties) {
        return [
            'title'         => $name,
            'type'          => 'object',
            // An empty array would be written as [], which no schema reader takes for an object
            'properties'    => count($properties) ? $properties : new \stdClass()
        ];
    }

    private static function typeSchema($type) {
        $nullable = false;
        if(substr($type, 0, 1) == '?') {
            $nullable = true;
            $type = substr($type, 1);
        }

        $types = [];
        foreach(explode('|', $type) as $part) {
            $part = trim($part);
            if(in_array(strtolower($part), ['null', 'void'])) {
                $nullable = true;
            } else if($part !== '') {
                $types[] = $part;
            }
        }

        if(count($types) == 0) {
            $schema = self::any();
        } else if(count($types) == 1) {
            $schema = self::singleSchema($types[0]);
        } else {
            $schema = ['oneOf' => array_map(function($part) {
                return self::singleSchema($part);
            }, $types)];
        }

        if($nullable) {
            $schema = self::withSibling($schema, 'nullable', true);
        }
        return $schema;
    }

    private static function singleSchema($type) {
        if(substr($type, -2) == '[]') {
            return ['type' => 'array', 'items' => self::singleSchema(substr($type, 0, -2))];
        }

        $name = ltrim($type, '\\');
        switch(strtolower($name)) {
            case 'int':
            case 'integer':
                return ['type' => 'integer'];
            case 'float':
            case 'double':
            case 'number':
                return ['type' => 'number'];
            case 'bool':
            case 'boolean':
            case 'true':
            case 'false':
                return ['type' => 'boolean'];
            case 'string':
                return ['type' => 'string'];
            case 'array':
            case 'iterable':
                return ['type' => 'array', 'items' => self::any()];
            case 'object':
            case 'stdclass':
                return ['type' => 'object'];
            case 'datetime':
            case 'datetimeinterface':
            case 'datetimeimmutable':
            case 'codeigniter\\i18n\\time':
                return ['type' => 'string', 'format' => 'date-time'];
            case 'file':
                return ['type' => 'string', 'format' => 'binary'];
            case 'mixed':
                return self::any();
        }

        // A model or an interface, by the name its schema has: the class name without namespace
        $pos = strrpos($name, '\\');
        return ['$ref' => self::SCHEMAS . ($pos === false ? $name : substr($name, $pos + 1))];
    }

    /**
     * A schema of any value. Not {}: an empty array is written as [], which is not a schema.
     */
    private static function any() {
        return ['nullable' => true];
    }

    /**
     * Next to a `$ref` OpenAPI 3.0 reads nothing else, so a ref with a description or nullable
     * is wrapped in an allOf.
     */
    private static function withSibling(array $schema, $key, $value) {
        if(isset($schema['$ref'])) {
            $schema = ['allOf' => [$schema]];
        }
        $schema[$key] = $value;
        return $schema;
    }

    private static function toArray($value) {
        return is_array($value) ? $value : (array)json_decode(json_encode($value), true);
    }

}
