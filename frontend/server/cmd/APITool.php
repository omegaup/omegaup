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
    public $typescriptExpansion;

    /**
     * @var ?string
     * @readonly
     */
    public $conversionFunction;

    public function __construct(
        string $typescriptExpansion,
        ?string $conversionFunction = null
    ) {
        $this->typescriptExpansion = $typescriptExpansion;
        $this->conversionFunction = $conversionFunction;
    }
}

class RequestParam {
    /**
     * @var string
     * @readonly
     */
    public $type;

    /**
     * @var bool
     * @readonly
     */
    public $isOptional;

    /**
     * @var string
     * @readonly
     */
    public $name;

    /**
     * @var null|string
     */
    public $description;

    public function __construct(
        string $type,
        string $name,
        ?string $description
    ) {
        $this->type = $type;
        $this->isOptional = !empty(
            array_intersect(
                ['null', 'mixed'],
                explode('|', $type)
            )
        );
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * @param array<int, string> $stringParams
     *
     * @return list<RequestParam>
     */
    public static function parse($stringParams) {
        $result = [];
        foreach ($stringParams as $stringParam) {
            if (
                preg_match(
                    '/^([^$]+)\s+\$([_a-zA-Z]\S*)\s*(\S.*)?$/',
                    $stringParam,
                    $matches,
                    PREG_UNMATCHED_AS_NULL
                ) !== 1
            ) {
                continue;
            }
            if (count($matches) == 4) {
                /** @var array{0: string, 1: string, 2: string, 3: ?string} $matches */
                [
                    $_,
                    $annotationType,
                    $annotationVariable,
                    $annotationDescription,
                ] = $matches;
            } else {
                /** @var array{0: string, 1: string, 2: string} $matches */
                [
                    $_,
                    $annotationType,
                    $annotationVariable,
                ] = $matches;
                $annotationDescription = null;
            }
            $result[] = new RequestParam(
                $annotationType,
                $annotationVariable,
                $annotationDescription
            );
        }
        usort(
            $result,
            fn (RequestParam $a, RequestParam $b) => $a->compare($b)
        );
        return $result;
    }

    /**
     * A comparison function to order all required parameters before the
     * non-required ones. Within each region, parameters are ordered
     * lexicographically.
     */
    public function compare(RequestParam $b): int {
        if ($this->isOptional != $b->isOptional) {
            return ($this->isOptional ? 1 : 0) - ($b->isOptional ? 1 : 0);
        }
        return strcmp($this->name, $b->name);
    }

    /**
     * The Python equivalent of the primitive type. It does not take into
     * account the optionality of arguments.
     */
    public function pythonPrimitiveType(): string {
        if ($this->type == 'mixed') {
            return 'Any';
        }
        $splitTypes = [];
        foreach (
            array_diff(
                explode('|', $this->type),
                ['null']
            ) as $splitType
        ) {
            if ($splitType[0] == "'") {
                $splitTypes = ['str'];
                break;
            }
            if ($splitType == 'boolean') {
                $splitTypes[] = 'bool';
                continue;
            }
            if ($splitType == 'string') {
                $splitTypes[] = 'str';
                continue;
            }
            if ($splitType == 'OmegaUp\\Timestamp') {
                $splitTypes[] = 'datetime.datetime';
                continue;
            }
            $splitTypes[] = $splitType;
        }
        if (count($splitTypes) > 1) {
            return 'Union[' . implode(', ', $splitTypes) . ']';
        }
        return $splitTypes[0];
    }

    /**
     * A stringified version of the value for Python.
     */
    public function pythonStringifiedValue(): string {
        switch ($this->pythonPrimitiveType()) {
            case 'str':
                return $this->name;
            case 'datetime.datetime':
                return "str(int({$this->name}.timestamp()))";
            default:
                return "str({$this->name})";
        }
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
     * @var list<RequestParam>
     * @readonly
     */
    public $requestParams = [];

    /**
     * @var ConversionResult
     * @readonly
     */
    public $returnType;

    /**
     * @var array<string, string>|string
     * @readonly
     */
    public $responseTypeMapping;

    /**
     * @param list<RequestParam> $requestParams
     * @param array<string, string>|string $responseTypeMapping
     */
    public function __construct(
        string $apiTypePrefix,
        string $docstringComment,
        $requestParams,
        ConversionResult $returnType,
        $responseTypeMapping
    ) {
        $this->apiTypePrefix = $apiTypePrefix;
        $this->docstringComment = $docstringComment;
        $this->requestParams = $requestParams;
        $this->returnType = $returnType;
        $this->responseTypeMapping = $responseTypeMapping;
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
                    ksort($type->properties);
                    foreach ($type->properties as $propertyName => $propertyType) {
                        if (is_numeric($propertyName)) {
                            throw new \Exception(
                                "Property {$path}.{$propertyName} is non-string: {$propertyType}"
                            );
                        }
                        if (empty($propertyPath) && $propertyName == 'status') {
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
                        if ($propertyType->possibly_undefined) {
                            $isNullable = true;
                        }
                        $conversionResult = $this->convertTypeToTypeScript(
                            $propertyType,
                            $methodName,
                            $childPropertyPath
                        );
                        if (!is_null($conversionResult->conversionFunction)) {
                            $requiresConversion = true;
                            $conversionStatement = (
                                "x.{$propertyName} = ({$conversionResult->conversionFunction})(x.{$propertyName});"
                            );
                            if ($isNullable) {
                                $conversionStatement = (
                                    "if (x.{$propertyName}) {$conversionStatement}"
                                );
                            }
                            $convertedProperties[] = $conversionStatement;
                        }
                        if ($isNullable) {
                            $propertyName .= '?';
                        }
                        $propertyTypes[] = "{$propertyName}: {$conversionResult->typescriptExpansion};";
                    }
                    $conversionFunction[] = '(x) => { ' . join(
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
                            "(x) => { if (!Array.isArray(x)) { return x; } return x.map({$conversionResult->conversionFunction}); }"
                        );
                    }
                    $typeNames[] = "{$conversionResult->typescriptExpansion}[]";
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
                        $typeNames[] = "{ [key: string]: {$conversionResult->typescriptExpansion}; }";
                        if (!is_null($conversionResult->conversionFunction)) {
                            $requiresConversion = true;
                            $conversionFunction[] = (
                                "(x) => { if (x instanceof Object) { Object.keys(x).forEach(y => x[y] = ({$conversionResult->conversionFunction})(x[y])); } return x; }"
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
                        $typeNames[] = "{ [key: number]: {$conversionResult->typescriptExpansion}; }";
                        if (!is_null($conversionResult->conversionFunction)) {
                            $requiresConversion = true;
                            $conversionFunction[] = (
                                "(x) => { if (x instanceof Object) { Object.keys(x).forEach(y => x[y] = ({$conversionResult->conversionFunction})(x[y])); } return x; }"
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
            } elseif (
                $typeName == 'bool' ||
                $typeName == 'false' ||
                $typeName == 'true'
            ) {
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
                        $requiresConversion = true;
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
        sort($typeNames);
        return new ConversionResult(
            join('|', $typeNames),
            $requiresConversion ? $conversionFunction[0] : null
        );
    }

    public function convertMethod(
        \ReflectionMethod $reflectionMethod,
        \Psalm\Internal\Scanner\ParsedDocblock $docComment,
        string $controllerClassBasename
    ): Method {
        $returns = $docComment->tags['return'];
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
        $unionType = \Psalm\Type::parseString($returnTypeString);
        $methodName = (
            $reflectionMethod->getDeclaringClass()->getName() .
            '::' .
            $reflectionMethod->getName()
        );
        if (!$unionType->isSingle()) {
            throw new Exception(
                "Method {$methodName} does not return a single type! {$unionType}"
            );
        }

        $conversionResult = $this->convertTypeToTypeScript(
            $unionType,
            $methodName
        );
        $returnType = array_values($unionType->getAtomicTypes())[0];
        if ($returnType instanceof \Psalm\Type\Atomic\ObjectLike) {
            /** @var array<string, string> */
            $responseTypeMapping = [];
            foreach ($returnType->properties as $propertyName => $propertyType) {
                if ($propertyName == 'status') {
                    continue;
                }
                $responseTypeMapping[strval(
                    $propertyName
                )] = $this->convertTypeToTypeScript(
                    $propertyType,
                    $methodName,
                    [strval($propertyName)]
                )->typescriptExpansion;
            }
        } else {
            $responseTypeMapping = $conversionResult->typescriptExpansion;
        }
        return new Method(
            $controllerClassBasename . substr(
                $reflectionMethod->name,
                3
            ),
            $docComment->description,
            RequestParam::parse(
                $docComment->tags['omegaup-request-param'] ?? []
            ),
            $conversionResult,
            $responseTypeMapping
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

    private function parseDocComment(string $docblock): \Psalm\Internal\Scanner\ParsedDocblock {
        return \Psalm\DocComment::parsePreservingLength(
            new \PhpParser\Comment\Doc($docblock)
        );
    }

    public function addController(string $controllerClassBasename): void {
        /** @var class-string */
        $controllerClassName = "\\OmegaUp\\Controllers\\{$controllerClassBasename}";
        $reflectionClass = new \ReflectionClass($controllerClassName);

        $docComment = $this->parseDocComment(
            strval($reflectionClass->getDocComment())
        );
        if (isset($docComment->tags['psalm-type'])) {
            foreach ($docComment->tags['psalm-type'] as $typeAlias) {
                [
                    $typeName,
                    $typeExpansion,
                ] = explode('=', $typeAlias);
                $conversionResult = $this->typeMapper->convertTypeToTypeScript(
                    \Psalm\Type::parseString($typeExpansion),
                    $typeAlias,
                    [$typeAlias]
                );
                if (
                    isset($this->typeMapper->typeAliases[$typeName]) &&
                    $this->typeMapper->typeAliases[$typeName]->typescriptExpansion != $conversionResult->typescriptExpansion
                ) {
                    throw new \Exception(
                        "Mismatched definition of `@psalm-type {$typeAlias}`. Previous definition was {$conversionResult->typescriptExpansion}."
                    );
                }
                $this->typeMapper->typeAliases[$typeName] = $conversionResult;
            }
        }

        $controller = new Controller(
            $controllerClassBasename,
            $docComment->description
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
            $docComment = $this->parseDocComment(
                strval($reflectionMethod->getDocComment())
            );
            $apiMethodName = strtolower(
                $reflectionMethod->name[3]
            ) . substr(
                $reflectionMethod->name,
                4
            );
            $controller->methods[$apiMethodName] = $this->typeMapper->convertMethod(
                $reflectionMethod,
                $docComment,
                $controllerClassBasename
            );
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
                    $docComment = $this->parseDocComment(
                        strval($reflectionProperty->getDocComment())
                    );
                    $returns = $docComment->tags['var'];
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
                        $reflectionProperty->getName()
                    ] = $this->typeMapper->convertTypeToTypeScript(
                        $returnType,
                        $typeName
                    )->typescriptExpansion;
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

            echo "  export namespace payloadParsers {\n";
            foreach ($this->typeMapper->typeAliases as $typeName => $conversionResult) {
                if (
                    strpos($typeName, 'Payload') !==
                    strlen($typeName) - strlen('Payload')
                ) {
                    continue;
                }
                echo "   export function {$typeName}(elementId: string = 'payload'): types.{$typeName} {\n";
                if (is_null($conversionResult->conversionFunction)) {
                    echo "     return JSON.parse(\n";
                    echo "       (document.getElementById(elementId) as HTMLElement).innerText,\n";
                    echo "     );\n\n";
                } else {
                    echo "     return ({$conversionResult->conversionFunction})(\n";
                    echo "       JSON.parse((document.getElementById(elementId) as HTMLElement).innerText),\n";
                    echo "     );\n\n";
                }
                echo "   }\n\n";
            }
            echo "  }\n\n";

            foreach ($this->typeMapper->typeAliases as $typeName => $conversionResult) {
                echo "  export interface {$typeName} {$conversionResult->typescriptExpansion};\n\n";
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
                echo "  export type {$method->apiTypePrefix}Response = {$method->returnType->typescriptExpansion};\n";
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

    public function generateApi(): void {
        echo <<<'EOD'
// generated by frontend/server/cmd/APITool.php. DO NOT EDIT.
import { messages } from './api_types';
import { addError } from './errors';

interface ApiCallOptions {
  quiet?: boolean;
}

export function apiCall<
  RequestType extends { [key: string]: any },
  ServerResponseType,
  ResponseType = ServerResponseType
>(
  url: string,
  transform?: (result: ServerResponseType) => ResponseType,
): (params?: RequestType, options?: ApiCallOptions) => Promise<ResponseType> {
  return (params?: RequestType, options?: ApiCallOptions) =>
    new Promise((accept, reject) => {
      let responseOk = true;
      let responseStatus = 200;
      fetch(
        url,
        params
          ? {
              method: 'POST',
              body: Object.keys(params)
                .filter(
                  (key) =>
                    params[key] !== null && typeof params[key] !== 'undefined',
                )
                .map((key) => {
                  if (params[key] instanceof Date) {
                    return `${encodeURIComponent(key)}=${encodeURIComponent(
                      Math.round(params[key].getTime() / 1000),
                    )}`;
                  }
                  return `${encodeURIComponent(key)}=${encodeURIComponent(
                    params[key],
                  )}`;
                })
                .join('&'),
              headers: {
                'Content-Type':
                  'application/x-www-form-urlencoded;charset=UTF-8',
              },
            }
          : undefined,
      )
        .then((response) => {
          if (response.status == 499) {
            // If we cancel the connection, let's just swallow the error since
            // the user is not going to see it.
            return;
          }
          responseOk = response.ok;
          responseStatus = response.status;
          return response.json();
        })
        .then((data) => {
          if (!responseOk) {
            if (typeof data === 'object' && !Array.isArray(data)) {
              data.status = 'error';
              data.httpStatusCode = responseStatus;
            }
            if (!options?.quiet) {
              addError(data);
              console.error(data);
            }
            reject(data);
            return;
          }
          if (transform) {
            accept(transform(data));
          } else {
            accept(data);
          }
        })
        .catch((err) => {
          const errorData = {
            status: 'error',
            error: err,
            httpStatusCode: responseStatus,
          };
          if (!options?.quiet) {
            addError(errorData);
            console.error(errorData);
          }
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
            echo (
                "- [{$controller->classBasename}](#" .
                strtolower($controller->classBasename) .
                ")\n"
            );
            ksort($controller->methods);
            foreach ($controller->methods as $apiMethodName => $method) {
                echo (
                    "  - [`/api/{$controller->apiName}/{$apiMethodName}/`](#" .
                    strtolower("api{$controller->apiName}{$apiMethodName}") .
                    ")\n"
                );
            }
        }
        echo "\n";

        foreach ($this->controllers as $controller) {
            echo "# {$controller->classBasename}\n\n";
            echo "{$controller->docstringComment}\n\n";
            foreach ($controller->methods as $apiMethodName => $method) {
                echo "## `/api/{$controller->apiName}/{$apiMethodName}/`\n\n";

                echo "### Description\n\n";
                echo "{$method->docstringComment}\n\n";

                if (!empty($method->requestParams)) {
                    echo "### Parameters\n\n";
                    echo "| Name | Type | Description |\n";
                    echo "|------|------|-------------|\n";
                    foreach ($method->requestParams as $requestParam) {
                        echo "| `{$requestParam->name}` | `" . str_replace(
                            '|',
                            '\\|',
                            $requestParam->type
                        ) . "` | {$requestParam->description} |\n";
                    }
                }

                echo "### Returns\n\n";
                if (empty($method->responseTypeMapping)) {
                    echo "_Nothing_\n";
                } elseif (is_string($method->responseTypeMapping)) {
                    echo "```typescript\n";
                    echo "{$method->returnType->typescriptExpansion}\n";
                    echo "```\n\n";
                } else {
                    echo "| Name | Type |\n";
                    echo "|------|------|\n";
                    ksort($method->responseTypeMapping);
                    foreach ($method->responseTypeMapping as $paramName => $paramType) {
                        echo "| `{$paramName}` | `" . str_replace(
                            '|',
                            '\\|',
                            $paramType
                        ) . "` |\n";
                    }
                }
            }
        }
    }

    public function generatePythonApi(): void {
        echo "\"\"\"A Python implementation of the omegaUp API.\"\"\"\n";
        echo "import datetime\n";
        echo "import logging\n";
        echo "import urllib.parse\n";
        echo "\n";
        echo "from typing import Any, BinaryIO, Dict, Iterable, Mapping, Optional\n";
        echo "\n";
        echo "import requests\n";
        echo "\n";
        echo "_DEFAULT_TIMEOUT = datetime.timedelta(minutes=1)\n";
        echo "\n";
        echo "\n";
        echo "def _filterKeys(d: Mapping[str, Any], keys: Iterable[str]) -> Dict[str, Any]:\n";
        echo "    \"\"\"Returns a copy of the mapping with certain values redacted.\n";
        echo "\n";
        echo "    Any of values mapped to the keys in the `keys` iterable will be replaced\n";
        echo "    with the string '[REDACTED]'.\n";
        echo "    \"\"\"\n";
        echo "    result: Dict[str, Any] = dict(d)\n";
        echo "    for key in keys:\n";
        echo "        if key in result:\n";
        echo "            result[key] = '[REDACTED]'\n";
        echo "    return result\n";
        echo "\n";
        echo "\n";
        echo "ApiReturnType = Dict[str, Any]\n";
        echo "\"\"\"The return type of any of the API requests.\"\"\"\n";
        echo "\n";
        ksort($this->controllers);
        foreach ($this->controllers as $controller) {
            echo "\n";
            echo "class {$controller->classBasename}:\n";
            echo '    r"""' . str_replace(
                "\n",
                "\n    ",
                $controller->docstringComment
            ) . "\n";
            echo "    \"\"\"\n";
            echo "    def __init__(self, client: 'Client') -> None:\n";
            echo "        self._client = client\n\n";
            foreach ($controller->methods as $apiMethodName => $method) {
                echo "    def {$apiMethodName}(\n";
                echo "            self,\n";
                echo "            *,\n";
                foreach ($method->requestParams as $requestParam) {
                    if ($requestParam->isOptional) {
                        continue;
                    }
                    echo "            {$requestParam->name}: {$requestParam->pythonPrimitiveType()},\n";
                }
                foreach ($method->requestParams as $requestParam) {
                    if (!$requestParam->isOptional) {
                        continue;
                    }
                    echo "            {$requestParam->name}: Optional[{$requestParam->pythonPrimitiveType()}] = None,\n";
                }
                echo "            # Out-of-band parameters:\n";
                echo "            files_: Optional[Mapping[str, BinaryIO]] = None,\n";
                echo "            check_: bool = True,\n";
                echo "            timeout_: datetime.timedelta = _DEFAULT_TIMEOUT) -> ApiReturnType:\n";
                echo '        r"""' . str_replace(
                    "\n",
                    "\n        ",
                    $method->docstringComment
                ) . "\n";

                if (!empty($method->requestParams)) {
                    echo "\n";
                    echo "        Args:\n";
                    foreach ($method->requestParams as $requestParam) {
                        if (empty($requestParam->description)) {
                            echo "            {$requestParam->name}:\n";
                        } else {
                            echo "            {$requestParam->name}: {$requestParam->description}\n";
                        }
                    }
                }

                echo "\n";
                echo "        Returns:\n";
                echo "            The API result dict.\n";
                echo "        \"\"\"\n";
                echo "        parameters: Dict[str, str] = {\n";
                foreach ($method->requestParams as $requestParam) {
                    if ($requestParam->isOptional) {
                        continue;
                    }
                    echo "            '{$requestParam->name}': {$requestParam->pythonStringifiedValue()},\n";
                }
                echo "        }\n";
                foreach ($method->requestParams as $requestParam) {
                    if (!$requestParam->isOptional) {
                        continue;
                    }
                    echo "        if {$requestParam->name} is not None:\n";
                    echo "            parameters['{$requestParam->name}'] = {$requestParam->pythonStringifiedValue()}\n";
                }
                echo "        return self._client.query('/api/{$controller->apiName}/{$apiMethodName}/',\n";
                echo "                                  payload=parameters,\n";
                echo "                                  files_=files_,\n";
                echo "                                  timeout_=timeout_,\n";
                echo "                                  check_=check_)\n";
                echo "\n";
            }
        }
        echo "\n";
        echo "\n";
        echo "class Client:\n";
        echo "    \"\"\".\"\"\",\n";
        echo "    def __init__(self,\n";
        echo "                 *,\n";
        echo "                 username: Optional[str] = None,\n";
        echo "                 password: Optional[str] = None,\n";
        echo "                 api_token: Optional[str] = None,\n";
        echo "                 auth_token: Optional[str] = None,\n";
        echo "                 url: str = 'https://omegaup.com') -> None:\n";
        echo "        self._url = url\n";
        echo "        self.username: Optional[str] = username\n";
        echo "        self.api_token: Optional[str] = api_token\n";
        echo "        self.auth_token: Optional[str] = None\n";
        echo "        if api_token is None:\n";
        echo "            if username is None:\n";
        echo "                raise ValueError(\n";
        echo "                    'username cannot be None if api_token is not provided',\n";
        echo "                )\n";
        echo "            if auth_token is not None:\n";
        echo "                self.auth_token = auth_token\n";
        echo "            elif password is not None:\n";
        echo "                self.auth_token = self.query('/api/user/login/',\n";
        echo "                                             payload={\n";
        echo "                                                 'usernameOrEmail': username,\n";
        echo "                                                 'password': password,\n";
        echo "                                             })['auth_token']\n";
        foreach ($this->controllers as $controller) {
            echo "        self._{$controller->apiName}: Optional[{$controller->classBasename}] = None\n";
        }
        echo "\n";
        echo "    def query(self,\n";
        echo "              endpoint: str,\n";
        echo "              payload: Optional[Mapping[str, str]] = None,\n";
        echo "              files_: Optional[Mapping[str, BinaryIO]] = None,\n";
        echo "              timeout_: datetime.timedelta = _DEFAULT_TIMEOUT,\n";
        echo "              check_: bool = True) -> ApiReturnType:\n";
        echo "        \"\"\"Issues a raw query to the omegaUp API.\"\"\"\n";
        echo "        logger = logging.getLogger('omegaup')\n";
        echo "        if payload is None:\n";
        echo "            payload = {}\n";
        echo "        else:\n";
        echo "            payload = dict(payload)\n";
        echo "\n";
        echo "        if logger.isEnabledFor(logging.DEBUG):\n";
        echo "            logger.debug('Calling endpoint: %s', endpoint)\n";
        echo "            logger.debug('Payload: %s', _filterKeys(payload, {'password'}))\n";
        echo "\n";
        echo "        headers = {}\n";
        echo "        if self.api_token is not None:\n";
        echo "            if self.username is not None:\n";
        echo "                headers['Authorization'] = ','.join((\n";
        echo "                    f'Credential={self.api_token}',\n";
        echo "                    f'Username={self.username}',\n";
        echo "                ))\n";
        echo "            else:\n";
        echo "                headers['Authorization'] = self.api_token\n";
        echo "        elif self.auth_token is not None:\n";
        echo "            payload['ouat'] = self.auth_token\n";
        echo "\n";
        echo "        r = requests.post(urllib.parse.urljoin(self._url, endpoint),\n";
        echo "                          data=payload,\n";
        echo "                          headers=headers,\n";
        echo "                          files=files_,\n";
        echo "                          timeout=timeout_.total_seconds())\n";
        echo "\n";
        echo "        try:\n";
        echo "            response: ApiReturnType = r.json()\n";
        echo "        except:  # noqa: bare-except Re-raised below\n";
        echo "            logger.exception(r.text)\n";
        echo "            raise\n";
        echo "\n";
        echo "        if logger.isEnabledFor(logging.DEBUG):\n";
        echo "            logger.info('Response: %s', _filterKeys(response, {'auth_token'}))\n";
        echo "\n";
        echo "        if check_ and r.status_code != 200:\n";
        echo "            raise Exception(response)\n";
        echo "\n";
        echo "        return response\n";
        foreach ($this->controllers as $controller) {
            echo "\n";
            echo "    @property\n";
            echo "    def {$controller->apiName}(self) -> {$controller->classBasename}:\n";
            echo "        \"\"\"Returns the {$controller->classBasename} API.\"\"\"\n";
            echo "        if self._{$controller->apiName} is None:\n";
            echo "            self._{$controller->apiName} = {$controller->classBasename}(self)\n";
            echo "        return self._{$controller->apiName}\n";
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

// Psalm requires having a ProjectAnalyzer instance set up in order to resolve
// some more complex types.
//
// It's a bit brittle to be fiddling with internal objects, but there is no
// other way to get a valid instance.
$rootDirectory = dirname(__DIR__, 3);
/** @psalm-suppress DeprecatedClass cannot yet upgrade to Composer 2 */
define('PSALM_VERSION', \PackageVersions\Versions::getVersion('vimeo/psalm'));
$projectAnalyzer = new \Psalm\Internal\Analyzer\ProjectAnalyzer(
    \Psalm\Config::loadFromXMLFile(
        "{$rootDirectory}/psalm.xml",
        $rootDirectory
    ),
    new \Psalm\Internal\Provider\Providers(
        new \Psalm\Internal\Provider\FileProvider()
    )
);

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
} elseif ($options['file'] == 'api.ts') {
    $apiGenerator->generateApi();
} elseif ($options['file'] == 'README.md') {
    $apiGenerator->generateDocumentation();
} elseif ($options['file'] == 'api.py') {
    $apiGenerator->generatePythonApi();
} else {
    throw new \Exception("Invalid option for --file: {$options['file']}");
}
