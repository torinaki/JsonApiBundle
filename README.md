# JsonApiBundle

Integration of JSON API with Symfony which supports JMS Serializer 2.0. 

Note! Old version of JMS Serializer 1.0 support will end soon. 
Please use JsonApiBundle version [1.0](https://github.com/ecentria/JsonApiBundle/tree/1.0/) instead.

Code License: 
[Resources/meta/LICENSE](Resources/meta/LICENSE)

# Table of Contents
1. [Get Started](#get-started)
    - [Requirements](#requirements)
    - [Installation](#installation)
    - [Configuration](#configuration)
1. [Usage](#usage)
    - [Serialization configuration](#serialization-configuration)
        - [YAML](#yaml)
        - [Annotations](#annotations)
    - [Serialization and API response](#serialization-and-api-response)
    - [Request parameters validation and converting](#request-parameters-validation-and-converting)
    - [Pagination with Pagerfanta](#pagination-with-pagerfanta)
    - [Routing](#routing)
4. [Open API (Swagger) documentation](#open-api-swagger-documentation)
1. [DEPRECATED documentation](#deprecated-documentation)

# Get Started

## Requirements

JsonApiBundle integrates with Symfony >=3.

JsonApiBundle requires [JMS Serializer 2.0](https://github.com/schmittjoh/serializer). 
Please use version [1.0](https://github.com/ecentria/JsonApiBundle/tree/1.0/) if you are looking for
[JMS Serializer 1.0](https://github.com/schmittjoh/serializer/tree/1.x) support.

## Installation

Install required package:
```bash
composer require ecentria/json-api-bundle:2.0-dev
```

Register bundles in your `AppKernel`:
```php
    public function registerBundles()
    {
        $bundles = [
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Mango\Bundle\JsonApiBundle\MangoJsonApiBundle(),
        ];

        return $bundles;
    }
```

## Configuration

Configure JMS serializer:
```yaml
jms_serializer:
    property_naming:
        separator: '_'
    metadata:
        auto_detection: true
        directories:
            sabio-entity:
                namespace_prefix: "Your\\Entity"                     # <------ Replace with yours entities namespace
                path: "@YourAppBundle/Resources/config/serializer/entity"   # <------ Replace with path where you will put serialization configs
```

Configure JsonApiBundle:
```yaml
mango_json_api:
    base_uri: ~
    catch_exceptions: true
```

# Usage
We assume that you are already familiar with JMS serializer.
If not please following original JMS serialization documentation: [https://jmsyst.com/libs/serializer](https://jmsyst.com/libs/serializer)

## Serialization configuration
### YAML
It is preferable to use YAML, but you can use annotations as well. The only difference that you should specify type and idField as following:
YAML example:
```yaml
Your\Entity\User:
    resource:
        type: users
        idField: id
```

### Annotations
Annotation example:
```php
<?php
/**
 * User model
 *
 * @JsonApi\Resource(
 *     type="users",
 *     showLinkSelf=true,
 *     absolute=true
 * )
 */
class User
{
    /**
     * Brand ID
     *
     * @var integer
     * @JsonApi\Id()
     * @Jms\Type("integer")
     */
    private $id;
}
```

## Serialization and API response
```php
<?php
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiResponse;

class UserController extends Controller
{
    public function getUsersAction()
    {
        $serializer = $this->get('jms_serializer');
        return new JsonApiResponse($serializer->serialize($users));
    }
}
```

## Request parameters validation and converting

Create class validation 
```php
<?php

namespace App\Parameters;

use Mango\Bundle\JsonApiBundle\RequestParameters\GetParametersAbstract;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Filtering\FilterParamInterface;

class BrandGetParameters extends GetParametersAbstract
{
    public function getAvailableFilterFields(): array
    {
        return [
            'brandId',
            'code',
            'name',
        ];
    }

    public function getAvailableSortingFields(): array
    {
        return [
            'brandId',
            'code',
            'name',
        ];
    }

    public static function getFilterFieldOperators(): array
    {
        return [
            'code' => FilterParamInterface::OPERATOR_CONTAINS,
            'name' => FilterParamInterface::OPERATOR_CONTAINS,
        ];
    }
}
```

Add following document block for controller method:
```php
    /**
     * @Sensio\ParamConverter(
     *     "parameters",
     *     class="App\Parameters\BrandGetParameters",
     *     converter = "json_api.request_params.converter.http_request_converter",
     *     options={"query", "validate"}
     * )
     */
```

## Pagination with Pagerfanta
```php
<?php

namespace App\Controller;

use App\Parameters\BrandGetParameters;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BrandController extends Controller
{
    /**
     * @Sensio\ParamConverter(
     *     "parameters",
     *     class="App\Parameters\BrandGetParameters",
     *     converter = "json_api.request_params.converter.http_request_converter",
     *     options={"query", "validate"}
     * )
     */
    public function getBrandsAction(BrandGetParameters $parameters)
    {
        if (!$parameters->isValid()) {
            return new JsonApiResponse(
                $this->serialize($parameters->getViolations()),
                JsonApiResponse::HTTP_BAD_REQUEST
            );
        }

        $brandsPager = new Pagerfanta(
            new DoctrineORMAdapter(
                $this->get('sabio_app.brand')->getListQuery($parameters)
            )
        );
        $brandsPager->setMaxPerPage($parameters->getPage()->getSize());
        $brandsPager->setCurrentPage($parameters->getPage()->getNumber());

        return new JsonApiResponse($this->serialize($brandsPager));
    }
}
```

## Routing
Not yet implemented. Coming soon

# Open API (Swagger) documentation
TBD

# DEPRECATED documentation
...but still might useful in some cases

## Annotations
### @Resource
This will define your class as a JSON-API resource, and you can optionally set it's type name.
> This annotation can be defined on a class.

```php
use Mango\Bundle\JsonApiBundle\Configuration\Annotation as JsonApi;

/**
 * @JsonApi\Resource(type="posts", showLinkSelf=true, absolute=false)
 */
 class Post 
 {
  // ...
 }
```
| Property      | Default | Required  | Content   | Info  |
| ---           | ---     | ---       | ---       | ---   |
| type          | ~       | No        | string    | If left default, it will resolve its type dynamically based on the short class name. |
| showLinkSelf  | true    | No        | boolean   | Add `self` link to the resource |
| absolute      | false   | No        | boolean   | Enables absolute urls creation |

### @Id (optional, it defaults to `id`)
This will define the property that will be used as the `id` of a resource. It needs to be unique for every resource of the same type.
> This annotation can be defined on a property.

```php
use Mango\Bundle\JsonApiBundle\Configuration\Annotation as JsonApi;

/**
 * @JsonApi\Resource(type="posts")
 */
 class Post 
 {
    /**
     * @JsonApi\Id
     */
    protected $uuid;
 }
```

### @Relationship
This will define a relationship that can be either a `oneToMany` or `manyToOne`. Optionally you can set `includeByDefault` to include (sideload) the relationship with it's primary resource.
> This annotation can be defined on a property.

```php
use Mango\Bundle\JsonApiBundle\Configuration\Annotation as JsonApi;

/**
 * @JsonApi\Resource(type="posts")
 */
 class Post 
 {
    // ..
    
    /**
     * @JsonApi\Relationship(includeByDefault=true, showLinkSelf=false, showLinkRelated=false, absolute=false)
     */
    protected $comments;
 }
```
| Property              | Default | Required  | Content   | Info  |
| ---                   | ---     | ---       | ---       | ---   |
| includeByDefault      | false   | No        | boolean   | This will include (sideload) the relationship with it's primary resource |
| showData              | false   | No        | boolean   | Shows `data`, which consists of ids of the relationship data |
| showLinkSelf          | false   | No        | boolean   | Add `self` link of the relationship |
| showLinkRelated       | false   | No        | boolean   | Add `related` link of the relationship |
| absolute              | false   | No        | boolean   | Enables absolute urls creation |

## Configuration Reference
```yaml
# app/config/config.yml

mango_json_api:
    show_version_info: true
```

## Serialization JSON VS JSON:API
```php
$serializer = $container->get('jms_serializer');

$serializer->serialize($object, 'json'); // raw json
$serializer->serialize($object, 'json:api'); // json:api

$serializer->deserialize($string, \Some\Type:class, 'json'); // from raw json
$serializer->deserialize($string, \Some\Type:class, 'json:api'); // from json:api
```

## Example response
> GET /api/channels

```json
{
    "jsonapi": {
        "version": "1.0"
    },
    "meta": {
        "page": 1,
        "limit": 10,
        "pages": 1,
        "total": 4
    },
    "data": [
        {
            "type": "channels",
            "id": 5,
            "attributes": {
                "code": "WEB-UK",
                "name": "UK Webstore",
                "description": null,
                "url": "localhost",
                "color": "Blue",
                "enabled": true,
                "created-at": "2015-07-16T12:11:50+0000",
                "updated-at": "2015-07-16T12:11:50+0000",
                "locales": [],
                "currencies": [],
                "payment-methods": [],
                "shipping-methods": [],
                "taxonomies": []
            },
            "relationships": {
                "workspace": {
                    "data": {
                        "type": "workspaces",
                        "id": 18
                    }
                }
            }
        },
        {
            "type": "channels",
            "id": 6,
            "attributes": {
                "code": "WEB-NL",
                "name": "Dutch Webstore",
                "description": null,
                "url": null,
                "color": "Orange",
                "enabled": true,
                "created-at": "2015-07-16T12:11:50+0000",
                "updated-at": "2015-07-16T12:11:50+0000",
                "locales": [],
                "currencies": [],
                "payment-methods": [],
                "shipping-methods": [],
                "taxonomies": []
            },
            "relationships": {
                "workspace": {
                    "data": {
                        "type": "workspaces",
                        "id": 18
                    }
                }
            }
        },
        {
            "type": "channels",
            "id": 7,
            "attributes": {
                "code": "WEB-US",
                "name": "United States Webstore",
                "description": null,
                "url": null,
                "color": "Orange",
                "enabled": true,
                "created-at": "2015-07-16T12:11:50+0000",
                "updated-at": "2015-07-16T12:11:50+0000",
                "locales": [],
                "currencies": [],
                "payment-methods": [],
                "shipping-methods": [],
                "taxonomies": []
            },
            "relationships": {
                "workspace": {
                    "data": {
                        "type": "workspaces",
                        "id": 18
                    }
                }
            }
        },
        {
            "type": "channels",
            "id": 8,
            "attributes": {
                "code": "MOBILE",
                "name": "Mobile Store",
                "description": null,
                "url": null,
                "color": "Orange",
                "enabled": true,
                "created-at": "2015-07-16T12:11:50+0000",
                "updated-at": "2015-07-16T12:11:50+0000",
                "locales": [],
                "currencies": [],
                "payment-methods": [],
                "shipping-methods": [],
                "taxonomies": []
            },
            "relationships": {
                "workspace": {
                    "data": {
                        "type": "workspaces",
                        "id": 18
                    }
                }
            }
        }
    ],
    "included": [
        {
            "type": "workspaces",
            "id": 18,
            "attributes": {
                "name": "First Workspace"
            },
            "relationships": {
                "channels": {
                    "links": {
                        "related": "/workspaces/18/channels"
                    }
                }
            }
        }
    ]
}
```
