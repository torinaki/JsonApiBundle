Upgrade from 0.x to 1.x
=========
## New features
1. Upgraded JMS Serializer till 2.1
1. Upgraded JMS Serializer Bundle till 3.0

## BC breaks
1. Bundle supports PHP >=7.2
1. DateHandler default format changed from `\DateTime::ISO8601` to `\DateTime::ATOM`.
1. DateHandler supports only `json` and `json:api` formats. 

## Notes
1. `json` and `json:api` serializers should be configured by default
because it is required by `HttpRequestToParametersConverter` 
