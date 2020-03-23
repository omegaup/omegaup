<?php

define('OMEGAUP_ROOT', dirname(__DIR__, 2));
require_once(__DIR__ . '/../libs/third_party/log4php/src/main/php/Logger.php');
require_once(__DIR__ . '/../autoload.php');
require_once(__DIR__ . '/../../../vendor/autoload.php');

/**
 * @return list<string>
 */
function listDir(string $path) {
    $dh = opendir($path);
    if (!is_resource($dh)) {
        die("Failed to open {$path}");
    }
    $paths = [];
    while (($path = readdir($dh)) !== false) {
        if ($path == '.' || $path == '..') {
            continue;
        }
        $paths[] = $path;
    }
    closedir($dh);
    asort($paths);
    /** @var list<string> */
    return $paths;
}

/**
 * @param list<string> $propertyPath
 */
function convertTypeToTypeScript(
    \Psalm\Type\Union $unionType,
    string $methodName,
    $propertyPath
): string {
    $path = $methodName . '.' . join('.', $propertyPath);
    $typeNames = [];
    foreach ($unionType->getAtomicTypes() as $typeName => $type) {
        if ($typeName == 'array') {
            if ($type instanceof \Psalm\Type\Atomic\ObjectLike) {
                $propertyTypes = [];
                foreach ($type->properties as $propertyName => $propertyType) {
                    if (is_numeric($propertyName)) {
                        throw new Exception(
                            "Property {$path}.{$propertyName} is non-string: {$propertyType}"
                        );
                    }
                    if ($path == '' && $propertyName == 'status') {
                        // Omit this.
                        continue;
                    }
                    $childPropertyPath = array_merge(
                        $propertyPath,
                        [$propertyName]
                    );
                    if ($propertyType->isNullable()) {
                        $propertyName .= '?';
                        $propertyType->removeType('null');
                    }
                    $propertyTypes[] = (
                        "{$propertyName}: " .
                        convertTypeToTypeScript(
                            $propertyType,
                            $methodName,
                            $childPropertyPath
                        ) .
                        ';'
                    );
                }
                $typeNames[] = '{ ' . join(' ', $propertyTypes) . ' }';
            } elseif ($type instanceof \Psalm\Type\Atomic\TList) {
                $typeNames[] = (
                    convertTypeToTypeScript(
                        $type->type_param,
                        $methodName,
                        $propertyPath
                    ) .
                    '[]'
                );
            } elseif ($type instanceof \Psalm\Type\Atomic\TArray) {
                if (count($type->type_params) != 2) {
                    throw new Exception(
                        "Array type {$path} does not have two type params: {$type}"
                    );
                }
                if (!$type->type_params[0]->isSingle()) {
                    throw new Exception(
                        "Array type {$path} has complex key: {$type}"
                    );
                }
                if ($type->type_params[0]->hasString()) {
                    $typeNames[] = (
                        '{ [key: string]: ' .
                        convertTypeToTypeScript(
                            $type->type_params[1],
                            $methodName,
                            $propertyPath
                        ) .
                        '; }'
                    );
                    continue;
                }
                if ($type->type_params[0]->hasInt()) {
                    $typeNames[] = (
                        '{ [key: int]: ' .
                        convertTypeToTypeScript(
                            $type->type_params[1],
                            $methodName,
                            $propertyPath
                        ) .
                        '; }'
                    );
                    continue;
                }
                throw new Exception(
                    "Array type {$path} does not have a int|string key: {$type}"
                );
            } else {
                throw new Exception("Unsupported type {$path}: {$type}");
            }
        } elseif ($typeName == 'int' || $typeName == 'float') {
            $typeNames[] = 'numeric';
        } elseif (
            $typeName == 'string' ||
            $type instanceof \Psalm\Type\Atomic\TLiteralString
        ) {
            $typeNames[] = 'string';
        } elseif ($typeName == 'null') {
            $typeNames[] = 'null';
        } elseif ($typeName == 'bool') {
            $typeNames[] = 'boolean';
        } elseif ($type instanceof \Psalm\Type\Atomic\TNamedObject) {
            if ($type->value == 'stdClass') {
                // This is only used to coerce the response into being an
                // associative array instead of a flat array.
                continue;
            }
            $voPrefix = 'OmegaUp\\DAO\\VO\\';
            if (strpos($type->value, $voPrefix) !== 0) {
                throw new Exception(
                    "Unsupported object type {$path}: {$type->value}"
                );
            }
            $typeNames[] = 'omegaup.dao.' . substr(
                $type->value,
                strlen(
                    $voPrefix
                )
            );
        } else {
            throw new Exception("Unsupported type {$path}: {$type}");
        }
    }
    return join('|', $typeNames);
}

function getReturnType(\ReflectionMethod $method): string {
    $returnType = $method->getReturnType();
    if (
        !is_null($returnType) &&
        $returnType->getName() == 'void'
    ) {
        return 'void';
    }

    $docComment = \Psalm\DocComment::parse($method->getDocComment());
    $returns = $docComment['specials']['return'];
    if (count($returns) != 1) {
        throw new \Exception('More @return annotations than expected!');
    }
    $returnTypeString = array_values($returns)[0];
    for ($i = strlen($returnTypeString) - 1; $i >= 0; --$i) {
        if (
            $returnTypeString[$i] == '}' ||
            $returnTypeString[$i] == ']' ||
            $returnTypeString[$i] == '>'
        ) {
            return convertTypeToTypeScript(
                \Psalm\Type::parseString(substr($returnTypeString, 0, $i + 1)),
                $method->getDeclaringClass()->getName() . '::' . $method->getName(),
                []
            );
        }
    }
    throw new Exception("Invalid @return annotation: {$returnTypeString}");
}

function processControllerFile(string $controllerClassBasename): void {
    /** @var class-string */
    $controllerClassName = sprintf(
        '\\OmegaUp\\Controllers\\%s',
        $controllerClassBasename
    );
    $controllerClass = new \ReflectionClass($controllerClassName);

    $methodMapping = [];
    foreach (
        $controllerClass->getMethods(
            ReflectionMethod::IS_STATIC
        ) as $method
    ) {
        if (strpos($method->name, 'api') !== 0) {
            continue;
        }
        $apiMethodName = strtolower(
            $method->name[3]
        ) . substr(
            $method->name,
            4
        );
        $apiTypePrefix = $controllerClassBasename . substr($method->name, 3);
        $returnType = getReturnType($method);

        echo "  type {$apiTypePrefix}Request = any;\n";
        if ($returnType == 'void') {
            $methodMapping[$apiMethodName] = 'void';
            continue;
        }
        $methodMapping[$apiMethodName] = "{$apiTypePrefix}Response";
        echo "  type {$apiTypePrefix}Response = $returnType;\n";
    }

    echo "  export interface ${controllerClassBasename} {\n";
    ksort($methodMapping);
    foreach ($methodMapping as $apiMethodName => $returnType) {
        echo "    {$apiMethodName}: () => Promise<{$returnType}>;\n";
    }
    echo "  }\n\n";
}

echo "// generated by frontend/server/cmd/APITool.php. DO NOT EDIT.\n";
echo "namespace api {\n";
$controllerClassBasenames = [];
$controllerFiles = listDir(
    sprintf('%s/server/src/Controllers', strval(OMEGAUP_ROOT))
);
foreach ($controllerFiles as $controllerFile) {
    $controllerClassBasename = basename($controllerFile, '.php');
    $controllerClassBasenames[] = $controllerClassBasename;
    processControllerFile($controllerClassBasename);
}
echo "}\n\n";

echo "const API = {\n";
foreach ($controllerClassBasenames as $controllerClassBasename) {
    echo "  {$controllerClassBasename}: api.{$controllerClassBasename},\n";
}
echo "};\n\n";

echo "export { API as default };\n";
