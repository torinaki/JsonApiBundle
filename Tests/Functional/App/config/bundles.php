<?php
/*
 * This file is part of the ecentria group, inc. software.
 *
 * (c) 2019, ecentria group, Inc
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use JMS\SerializerBundle\JMSSerializerBundle;

return [
    new FrameworkBundle(),
    new JMSSerializerBundle(),
    new MangoJsonApiBundle(),
];
