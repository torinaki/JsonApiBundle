# Change Log

## 2.0.0 (2019-05-07)

### BC breaks
In the scope of migration to new version of JMS Serializer 2.0 [\#15](https://github.com/ecentria/JsonApiBundle/pull/15)
were introduced multiple BC breaks:
* Upgraded JMS Serializer till 2.1
* Upgraded JMS Serializer Bundle till 3.0
* Now bundle supports PHP >=7.2
* `DateHandler` default format changed from `\DateTime::ISO8601` to `\DateTime::ATOM`
* Now `DateHandler` supports only `json` and `json:api` formats. `XML` support was not implemented 
