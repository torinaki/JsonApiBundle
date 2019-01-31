<?php
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Tests\RequestParameters\Validator\Constraints;

use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Mango\Bundle\JsonApiBundle\RequestParameters\Validator\Constraints\SortFieldValidator;
use Mango\Bundle\JsonApiBundle\RequestParameters\Validator\Constraints\SortField;
use Mango\Bundle\JsonApiBundle\RequestParameters\GetParametersAbstract;

/**
 * Tests for SortFieldValidator validator
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class SortFieldValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * Test for 'validate' method when object if not an instance of GetParametersAbstract class
     * ER: Exception should be thrown
     *
     * @return void
     */
    public function testValidateThrowsExceptionOnInvalidModel()
    {
        $this->setRoot(new \stdClass());
        $constraint = new SortField();

        $this->expectException(\LogicException::class);
        $this->validator->validate('some value', $constraint);
    }

    /**
     * Data provider for testValidate
     *
     * @return array
     */
    public function validateProvider()
    {
        return [
            // #0: empty fields, empty value - invalid
            [
                'value'    => '',
                'fields'   => [],
                'expected' => false
            ],
            // #1: empty fields, null value - invalid
            [
                'value'    => null,
                'fields'   => [],
                'expected' => false
            ],
            // #2: empty fields, some value - invalid
            [
                'value'    => 'field',
                'fields'   => [],
                'expected' => false
            ],
            // #3: some value not in fields list - invalid
            [
                'value'    => 'field1',
                'fields'   => ['field2', 'field3'],
                'expected' => false
            ],
            // #4: some value in fields list - valid
            [
                'value'    => 'field2',
                'fields'   => ['field2', 'field3'],
                'expected' => true
            ],
            // #5: field is case sensitive - invalid
            [
                'value'    => 'Field2',
                'fields'   => ['field2', 'field3'],
                'expected' => false
            ],
        ];
    }

    /**
     * Test for 'validate' method
     *
     * @param string $value           value to validate
     * @param array  $availableFields available field list
     * @param bool   $expected        valid/invalid
     * @return void
     * @dataProvider validateProvider
     */
    public function testValidate($value, $availableFields, $expected)
    {
        $params = $this->getMockBuilder(GetParametersAbstract::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $params->expects($this->once())
            ->method('getAvailableSortingFields')
            ->willReturn($availableFields);

        $this->setRoot($params);
        $constraint = new SortField();

        $this->validator->validate($value, $constraint);

        if ($expected) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation("Field '{{ field_name }}' is not allowed for sorting.")
                ->setParameter('{{ field_name }}', $value)
                ->assertRaised();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new SortFieldValidator();
    }
}
