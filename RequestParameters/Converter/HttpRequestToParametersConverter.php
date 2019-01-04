<?php
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Converter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use JMS\Serializer\SerializerInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\ValidatableInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\GetParametersInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\SpecialParameters;
use Mango\Bundle\JsonApiBundle\RequestParameters\Parser\ParamParserInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Parser\FilterParamParser;

/**
 * HttpRequestToParametersConverter service to convert http request to parameters model for controllers
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class HttpRequestToParametersConverter implements ParamConverterInterface
{
    const OPTION_QUERY = 'query';
    const OPTION_VALIDATE = 'validate';
    const FORMAT = 'json';

    /**
     * Serializer service
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Validator
     *
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Parameter parsers
     *
     * @var ParamParserInterface[]
     */
    private $paramParsers;

    /**
     * Constructor
     *
     * @param SerializerInterface    $serializer   serializer
     * @param ValidatorInterface     $validator    validator
     * @param ParamParserInterface[] $paramParsers param parsers collection
     */
    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, array $paramParsers)
    {
        $this->checkParsers($paramParsers);
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->paramParsers = $paramParsers;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $model = $this->buildModel($request, $configuration);
        $this->validateModel($model, $configuration);

        $request->attributes->set($configuration->getName(), $model);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return class_exists($configuration->getClass())
            && !empty($configuration->getName())
            && is_subclass_of($configuration->getClass(), GetParametersInterface::class);
    }

    /**
     * Builds parameters model
     *
     * @param Request        $request       request
     * @param ParamConverter $configuration configuration
     * @return object|GetParametersInterface
     */
    private function buildModel(Request $request, ParamConverter $configuration)
    {
        /** @var string|GetParametersInterface $className */

        $options = $configuration->getOptions();
        $className = $configuration->getClass();
        if (isset($options[self::OPTION_QUERY]) || in_array(self::OPTION_QUERY, $options, true)) {
            $content = $request->query->all();
        } else {
            $content = json_decode($request->getContent(), true);
        }

        $content[SpecialParameters::FILTER] = $this->paramParsers[SpecialParameters::FILTER]->parse(
            $content[SpecialParameters::FILTER] ?? [],
            [
                FilterParamParser::PAYLOAD_OPERATORS => is_subclass_of($className, GetParametersInterface::class)
                    ? $className::getFilterFieldOperators()
                    : [],
            ]
        );

        $content[SpecialParameters::SORT] = $this->paramParsers[SpecialParameters::SORT]->parse(
            $content[SpecialParameters::SORT] ?? ''
        );

        $content[SpecialParameters::PAGE] = $this->paramParsers[SpecialParameters::PAGE]->parse(
            $content[SpecialParameters::PAGE] ?? []
        );

        return $this->serializer->deserialize(
            json_encode($content),
            $className,
            self::FORMAT
        );
    }

    /**
     * Validates model
     *
     * @param GetParametersInterface $model         model
     * @param ParamConverter         $configuration configuration
     * @return void
     */
    private function validateModel(GetParametersInterface $model, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();
        if (!isset($options[self::OPTION_VALIDATE]) && !in_array(self::OPTION_VALIDATE, $options, true)) {
            return;
        }

        if ($model instanceof ValidatableInterface) {
            $violations = $this->validator->validate($model);
            $model->setViolations($violations);
            $model->setValid($violations->count() === 0);
        }
    }

    /**
     * Checks parsers collection
     *
     * @param ParamParserInterface[] $paramParsers parsers
     * @return void
     * @throws \InvalidArgumentException
     */
    private function checkParsers(array $paramParsers)
    {
        $specialParams = SpecialParameters::getAll();
        foreach ($paramParsers as $key => $paramParser) {
            if (!in_array($key, $specialParams, true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Unknown "%s" spacial parameter. Available parameters: %s',
                        $key,
                        implode(',', $specialParams)
                    )
                );
            }
            if (!$paramParser instanceof ParamParserInterface) {
                throw new \InvalidArgumentException(
                    'Only parsers that are implements ParamParserInterface are allowed'
                );
            }
        }
    }
}
