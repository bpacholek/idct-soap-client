idct-soap-client
================

Class derived from PHP SoapClient to add support for connection and read timeouts.

Requires PHP 5+ with SoapClient and cUrl.

## usage

The usage is exactly the same as with PHP original SoapClient. The difference comes with the initialization as the constructor allows to set multiple new parameters which extend the original functionality.

### Persistance Factor

Can be set in the constructor or using ``setPersistanceFactor`` method.
Sets the amount of retries to conduct before treating the request as failed.
By default set to 1.

### Persistance Timeout

Can be set in the constructor or using ``setPersistanceTmeout`` method.
Sets the amount of seconds to wait by cUrl before treating a single request as failed. Acts as a read timeout.
By default set to 0 which means *disabled*.

### Negotiation Timeout

Can be set in the constructor or using ``setNegotiationTimeout`` method.
Sets the maximum amount of seconds cUrl will try to establish connection with the WebService before treating the request as failed. As a connectin timeout.
By default set to 0 which means *disabled. 
