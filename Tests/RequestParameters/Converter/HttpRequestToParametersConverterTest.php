<?php
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Tests\RequestParameters\Converter;

use Mango\Bundle\JsonApiBundle\Tests\Fixtures\RequestTestModel;
use Mango\Bundle\JsonApiBundle\Tests\Functional\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Mango\Bundle\JsonApiBundle\RequestParameters\GetParametersAbstract;
use Mango\Bundle\JsonApiBundle\RequestParameters\Converter\HttpRequestToParametersConverter;
use Mango\Bundle\JsonApiBundle\RequestParameters\Parser\FilterParamParser;
use Mango\Bundle\JsonApiBundle\RequestParameters\Parser\SortParamParser;
use Mango\Bundle\JsonApiBundle\RequestParameters\Parser\PageParamParser;

/**
 * Tests for HttpRequestToParametersConverter service
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class HttpRequestToParametersConverterTest extends WebTestCase
{
    /**
     * Serializer
     *
     * @var Serializer
     */
    private $serializer;

    /**
     * Validator
     *
     * @var RecursiveValidator
     */
    private $validator;

    /**
     * Filter param parser
     *
     * @var FilterParamParser
     */
    private $filterParamParser;

    /**
     * Sort param parser
     *
     * @var SortParamParser
     */
    private $sortParamParser;

    /**
     * Page param parser
     *
     * @var PageParamParser
     */
    private $pageParamParser;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $kernel = static::bootKernel(['test_case' => 'AnnotationOnly']);
        $this->serializer = $kernel->getContainer()->get('jms_serializer');
        $this->validator = $this->getMockBuilder(RecursiveValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterParamParser = $this->getMockBuilder(FilterParamParser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortParamParser = $this->getMockBuilder(SortParamParser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageParamParser = $this->getMockBuilder(PageParamParser::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Data provider for testCreationWhenInvalidParsers
     *
     * @return array
     */
    public function creationWhenInvalidParsersProvider()
    {
        return [
            // #0: not instance of parser
            [
                [new FilterParamParser(), new \stdClass(), new PageParamParser()]
            ],
            // #1: incorrect keys
            [
                [new FilterParamParser(), new SortParamParser(), new PageParamParser()]
            ],
        ];
    }

    /**
     * Test for object creation when invalid parsers
     *
     * @param array $parsers parsers
     * @return void
     * @dataProvider creationWhenInvalidParsersProvider
     */
    public function testCreationWhenInvalidParsers(array $parsers)
    {
        $this->expectException(\InvalidArgumentException::class);

        new HttpRequestToParametersConverter(
            $this->serializer,
            $this->validator,
            $parsers
        );
    }

    /**
     * Data provider for testSupports
     *
     * @return array
     */
    public function supportsProvider()
    {
        return [
            // #0: all empty - false
            ['', '', false],
            // #1: empty name - false
            ['', get_class($this->createParamsModel()), false],
            // #2: empty class name - false
            ['some string', '', false],
            // #3: not existing class name - false
            ['some string', 'NotExistingClass', false],
            // #4: existing class but not subclass of GetParametersAbstract - false
            ['some string', 'stdClass', false],
            // #5: abstract class - true
            ['some string', GetParametersAbstract::class, true],
            // #6: subclass of GetParametersAbstract - true
            ['some string', get_class($this->createParamsModel()), true],
        ];
    }

    /**
     * Test for 'supports' method
     *
     * @param string $name      name
     * @param string $className class name
     * @param bool   $expected  expected result
     * @return void
     * @dataProvider supportsProvider
     */
    public function testSupports($name, $className, $expected)
    {
        $configuration = new ParamConverter(
            [
                'name'  => $name,
                'class' => $className
            ]
        );

        $service = $this->createService();

        $this->assertSame($expected, $service->supports($configuration));
    }

    /**
     * Data provider for testApply
     *
     * @return array
     */
    public function applyProvider()
    {
        return [
            // #0: no options - getting content from request, no validation
            [
                'options'      => [],
                'from_content' => true,
                'validate'     => false,
                'is_valid'     => true,
            ],
            // #1: request - getting content from request, no validation
            [
                'options'      => ['request'],
                'from_content' => true,
                'validate'     => false,
                'is_valid'     => true,
            ],
            // #2: content, validate, valid
            [
                'options'      => ['validate'],
                'from_content' => true,
                'validate'     => true,
                'is_valid'     => true,
            ],
            // #3: content, validate, invalid
            [
                'options'      => ['validate'],
                'from_content' => true,
                'validate'     => true,
                'is_valid'     => false,
            ],
            // #4: another form of option, valid
            [
                'options'      => ['validate' => 'true'],
                'from_content' => true,
                'validate'     => true,
                'is_valid'     => true,
            ],
            // #5: query option - getting query from request, no validation
            [
                'options'      => ['query'],
                'from_content' => false,
                'validate'     => false,
                'is_valid'     => true,
            ],
            // #6: query, validation, valid
            [
                'options'      => ['query', 'validate'],
                'from_content' => false,
                'validate'     => true,
                'is_valid'     => true,
            ],
            // #7: query, validation, invalid
            [
                'options'      => ['query', 'validate'],
                'from_content' => false,
                'validate'     => true,
                'is_valid'     => false,
            ],
            // #8: another form of option, valid
            [
                'options'      => ['query' => 'true'],
                'from_content' => false,
                'validate'     => false,
                'is_valid'     => true,
            ],
        ];
    }

    /**
     * Test for 'apply' method
     *
     * @param array $options     passed options to converter
     * @param bool  $fromContent whether to use content instead of query
     * @param bool  $validate    whether the model should be validated
     * @param bool  $isValid     is model valid flag
     * @return void
     * @dataProvider applyProvider
     */
    public function testApply($options, $fromContent, $validate, $isValid)
    {
        $query = [
            'some_param' => 123,
            'filter'     => ['field1' => 'value1'],
            'sort'       => 'field2',
            'page'       => ['number' => 2],
            'more_param' => 'some string'
        ];
        $content = [
            'param' => 234
        ];

        $expectedContentToDeserialize = $fromContent
            ? json_encode(
                [
                    'param'      => 234,
                    'filter'     => [],
                    'sort'       => [],
                    'page'       => [],
                ]
            )
            : json_encode(
                [
                    'some_param' => 123,
                    'filter'     => [],
                    'sort'       => [],
                    'page'       => [],
                    'more_param' => 'some string'
                ]
            );

        $model = $this->createParamsModel();
        $request = new Request($query, [], [], [], [], [], json_encode($content));
        $configuration = new ParamConverter(
            [
                'name'    => 'some_name',
                'class'   => RequestTestModel::class,
                'options' => $options
            ]
        );

        $this->filterParamParser->expects($this->once())
            ->method('parse')
            ->with($fromContent ? [] : $query['filter'], ['operators' => []])
            ->willReturn([]);

        $this->sortParamParser->expects($this->once())
            ->method('parse')
            ->with($fromContent ? '' : $query['sort'])
            ->willReturn([]);

        $this->pageParamParser->expects($this->once())
            ->method('parse')
            ->with($fromContent ? [] : $query['page'])
            ->willReturn([]);

        $violations = [];
        if ($validate) {
            $violations = new ConstraintViolationList();
            if (!$isValid) {
                $violations->add(new ConstraintViolation('', '', [], null, '', ''));
            }

            $this->validator->expects($this->once())
                ->method('validate')
                ->with($this->isInstanceOf(RequestTestModel::class))
                ->willReturn($violations);
        } else {
            $this->validator->expects($this->never())->method('validate');
        }

        $service = $this->createService();

        $this->assertTrue($service->apply($request, $configuration));
        $this->assertInstanceOf(RequestTestModel::class, $request->attributes->get('some_name'));
        if ($validate) {
            $this->assertSame($isValid, $request->attributes->get('some_name')->isValid());
            $this->assertSame($violations, $request->attributes->get('some_name')->getViolations());
        }
    }

    /**
     * Returns new instance of converter
     *
     * @return HttpRequestToParametersConverter
     */
    private function createService()
    {
        return new HttpRequestToParametersConverter(
            $this->serializer,
            $this->validator,
            [
                'filter' => $this->filterParamParser,
                'sort'   => $this->sortParamParser,
                'page'   => $this->pageParamParser
            ]
        );
    }

    /**
     * Return new subclass of GetParametersAbstract
     *
     * @return GetParametersAbstract
     */
    private function createParamsModel()
    {
        return new class extends GetParametersAbstract {

            public function getAvailableFilterFields(): array
            {
                return [];
            }

            public function getAvailableSortingFields(): array
            {
                return [];
            }

            public static function getFilterFieldOperators(): array
            {
                return [];
            }
        };
    }
}
