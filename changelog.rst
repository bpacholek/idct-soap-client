version 0.1.1 (2014-08-16)
================

* Added changelog.rst
* Changed default value of ``persistanceTimeout`` in the constructor in order to use ini ``default_socket_timeout`` value when ``null`
* set parameter ``timeoutInSeconds`` of ``setPersistanceTimeout`` to default to ``null` to use ``default_socket_timeout`` for ``persistanceFactor`` when ``null``