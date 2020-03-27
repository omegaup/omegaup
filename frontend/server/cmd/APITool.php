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

    /**
     * @var string
     * @readonly
     */
    public $apiName = '';

    /** @var array<string, Method> */
    public $methods = [];

    public function __construct(string $classBasename) {
        $this->classBasename = $classBasename;
        $this->apiName = strtolower(
            $classBasename[0]
        ) . substr(
            $classBasename,
            1
        );
    }
}

/**
 * @param array<string, string> $typeAliases
 * @param array<string, true> $daoTypes
 * @param list<string> $propertyPath
 */
function convertTypeToTypeScript(
    \Psalm\Type\Union $unionType,
    &$daoTypes,
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
                            $daoTypes,
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
                        $daoTypes,
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
                            $daoTypes,
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
                        '{ [key: number]: ' .
                        convertTypeToTypeScript(
                            $type->type_params[1],
                            $daoTypes,
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
            $typeNames[] = 'number';
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
                $typeNames[] = "types.{$type->value}";
                continue;
            }
            $voPrefix = 'OmegaUp\\DAO\\VO\\';
            if (strpos($type->value, $voPrefix) !== 0) {
                throw new \Exception(
                    "Unsupported object type {$path}: {$type->value}"
                );
            }
            $daoTypeName = substr(
                $type->value,
                strlen(
                    $voPrefix
                )
            );
            $daoTypes[$daoTypeName] = true;
            $typeNames[] = "dao.{$daoTypeName}";
        } else {
            throw new \Exception("Unsupported type {$path}: {$type}");
        }
    }
    return join('|', $typeNames);
}

/**
 * @param array<string, true> $daoTypes
 * @param array<string, string> $typeAliases
 */
function getReturnType(
    \ReflectionMethod $method,
    &$daoTypes,
    $typeAliases
): string {
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
                $daoTypes,
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

    /** @var array<string, true> */
    private $daoTypes = [];

    public function addController(string $controllerClassBasename): void {
        /** @var class-string */
        $controllerClassName = "\\OmegaUp\\Controllers\\{$controllerClassBasename}";
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
                    $this->daoTypes,
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
            $returnType = getReturnType(
                $reflectionMethod,
                $this->daoTypes,
                $this->typeAliases
            );
            if ($returnType == 'void') {
                // void APIs are not really intended to be called from
                // JavaScript, so they are not exposed.
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
            $method->returnType = $returnType;
            $controller->methods[$apiMethodName] = $method;
        }
    }

    public function generateTypes(): void {
        echo "// generated by frontend/server/cmd/APITool.php. DO NOT EDIT.\n";

        if (!empty($this->daoTypes)) {
            echo "\n// DAO types\n";
            echo "export namespace dao {\n";
            ksort($this->daoTypes);
            foreach ($this->daoTypes as $typeName => $typeExpansion) {
                echo "  export interface {$typeName} {\n";
                /** @var class-string */
                $voClassName = "\\OmegaUp\\DAO\\VO\\{$typeName}";
                $reflectionClass = new \ReflectionClass($voClassName);
                /** @var array<string, string> */
                $properties = [];
                foreach (
                    $reflectionClass->getProperties(
                        ReflectionProperty::IS_PUBLIC
                    ) as $reflectionProperty
                ) {
                    $docComment = \Psalm\DocComment::parse(
                        $reflectionProperty->getDocComment()
                    );
                    $returns = $docComment['specials']['var'];
                    if (count($returns) != 1) {
                        throw new \Exception(
                            'More @var annotations than expected!'
                        );
                    }
                    $returnType = \Psalm\Type::parseString(
                        array_values(
                            $returns
                        )[0]
                    );
                    if ($returnType->isNullable()) {
                        $returnType->removeType('null');
                    }
                    $properties[
                        strval($reflectionProperty->name)
                    ] = convertTypeToTypeScript(
                        $returnType,
                        $_,
                        [],
                        $typeName,
                        []
                    );
                }
                ksort($properties);
                foreach ($properties as $propertyTypeName => $propertyTypeExpansion) {
                    echo "    {$propertyTypeName}?: {$propertyTypeExpansion};\n";
                }
                echo "  }\n\n";
            }
            echo "}\n";
        }

        if (!empty($this->typeAliases)) {
            echo "\n// Type aliases\n";
            echo "export namespace types {\n";
            ksort($this->typeAliases);
            foreach ($this->typeAliases as $typeName => $typeExpansion) {
                echo "  export interface {$typeName} {$typeExpansion};\n\n";
            }
            echo "}\n";
        }

        ksort($this->controllers);

        echo "\n// API messages\n";
        echo "export namespace messages {\n";
        foreach ($this->controllers as $_ => $controller) {
            if (empty($controller->methods)) {
                continue;
            }

            echo "  // {$controller->classBasename}\n";
            ksort($controller->methods);
            foreach ($controller->methods as $apiMethodName => $method) {
                echo "  export type {$method->apiTypePrefix}Request = { [key: string]: any;};\n";
                echo "  export type {$method->apiTypePrefix}Response = {$method->returnType};\n";
            }
            echo "\n";
        }
        echo "}\n";

        echo "\n// Controller interfaces\n";
        echo "export namespace controllers {\n";
        foreach ($this->controllers as $_ => $controller) {
            echo "  export interface {$controller->classBasename} {\n";
            foreach ($controller->methods as $apiMethodName => $method) {
                echo "    {$apiMethodName}: (\n";
                echo "      params?: messages.{$method->apiTypePrefix}Request\n";
                echo "    ) => Promise<messages.{$method->apiTypePrefix}Response>;\n";
            }
            echo "  }\n\n";
        }
        echo "}\n";
    }

    public function generateDeclarations(): void {
        echo "// generated by frontend/server/cmd/APITool.php. DO NOT EDIT.\n";
        echo "import { controllers } from './api_types';\n";
        echo "\n";
        echo "const API = {\n";

        ksort($this->controllers);
        foreach ($this->controllers as $controller) {
            echo "  {$controller->classBasename}: controllers.{$controller->classBasename},\n";
        }
        echo "};\n";
        echo "\n";
        echo "export { API as default };\n";
    }

    public function generateTransitional(): void {
        echo "// generated by frontend/server/cmd/APITool.php. DO NOT EDIT.\n";
        echo "import { messages } from './api_types';\n";
        echo "import { addError } from './errors';\n\n";
        echo <<<'EOD'
export function apiCall<
  RequestType extends { [key: string]: any },
  ServerResponseType,
  ResponseType = ServerResponseType
>(
  url: string,
  transform?: (result: ServerResponseType) => ResponseType,
): (params?: RequestType) => Promise<ResponseType> {
  return (params?: RequestType) =>
    new Promise((accept, reject) => {
      let responseOk = true;
      fetch(
        url,
        params
          ? {
              method: 'POST',
              body: Object.keys(params)
                .filter(key => typeof params[key] !== 'undefined')
                .map(
                  key =>
                    `${encodeURIComponent(key)}=${encodeURIComponent(
                      params[key],
                    )}`,
                )
                .join('&'),
              headers: {
                'Content-Type':
                  'application/x-www-form-urlencoded;charset=UTF-8',
              },
            }
          : undefined,
      )
        .then(response => {
          if (response.status == 499) {
            // If we cancel the connection, let's just swallow the error since
            // the user is not going to see it.
            return;
          }
          responseOk = response.ok;
          return response.json();
        })
        .then(data => {
          if (!responseOk) {
            addError(data);
            reject(data);
            return;
          }
          if (transform) {
            accept(transform(data));
          } else {
            accept(data);
          }
        })
        .catch(err => {
          const errorData = { status: 'error', error: err };
          addError(errorData);
          reject(errorData);
        });
    });
}

EOD;
        echo "\n";
        ksort($this->controllers);
        foreach ($this->controllers as $controller) {
            echo "export const {$controller->classBasename} = {\n";
            ksort($controller->methods);
            foreach ($controller->methods as $apiMethodName => $method) {
                echo "    {$apiMethodName}: apiCall<\n";
                echo "      messages.{$method->apiTypePrefix}Request,\n";
                echo "      messages.{$method->apiTypePrefix}Response\n";
                echo "    >('/api/{$controller->apiName}/{$apiMethodName}/'),\n";
            }
            echo "};\n\n";
        }
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

$options = getopt('', ['file:']);
if (!isset($options['file']) || !is_string($options['file'])) {
    throw new \Exception('Missing option for --file');
}

$apiGenerator = new APIGenerator();
$controllerFiles = listDir(
    sprintf('%s/server/src/Controllers', strval(OMEGAUP_ROOT))
);
foreach ($controllerFiles as $controllerFile) {
    $apiGenerator->addController(basename($controllerFile, '.php'));
}
if ($options['file'] == 'api_types.ts') {
    $apiGenerator->generateTypes();
} elseif ($options['file'] == 'api.d.ts') {
    $apiGenerator->generateDeclarations();
} elseif ($options['file'] == 'api_transitional.ts') {
    $apiGenerator->generateTransitional();
} else {
    throw new \Exception("Invalid option for --file: {$options['file']}");
}
