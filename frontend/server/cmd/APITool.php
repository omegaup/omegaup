<?php

define('OMEGAUP_ROOT', dirname(__DIR__, 2));
require_once(__DIR__ . '/../libs/third_party/log4php/src/main/php/Logger.php');
require_once(__DIR__ . '/../autoload.php');
require_once(__DIR__ . '/../../../vendor/autoload.php');

class ConversionResult {
    /**
     * @var string
     * @readonly
     */
    public $expansion;

    /**
     * @var ?string
     * @readonly
     */
    public $conversionFunction;

    public function __construct(
        string $expansion,
        ?string $conversionFunction = null
    ) {
        $this->expansion = $expansion;
        $this->conversionFunction = $conversionFunction;
    }
}

class Method {
    /**
     * @var string
     * @readonly
     */
    public $apiTypePrefix = '';

    /**
     * @var string
     * @readonly
     */
    public $docstringComment = '';

    /**
     * @var ConversionResult
     * @readonly
     */
    public $returnType;

    public function __construct(
        string $apiTypePrefix,
        string $docstringComment,
        ConversionResult $returnType
    ) {
        $this->apiTypePrefix = $apiTypePrefix;
        $this->docstringComment = $docstringComment;
        $this->returnType = $returnType;
    }
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

    /**
     * @var string
     * @readonly
     */
    public $docstringComment = '';

    /** @var array<string, Method> */
    public $methods = [];

    public function __construct(
        string $classBasename,
        string $docstringComment
    ) {
        $this->classBasename = $classBasename;
        $this->apiName = strtolower(
            $classBasename[0]
        ) . substr(
            $classBasename,
            1
        );
        $this->docstringComment = $docstringComment;
    }
}

class TypeMapper {
    /** @var array<string, ConversionResult> */
    public $typeAliases = [];

    /** @var array<string, true> */
    private $daoTypes;

    /**
     * @param array<string, true> $daoTypes
     */
    public function __construct(&$daoTypes = []) {
        $this->daoTypes =& $daoTypes;
    }

    /**
     * @param list<string> $propertyPath
     */
    public function convertTypeToTypeScript(
        \Psalm\Type\Union $unionType,
        string $methodName,
        $propertyPath = []
    ): ConversionResult {
        $path = $methodName . '.' . join('.', $propertyPath);
        $typeNames = [];
        $requiresConversion = false;
        $conversionFunction = [];
        foreach ($unionType->getAtomicTypes() as $typeName => $type) {
            if ($typeName == 'array') {
                if ($type instanceof \Psalm\Type\Atomic\ObjectLike) {
                    $convertedProperties = [];
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
                        $isNullable = $propertyType->isNullable();
                        if ($isNullable) {
                            $propertyType->removeType('null');
                        }
                        $conversionResult = $this->convertTypeToTypeScript(
                            $propertyType,
                            $methodName,
                            $childPropertyPath
                        );
                        if (!is_null($conversionResult->conversionFunction)) {
                            $requiresConversion = true;
                            if ($isNullable) {
                                $convertedProperties[] = (
                                    "if (x.{$propertyName}) x.{$propertyName} = ({$conversionResult->conversionFunction})(x.{$propertyName});"
                                );
                            } else {
                                $convertedProperties[] = (
                                    "x.{$propertyName} = ({$conversionResult->conversionFunction})(x.{$propertyName});"
                                );
                            }
                        }
                        if ($isNullable) {
                            $propertyName .= '?';
                        }
                        $propertyTypes[] = "{$propertyName}: {$conversionResult->expansion};";
                    }
                    $conversionFunction[] = 'x => { ' . join(
                        ' ',
                        $convertedProperties
                    ) . ' return x; }';
                    $typeNames[] = '{ ' . join(' ', $propertyTypes) . ' }';
                } elseif ($type instanceof \Psalm\Type\Atomic\TList) {
                    $conversionResult = $this->convertTypeToTypeScript(
                        $type->type_param,
                        $methodName,
                        $propertyPath
                    );
                    if (!is_null($conversionResult->conversionFunction)) {
                        $requiresConversion = true;
                        $conversionFunction[] = (
                            "x => { if (!Array.isArray(x)) { return x; } return x.map({$conversionResult->conversionFunction}); }"
                        );
                    }
                    $typeNames[] = "{$conversionResult->expansion}[]";
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
                        $conversionResult = $this->convertTypeToTypeScript(
                            $type->type_params[1],
                            $methodName,
                            $propertyPath
                        );
                        $typeNames[] = "{ [key: string]: {$conversionResult->expansion}; }";
                        if (!is_null($conversionResult->conversionFunction)) {
                            $requiresConversion = true;
                            $conversionFunction[] = (
                                "x => { if (x instanceof Object) { Object.keys.forEach(y => x[y] = ({$conversionResult->conversionFunction})[x[y]); } return x; }"
                            );
                        }
                        continue;
                    }
                    if ($type->type_params[0]->hasInt()) {
                        $conversionResult = $this->convertTypeToTypeScript(
                            $type->type_params[1],
                            $methodName,
                            $propertyPath
                        );
                        $typeNames[] = "{ [key: number]: {$conversionResult->expansion}; }";
                        if (!is_null($conversionResult->conversionFunction)) {
                            $requiresConversion = true;
                            $conversionFunction[] = (
                                "x => { if (x instanceof Object) { Object.keys.forEach(y => x[y] = ({$conversionResult->conversionFunction})[x[y]); } return x; }"
                            );
                        }
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
                if ($type->value == 'OmegaUp\\Timestamp') {
                    // This is automatically cast into a JavaScript Date.
                    $typeNames[] = 'Date';
                    $requiresConversion = true;
                    $conversionFunction[] = '(x: number) => new Date(x * 1000)';
                    continue;
                }
                if (isset($this->typeAliases[$type->value])) {
                    $typeNames[] = "types.{$type->value}";
                    $conversionResult = $this->typeAliases[$type->value];
                    if (!is_null($conversionResult->conversionFunction)) {
                        $conversionFunction[] = $conversionResult->conversionFunction;
                    }
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
                $this->daoTypes[$daoTypeName] = true;
                $typeNames[] = "dao.{$daoTypeName}";
            } else {
                throw new \Exception("Unsupported type {$path}: {$type}");
            }
        }
        if ($requiresConversion && count($conversionFunction) != 1) {
            throw new Exception(
                "Conversion function too complex {$path}: [" .
                join(', ', $conversionFunction)
            );
        }
        return new ConversionResult(
            join('|', $typeNames),
            $requiresConversion ? $conversionFunction[0] : null
        );
    }

    /**
     * @param array{description: string, specials: array<string, array<int, string>>} $docComment
     */
    public function getReturnType(
        \ReflectionMethod $reflectionMethod,
        $docComment
    ): ConversionResult {
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
                $returnTypeString = substr($returnTypeString, 0, $i + 1);
                break;
            }
        }
        return $this->convertTypeToTypeScript(
            \Psalm\Type::parseString($returnTypeString),
            $reflectionMethod->getDeclaringClass()->getName() .
            '::' .
            $reflectionMethod->getName()
        );
    }
}

class APIGenerator {
    /** @var array<string, Controller> */
    private $controllers = [];

    /** @var array<string, true> */
    private $daoTypes = [];

    /** @var TypeMapper */
    private $typeMapper;

    public function __construct() {
        $this->typeMapper = new TypeMapper($this->daoTypes);
    }

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
                $conversionResult = $this->typeMapper->convertTypeToTypeScript(
                    \Psalm\Type::parseString($typeExpansion),
                    $typeAlias
                );
                if (
                    isset($this->typeMapper->typeAliases[$typeName]) &&
                    $this->typeMapper->typeAliases[$typeName]->expansion != $conversionResult->expansion
                ) {
                    throw new \Exception(
                        "Mismatched definition of `@psalm-type {$typeAlias}`. Previous definition was {$conversionResult->expansion}."
                    );
                }
                $this->typeMapper->typeAliases[$typeName] = $conversionResult;
            }
        }

        $controller = new Controller(
            $controllerClassBasename,
            $docComment['description']
        );

        foreach (
            $reflectionClass->getMethods(
                ReflectionMethod::IS_STATIC
            ) as $reflectionMethod
        ) {
            if (strpos($reflectionMethod->name, 'api') !== 0) {
                continue;
            }
            $returnType = $reflectionMethod->getReturnType();
            if (
                !is_null($returnType) &&
                $returnType->getName() == 'void'
            ) {
                // void APIs are not really intended to be called from
                // JavaScript, so they are not exposed.
                continue;
            }
            $docComment = \Psalm\DocComment::parse(
                $reflectionMethod->getDocComment()
            );
            $conversionResult = $this->typeMapper->getReturnType(
                $reflectionMethod,
                $docComment
            );
            $apiMethodName = strtolower(
                $reflectionMethod->name[3]
            ) . substr(
                $reflectionMethod->name,
                4
            );
            $method = new Method(
                $controllerClassBasename . substr(
                    $reflectionMethod->name,
                    3
                ),
                $docComment['description'],
                $conversionResult
            );
            $controller->methods[$apiMethodName] = $method;
        }

        if (empty($controller->methods)) {
            return;
        }
        $this->controllers[$controllerClassBasename] = $controller;
    }

    public function generateTypes(): void {
        echo "// generated by frontend/server/cmd/APITool.php. DO NOT EDIT.\n";

        if (!empty($this->daoTypes)) {
            echo "\n// DAO types\n";
            echo "export namespace dao {\n";
            ksort($this->daoTypes);
            $localTypeMapper = new TypeMapper();
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
                    ] = $localTypeMapper->convertTypeToTypeScript(
                        $returnType,
                        $typeName
                    )->expansion;
                }
                ksort($properties);
                foreach ($properties as $propertyTypeName => $propertyTypeExpansion) {
                    echo "    {$propertyTypeName}?: {$propertyTypeExpansion};\n";
                }
                echo "  }\n\n";
            }
            echo "}\n";
        }

        if (!empty($this->typeMapper->typeAliases)) {
            echo "\n// Type aliases\n";
            echo "export namespace types {\n";
            ksort($this->typeMapper->typeAliases);
            foreach ($this->typeMapper->typeAliases as $typeName => $conversionResult) {
                echo "  export interface {$typeName} {$conversionResult->expansion};\n\n";
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
                if (!is_null($method->returnType->conversionFunction)) {
                    echo "  export type _{$method->apiTypePrefix}ServerResponse = any\n";
                }
                echo "  export type {$method->apiTypePrefix}Response = {$method->returnType->expansion};\n";
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
            console.error(data);
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
          console.error(errorData);
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
                if (!is_null($method->returnType->conversionFunction)) {
                    echo "      messages._{$method->apiTypePrefix}ServerResponse,\n";
                    echo "      messages.{$method->apiTypePrefix}Response\n";
                    echo "    >('/api/{$controller->apiName}/{$apiMethodName}/',\n";
                    echo "      {$method->returnType->conversionFunction}),\n";
                } else {
                    echo "      messages.{$method->apiTypePrefix}Response\n";
                    echo "    >('/api/{$controller->apiName}/{$apiMethodName}/'),\n";
                }
            }
            echo "};\n\n";
        }
    }

    public function generateDocumentation(): void {
        ksort($this->controllers);
        foreach ($this->controllers as $controller) {
            echo "# {$controller->classBasename}\n\n";
            echo "{$controller->docstringComment}\n\n";
            ksort($controller->methods);
            foreach ($controller->methods as $apiMethodName => $method) {
                echo "## `/api/{$controller->apiName}/{$apiMethodName}/`\n\n";

                echo "### Descripción\n\n";
                echo "{$method->docstringComment}\n\n";

                echo "### Parámetros\n\n";
                echo "_Por documentar_\n\n";

                echo "### Regresa\n\n";
                echo "```typescript\n";
                echo "{$method->returnType->expansion}\n";
                echo "```\n\n";
            }
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
    if (strpos($controllerFile, '.php') === false) {
        continue;
    }
    $apiGenerator->addController(basename($controllerFile, '.php'));
}
if ($options['file'] == 'api_types.ts') {
    $apiGenerator->generateTypes();
} elseif ($options['file'] == 'api.d.ts') {
    $apiGenerator->generateDeclarations();
} elseif ($options['file'] == 'api_transitional.ts') {
    $apiGenerator->generateTransitional();
} elseif ($options['file'] == 'README.md') {
    $apiGenerator->generateDocumentation();
} else {
    throw new \Exception("Invalid option for --file: {$options['file']}");
}
