<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer;

use Doctrine\Common\Proxy\Proxy;
use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Visitor\Factory\JsonSerializationVisitorFactory;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata as JsonApiClassMetadata;
use Mango\Bundle\JsonApiBundle\EventListener\Serializer\JsonApiEventSubscriber;
use Mango\Bundle\JsonApiBundle\Representation\PaginatedRepresentation;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class JsonApiSerializationVisitor extends AbstractVisitor implements SerializationVisitorInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    protected $jsonApiClassMetadata;

    /**
     * @var bool
     */
    protected $showVersionInfo;

    /**
     * @var bool
     */
    protected $isJsonApiDocument = false;

    /**
     * @var JsonSerializationVisitorFactory
     */
    private $visitor;

    public function __construct(JsonSerializationVisitor $visitor, MetadataFactoryInterface $metadataFactory, $showVersionInfo = false)
    {
        $this->visitor = $visitor;
        $this->showVersionInfo = $showVersionInfo;
        $this->jsonApiClassMetadata = $metadataFactory;
    }

    public function setNavigator(GraphNavigatorInterface $navigator): void
    {
        parent::setNavigator($navigator);
        $this->visitor->setNavigator($navigator);
    }

    public function visitNull($data, array $type)
    {
        return $this->visitor->visitNull(...\func_get_args());
    }

    public function visitString(string $data, array $type)
    {
        return $this->visitor->visitString(...\func_get_args());
    }

    public function visitBoolean(bool $data, array $type)
    {
        return $this->visitor->visitBoolean(...\func_get_args());
    }

    public function visitDouble(float $data, array $type)
    {
        return $this->visitor->visitDouble(...\func_get_args());
    }

    public function visitInteger(int $data, array $type)
    {
        return $this->visitor->visitInteger(...\func_get_args());
    }

    public function visitArray(array $data, array $type)
    {
        return $this->visitor->visitArray(...\func_get_args());
    }

    public function startVisitingObject(ClassMetadata $metadata, object $data, array $type): void
    {
        $this->visitor->startVisitingObject(...\func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function endVisitingObject(ClassMetadata $metadata, object $data, array $type)
    {
        $rs = $this->visitor->endVisitingObject($metadata, $data, $type);

        if ($metadata->name === PaginatedRepresentation::class) {
            $result = [
                'data' => $rs['items'],
                'meta' => $rs['items'],
                'links' => $rs['items'],
            ];
            return $result;
        }

        if (true !== $metadata->isList && empty($rs)) {
            return new \ArrayObject();
        }

        if ($data instanceof Proxy || $data instanceof ORMProxy) {
            $class = get_parent_class($data);
        } else {
            $class = get_class($data);
        }

        /** @var JsonApiClassMetadata $classMetadata */
        $classMetadata = $this->jsonApiClassMetadata->getMetadataForClass($class);

        if (null === $classMetadata) {
            return $rs;
        }

        $result = array();

//        if (isset($rs[JsonApiEventSubscriber::EXTRA_DATA_KEY]['type'])) {
//            $language = new ExpressionLanguage();
//            $type =  $rs[JsonApiEventSubscriber::EXTRA_DATA_KEY]['type'];
//
//            $groups = $this->navigator->context->get('groups');
//            $groups = $groups instanceof None ? [] : $groups->get();
//
//            try {
//                $result['type'] = $language->evaluate($type, ['groups' => $groups]);
//            } catch (SyntaxError $e) {
//                $result['type'] = $type;
//            }
//        }
        $idField = $classMetadata->getIdField();

        $result['attributes'] = array_filter(
            $rs,
            function ($key) use ($idField) {
                switch ($key) {
                    case $idField:
                    case 'relationships':
                    case 'links':
                        return false;
                }

                if ($key === JsonApiEventSubscriber::EXTRA_DATA_KEY) {
                    return false;
                }

                return true;
            },
            ARRAY_FILTER_USE_KEY
        );

        if (isset($rs['relationships'])) {
            $result['relationships'] = $rs['relationships'];
        }

        if (isset($rs['links'])) {
            $result['links'] = $rs['links'];
        }

        if (isset($rs['id'])) {
            $result['id'] = $rs['id'];
        }

        if (isset($rs['type'])) {
            $result['type'] = $rs['type'];
        }

        return $result;
    }


    public function visitProperty(PropertyMetadata $metadata, $data): void
    {
        $this->visitor->visitProperty(...\func_get_args());
    }

    /**
     * @return bool
     */
    public function isJsonApiDocument()
    {
        return $this->isJsonApiDocument;
    }

    /**
     * @param mixed $root
     *
     * @return array
     */
    public function prepare($root)
    {
        if (is_array($root) && array_key_exists('data', $root)) {
            $data = $root['data'];
        } else {
            $data = $root;
        }

        if (($data instanceof \Traversable) && count($data) === 0 && !$this->isResource($data)) {
            $data = [];
        }

        $this->isJsonApiDocument = $this->validateJsonApiDocument($data);

        if ($this->isJsonApiDocument) {
            $meta = null;
            if (is_array($root) && isset($root['meta']) && is_array($root['meta'])) {
                $meta = $root['meta'];
            }

            return $this->buildJsonApiRoot($data, $meta);
        }

        return $root;
    }

    /**
     * Build json api root
     *
     * @param mixed      $data
     * @param array|null $meta
     *
     * @return array
     */
    protected function buildJsonApiRoot($data, array $meta = null)
    {
        if ($data instanceof ConstraintViolationListInterface) {
            $root = [
                'errors' => $data,
            ];
        } elseif ($data instanceof \Exception) {
            $root = [
                'errors' => [$data],
            ];
        } else {
            $root = [
                'data' => $data,
            ];
        }

        if ($meta) {
            $root['meta'] = $meta;
        }

        return $root;
    }

    /**
     * it is a JSON-API document if:
     *  - it is an object and is a JSON-API resource
     *  - it is an array containing objects which are JSON-API resources
     *  - it is empty (we cannot identify it)
     *
     * @param mixed $data
     *
     * @return bool
     */
    protected function validateJsonApiDocument($data)
    {
        if (is_null($data)) {
            return true;
        }

        if (is_array($data)) {
            return true;
        }

        if ((is_array($data) || $data instanceof \Traversable) && count($data) > 0 && $this->hasResource($data)) {
            return true;
        }

        if ($data instanceof ConstraintViolationListInterface) {
            return true;
        }

        if ($data instanceof \Exception) {
            return true;
        }

        return $this->isResource($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getResult($root)
    {
        if (false === $this->isJsonApiDocument) {
            return $this->visitor->getResult($root);
        }

        if ($root) {
            $data = array();
            $meta = array();
            $included = array();
            $links = array();
            $errors = array();

            if (array_key_exists('data', $root)) {
                $data = $root['data'];
            }

            if (isset($root['included'])) {
                $included = $root['included'];
            }

            if (isset($root['meta'])) {
                $meta = $root['meta'];
            }

            if (isset($root['links'])) {
                $links = $root['links'];
            }

            if (isset($root['errors'])) {
                $errors = $root['errors'];
            }

            if (!is_null($data)) {
                // filter out duplicate primary resource objects that are in `included`
                $included = array_udiff(
                    (array) $included,
                    (isset($data['type'])) ? array($data) : $data,
                    function ($a, $b) {
                        return strcmp(
                            $a['type'] . $a['id'],
                            $b['type'] . $b['id']
                        );
                    }
                );
            }

            // start building new root array
            $root = array();

            if ($this->showVersionInfo) {
                $root['jsonapi'] = array(
                    'version' => '1.0',
                );
            }

            if ($meta) {
                $root['meta'] = $meta;
            }

            if ($links) {
                $root['links'] = $links;
            }

            if (is_array($errors) && count($errors) > 0) {
                $root['errors'] = $errors;
            } else {
                $root['data'] = $data;
                if ($included) {
                    $data['included'] = array_values($included);
                }
            }
        }

        return $this->visitor->getResult($root);
    }

    /**
     * @param $items
     *
     * @return bool
     */
    private function hasResource($items)
    {
        foreach ($items as $item) {
            return $this->isResource($item);
        }

        return false;
    }

    /**
     * Check if the given variable is a valid JSON-API resource.
     *
     * @param $data
     *
     * @return bool
     */
    private function isResource($data)
    {
        if (is_object($data)) {
            if ($data instanceof Proxy || $data instanceof ORMProxy) {
                $class = get_parent_class($data);
            } else {
                $class = get_class($data);
            }

            /** @var JsonApiClassMetadata $metadata */
            if ($metadata = $this->jsonApiClassMetadata->getMetadataForClass($class)) {
                if ($metadata->getResource()) {
                    return true;
                }
            }
        }

        return false;
    }
}
