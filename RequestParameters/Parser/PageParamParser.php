<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Parser;

use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Pagination\PageParamInterface;

/**
 * PageParamParser service to parse 'page' parameter for parameter converter
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class PageParamParser implements ParamParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($data, array $payload = []): array
    {
        if (!is_array($data)) {
            throw new ParamParserException(sprintf('Page param expected to be an array, %s given', gettype($data)));
        }

        $result = [];
        $result['number'] = array_key_exists('number', $data) && is_numeric($data['number'])
            ? $data['number']
            : PageParamInterface::DEFAULT_NUMBER;
        $result['size'] = array_key_exists('size', $data) && is_numeric($data['size'])
            ? $data['size']
            : PageParamInterface::DEFAULT_SIZE;

        return $result;
    }
}
