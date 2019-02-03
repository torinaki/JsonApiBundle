<?php


namespace Mango\Bundle\JsonApiBundle\Tests\Fixtures;


use Mango\Bundle\JsonApiBundle\RequestParameters\GetParametersAbstract;

class RequestTestModel extends GetParametersAbstract
{
    /**
     * Returns list of available fields for filtering with their operators
     *
     * @return array
     */
    public function getAvailableFilterFields(): array
    {
        return [];
    }

    /**
     * Returns list of available fields for sorting
     *
     * @return array
     */
    public function getAvailableSortingFields(): array
    {
        return [];
    }

    /**
     * Returns operators for filter fields
     *
     * @return array
     *
     * todo: remove this method when operator will be passes using query
     */
    public static function getFilterFieldOperators(): array
    {
        return [];
    }
}
