idct-soap-client
================

Class derived from PHP SoapClient to add support for connection and read timeouts.

Requires PHP 5.4+ with SoapClient and cUrl.

## usage

The usage is exactly the same as with PHP original SoapClient. The difference
comes with the initialization as the constructor allows to set multiple new
parameters which extend the original functionality.

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
Sets the maximum amount of seconds cUrl will try to establish connection with
the WebService before treating the request as failed. As a connectin timeout.
By default set to 0 which means *disabled*.

## tips

### Content-Type and SOAPAction

Protocol __SOAP 1.1__ requires SOAPAction header to be set. Method `buildHeaders`
automatically adds it during soap request. In __SOAP 1.2__ SOAPAction is included
 in `Content-type` header therefore if you need to overwrite `Content-type` with
__SOAP 1.2__ be sure to include `{SOAPACTION}` token for automatic replacement.

## contribution

If you find any issues or want to add new features please use the Issues or
Pull Request functions: code addition is much appreciated!

Before sending code be sure to run `fix_code.sh` to clean it up.

Thanks!
