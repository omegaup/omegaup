<?php
/**
 * Description of ProblemLibinteractiveGenTest
 */
class ProblemLibinteractiveGenTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * A PHPUnit data provider for libinteractive details.
     * $emptyRequest, $missingParameters
     * @return list<array{0: bool, 1: list<string>, 2: array<string, string>}>
     */
    public function libinteractiveDetailsValueProvider(): array {
        return [
            [true, [], []],
            [false, [], ['name' => 'wrong name']],
            [false, [], []],
            [false, ['name'], []],
            [false, ['os'], []],
            [false, ['idl'], []],
            [false, ['language'], []],
        ];
    }

    /**
     * Test should return error when some parameters are not provided
     *
     * @param list<string> $missingParameters
     * @param array<string, string> $wrongParameters
     *
     * @dataProvider libinteractiveDetailsValueProvider
     */
    public function testProblemLibinteractiveGenWithIncompleteRequest(
        bool $isEmptyRequest,
        array $missingParameters,
        array $wrongParameters
    ) {
        $language = 'c';
        $os = 'unix';
        $name = 'file_name';
        $idl = "interface Main {\n};\n\ninterface sums {\nint sums(int a, int b);\n};";
        $error = null;

        $defaultValuesParameters = [
            'language' => $language,
            'os' => $os,
            'name' => $name,
            'idl' => $idl,
        ];
        $parameters = $isEmptyRequest ? [] : $defaultValuesParameters;
        foreach ($missingParameters as $missingParameter) {
            unset($parameters[$missingParameter]);
        }
        foreach ($wrongParameters as $index => $wrongParameter) {
            $parameters[$index] = $wrongParameter;
        }
        $response = \OmegaUp\Controllers\Problem::getLibinteractiveGenForTypeScript(
            new \OmegaUp\Request($parameters)
        )['smartyProperties']['payload'];

        if ($isEmptyRequest) {
            $defaultValuesParameters['name'] = null;
            $defaultValuesParameters['idl'] = null;
        } else {
            if (isset($wrongParameters['name'])) {
                $error = [
                    'description' =>  \OmegaUp\Translations::getInstance()->get(
                        'parameterInvalidAlias'
                    ),
                    'field' => 'name',
                ];
                $defaultValuesParameters['name'] = $wrongParameters['name'];
            } elseif (!empty($missingParameters)) {
                $field = null;
                foreach ($missingParameters as $missingParameter) {
                    $defaultValuesParameters[$missingParameter] = null;
                    $field = $missingParameter;
                }
                $error = [
                    'description' =>  \OmegaUp\Translations::getInstance()->get(
                        'parameterInvalid'
                    ),
                    'field' => $field,
                ];
            }
        }
        $this->assertEquals(
            $response['language'],
            $defaultValuesParameters['language']
        );
        $this->assertEquals($response['os'], $defaultValuesParameters['os']);
        $this->assertEquals(
            $response['name'],
            $defaultValuesParameters['name']
        );
        $this->assertEquals($response['idl'], $defaultValuesParameters['idl']);
        if (!empty($missingParameters)) {
            $this->assertEquals($response['error'], $error);
        }
    }
}
