<?php

define('OMEGAUP_ROOT', dirname(__DIR__, 2));
require_once(__DIR__ . '/../libs/third_party/log4php/src/main/php/Logger.php');
require_once(__DIR__ . '/../autoload.php');
require_once(__DIR__ . '/../../../vendor/autoload.php');

class Method {
    /** @var string */
    public $apiTypePrefix = '';

    /** @var string */
    public $returnType = '';
}

class Controller {
    /**
     * @var string
     * @readonly
     */
    public $classBasename = '';

    /** @var array<string, Method> */
    public $methods = [];

    public function __construct(string $classBasename) {
        $this->classBasename = $classBasename;
    }
}

/**
 * @param array<string, string> $typeAliases
 * @param list<string> $propertyPath
 */
function convertTypeToTypeScript(
    \Psalm\Type\Union $unionType,
    $typeAliases,
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
                        throw new \Exception(
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
                            $typeAliases,
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
                        $typeAliases,
                        $methodName,
                        $propertyPath
                    ) .
                    '[]'
                );
            } elseif ($type instanceof \Psalm\Type\Atomic\TArray) {
                if (count($type->type_params) != 2) {
                    throw new \Exception(
                        "Array type {$path} does not have two type params: {$type}"
                    );
                }
                if (!$type->type_params[0]->isSingle()) {
                    throw new \Exception(
                        "Array type {$path} has complex key: {$type}"
                    );
                }
                if ($type->type_params[0]->hasString()) {
                    $typeNames[] = (
                        '{ [key: string]: ' .
                        convertTypeToTypeScript(
                            $type->type_params[1],
                            $typeAliases,
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
                            $typeAliases,
                            $methodName,
                            $propertyPath
                        ) .
                        '; }'
                    );
                    continue;
                }
                throw new \Exception(
                    "Array type {$path} does not have a int|string key: {$type}"
                );
            } else {
                throw new \Exception("Unsupported type {$path}: {$type}");
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
            if (isset($typeAliases[$type->value])) {
                $typeNames[] = "api.{$type->value}";
                continue;
            }
            $voPrefix = 'OmegaUp\\DAO\\VO\\';
            if (strpos($type->value, $voPrefix) !== 0) {
                throw new \Exception(
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
            throw new \Exception("Unsupported type {$path}: {$type}");
        }
    }
    return join('|', $typeNames);
}

/**
 * @param array<string, string> $typeAliases
 */
function getReturnType(\ReflectionMethod $method, $typeAliases): string {
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
                $typeAliases,
                $method->getDeclaringClass()->getName() . '::' . $method->getName(),
                []
            );
        }
    }
    throw new Exception("Invalid @return annotation: {$returnTypeString}");
}

class APIGenerator {
    /** @var array<string, Controller> */
    private $controllers = [];

    /** @var array<string, string> */
    private $typeAliases = [];

    public function addController(string $controllerClassBasename): void {
        /** @var class-string */
        $controllerClassName = sprintf(
            '\\OmegaUp\\Controllers\\%s',
            $controllerClassBasename
        );
        $reflectionClass = new \ReflectionClass($controllerClassName);

        $docComment = \Psalm\DocComment::parse(
            $reflectionClass->getDocComment()
        );
        if (isset($docComment['specials']['psalm-type'])) {
            foreach ($docComment['specials']['psalm-type'] as $typeAlias) {
                [
                    $typeName,
                    $typeExpansion,
                ] = explode('=', $typeAlias);
                $typeExpansion = convertTypeToTypeScript(
                    \Psalm\Type::parseString($typeExpansion),
                    $this->typeAliases,
                    $controllerClassName,
                    []
                );
                if (
                    isset($this->typeAliases[$typeName]) &&
                    $this->typeAliases[$typeName] != $typeExpansion
                ) {
                    throw new \Exception(
                        "Mismatched definition of `@psalm-type {$typeAlias}`. Previous definition was {$typeExpansion}."
                    );
                }
                $this->typeAliases[$typeName] = $typeExpansion;
            }
        }

        $controller = new Controller($controllerClassBasename);
        $this->controllers[$controllerClassBasename] = $controller;

        foreach (
            $reflectionClass->getMethods(
                ReflectionMethod::IS_STATIC
            ) as $reflectionMethod
        ) {
            if (strpos($reflectionMethod->name, 'api') !== 0) {
                continue;
            }
            $apiMethodName = strtolower(
                $reflectionMethod->name[3]
            ) . substr(
                $reflectionMethod->name,
                4
            );
            $method = new Method();
            $method->apiTypePrefix = $controllerClassBasename . substr(
                $reflectionMethod->name,
                3
            );
            $method->returnType = getReturnType(
                $reflectionMethod,
                $this->typeAliases
            );
            $controller->methods[$apiMethodName] = $method;
        }
    }

    public function generate(): void {
        echo "// generated by frontend/server/cmd/APITool.php. DO NOT EDIT.\n";
        echo "namespace api {\n";

        if (!empty($this->typeAliases)) {
            echo "  // Type aliases\n";
            ksort($this->typeAliases);
            foreach ($this->typeAliases as $typeName => $typeExpansion) {
                echo "  type {$typeName} = {$typeExpansion};\n";
            }
            echo "\n";
        }

        ksort($this->controllers);
        foreach ($this->controllers as $_ => $controller) {
            echo "  // {$controller->classBasename}\n";
            ksort($controller->methods);
            foreach ($controller->methods as $apiMethodName => $method) {
                echo "  type {$method->apiTypePrefix}Request = any;\n";
                if ($method->returnType != 'void') {
                    echo "  type {$method->apiTypePrefix}Response = {$method->returnType};\n";
                }
            }
            echo "  export interface {$controller->classBasename} {\n";
            foreach ($controller->methods as $apiMethodName => $method) {
                if ($method->returnType == 'void') {
                    echo "    {$apiMethodName}: () => Promise<void>;\n";
                } else {
                    echo "    {$apiMethodName}: () => Promise<{$method->apiTypePrefix}Response>;\n";
                }
            }
            echo "  }\n\n";
        }
        echo "}\n\n";

        echo "const API = {\n";
        foreach ($this->controllers as $controller) {
            echo "  {$controller->classBasename}: api.{$controller->classBasename},\n";
        }
        echo "};\n\n";

        echo "export { API as default };\n";
    }
}

/**
 * @return Generator<int, string>
 */
function listDir(string $path): Generator {
    $dh = opendir($path);
    if (!is_resource($dh)) {
        die("Failed to open {$path}");
    }
    while (($path = readdir($dh)) !== false) {
        if ($path == '.' || $path == '..') {
            continue;
        }
        yield $path;
    }
    closedir($dh);
}

$apiGenerator = new APIGenerator();
$controllerFiles = listDir(
    sprintf('%s/server/src/Controllers', strval(OMEGAUP_ROOT))
);
foreach ($controllerFiles as $controllerFile) {
    $apiGenerator->addController(basename($controllerFile, '.php'));
}
$apiGenerator->generate();
