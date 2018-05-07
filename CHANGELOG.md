# 0.2.1
* Reorganised code.
* Tagged release.

# 0.2.0
* Added PSR-4 improvements.
* Changed changelog.rst to CHANGELOG.md
* Dropped support for php 5.3

# 0.1.2
* Default value for the content-type header set to text/xml
* Added support for different content-type header values

# 0.1.1
* Packagist support
* Added changelog.rst
* Changed default value of `persistanceTimeout` in the constructor in order to
use ini `default_socket_timeout` value when `null`
* set parameter `timeoutInSeconds` of `setPersistanceTimeout` to default to
`null` to use `default_socket_timeout` for `persistanceFactor` when `null`
