<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\Common\Proxy\Proxy as ORMProxy;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata as JsonApiClassMetadata;
use Mango\Bundle\JsonApiBundle\EventListener\Serializer\JsonEventSubscriber;
use Metadata\MetadataFactoryInterface;
use PhpOption\None;
use Symfony\Component\ExpressionLanguage;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class JsonApiSerializationVisitor implements SerializationVisitorInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @var bool
     */
    protected $showVersionInfo;

    /**
     * @var bool
     */
    protected $isJsonApiDocument = false;

    /** @var JsonSerializationVisitor */
    private $jsonSerializationVisitor;

    /** @var array */
    private $included;

    /** @var \SplStack */
    private $documentMetadataStack;

    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        $showVersionInfo = false,
        int $options = JSON_PRESERVE_ZERO_FRACTION
    ) {
        $this->jsonSerializationVisitor = new JsonSerializationVisitor($options);
        $this->metadataFactory = $metadataFactory;
        $this->showVersionInfo = $showVersionInfo;
        $this->documentMetadataStack = new \SplStack();
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
    public function getResult($data)
    {
        if (false === $this->isJsonApiDocument) {
            return $this->jsonSerializationVisitor->getResult($data);
        }

        $root = $data;

        if ($root) {

            $data = array();
            $meta = array();
            $included = array();
            $links = array();
            $errors = array();

            if (array_key_exists('data', $root)) {
                $data = $root['data'];
            }

            if (isset($this->included)) {
                $included = $this->included;
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

            if (null !== $data) {
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
                    $root['included'] = array_values($included);
                }
            }

        }

        return $this->jsonSerializationVisitor->getResult($root);
    }

    /**
     * {@inheritdoc}
     */
    public function endVisitingObject(ClassMetadata $metadata, object $data, array $type)
    {
        $rs = $this->jsonSerializationVisitor->endVisitingObject($metadata, $data, $type);

        [$id, $type, $relationships, $links] = $this->documentMetadataStack->pop();
        if ($rs instanceof \ArrayObject) {
            return [];
        }

        // TODO: solve issue with undefined Proxy classes
        if ($data instanceof Proxy || $data instanceof ORMProxy) {
            $class = get_parent_class($data);
        } else {
            $class = get_class($data);
        }

        /** @var JsonApiClassMetadata $jsonApiMetadata */
        $jsonApiMetadata = $this->metadataFactory->getMetadataForClass($class);

        if (null === $jsonApiMetadata) {
            return $rs;
        }

        $result = array();

        if (isset($type)) {

            // TODO: implement expression language
//            $language = new ExpressionLanguage\ExpressionLanguage();
//            $groups = $context->hasAttribute('groups') ? $context->getAttribute('groups') : [];
//            $groups = $groups instanceof None ? [] : $groups->get();

//            try {
//                $result['type'] = $language->evaluate($type, ['groups' => $groups]);
//            } catch (ExpressionLanguage\SyntaxError $e) {
                $result['type'] = $type;
//            }
        }

        if (isset($id)) {
            $result['id'] = $id;
        }

        $idField = $jsonApiMetadata->getIdField();

        $result['attributes'] = array_filter(
            $rs,
            function ($key) use ($idField) {
                switch ($key) {
                    case $idField:
                    case 'relationships':
                    case 'links':
                        return false;
                }

                if ($key === JsonEventSubscriber::EXTRA_DATA_KEY) {
                    return false;
                }

                return true;
            },
            ARRAY_FILTER_USE_KEY
        );

        if (!empty($relationships)) {
            $result['relationships'] = $relationships;
        }

        if (isset($links)) {
            $result['links'] = $links;
        }

        return $result;
    }

    /**
     * @param $items
     *
     * @return bool
     */
    protected function hasResource($items)
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
    protected function isResource($data)
    {
        if (is_object($data)) {
            if ($data instanceof Proxy || $data instanceof ORMProxy) {
                $class = get_parent_class($data);
            } else {
                $class = get_class($data);
            }

            /** @var JsonApiClassMetadata $metadata */
            if ($metadata = $this->metadataFactory->getMetadataForClass($class)) {
                if ($metadata->getResource()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitNull($data, array $type)
    {
        return $this->jsonSerializationVisitor->visitNull($data, $type);
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitString(string $data, array $type)
    {
        return $this->jsonSerializationVisitor->visitString($data, $type);
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitBoolean(bool $data, array $type)
    {
        return $this->jsonSerializationVisitor->visitBoolean($data, $type);
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitDouble(float $data, array $type)
    {
        return $this->jsonSerializationVisitor->visitDouble($data, $type);
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitInteger(int $data, array $type)
    {
        return $this->jsonSerializationVisitor->visitInteger($data, $type);
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return array|\ArrayObject
     */
    public function visitArray(array $data, array $type)
    {
        return $this->jsonSerializationVisitor->visitArray($data, $type);
    }

    /**
     * Called before the properties of the object are being visited.
     *
     * @param mixed $data
     * @param array $type
     */
    public function startVisitingObject(ClassMetadata $metadata, object $data, array $type): void
    {
        $this->jsonSerializationVisitor->startVisitingObject($metadata, $data, $type);
    }

    /**
     * @param mixed $data
     */
    public function visitProperty(PropertyMetadata $metadata, $data): void
    {
        $this->jsonSerializationVisitor->visitProperty($metadata, $data);
    }

    /**
     * Called before serialization/deserialization starts.
     */
    public function setNavigator(GraphNavigatorInterface $navigator): void
    {
        $this->jsonSerializationVisitor->setNavigator($navigator);
    }

    public function setIncluded(array $included)
    {
        $this->included = $included;
    }

    public function pushDocumentMetadata(string $id, string $type, ?array $relationships = null, ?array $links = null):void
    {
        $this->documentMetadataStack->push([$id, $type, $relationships, $links]);
    }

    public function popDocumentMetadata(): array
    {
        return $this->documentMetadataStack->pop();
    }
}
