<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Parser;

/**
 * ParamParserInterface interface for parameter parsers
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
interface ParamParserInterface
{
    /**
     * Parses data into an array
     *
     * @param mixed $data    data to parse
     * @param array $payload payload
     * @return array
     */
    public function parse($data, array $payload = []): array;
}
