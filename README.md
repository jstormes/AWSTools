# CloudWatch
A wrapper around AWS services to make the easier to use in PHP.

# Quickstart

## PHP on Bare Metal

Clone project 

`git clone git@github.com:jstormes/AWSTools.git`

`cd AWSTools`

Use composer to install AWS dependencies

`composer install`

Make sure AWS is set up for you CLI environment.

You should have .aws in your home directory with proper
`credentials` and `config` files.  Your access_key MUST 
have permissions to create CloudWatch Logs and Streams.

Run the demos:

`./bin/cli-demo-minimal.php`

`./bin/cli-demo-lazy-vs-direct.php`

`./bin/cli-demo-dynamic-severity.php`

`./bin/cli-demo-formatters.php`

Look in the CloudWatch logs to see the output from the examples.

## PHP in Docker

TBD

# Known issues

* If you have two PHP apps running at the same time and logging to the same stream, you will have issues
  with sequenceToken.  You need to make sure each PHP Application instance (aka Docker Instance)
  gets its own stream.  My current ideas is to use the HOSTNAME as it is unique to each Docker
  instance as part of the stream name.
  
* An extension of the previous error is two long-running scrips on the same docker instance
  It might be necessary to use shared memory and lock to make sure the sequenceToken is correct
  across the two running PHP instances.  Embrace the statelessness of the cloud!!!  Or let the call 
  fail and try to recover the sequenceToken, that might be a performance nightmare.

# To Do

* Allow `Exception` class to be passed as context and create a sample `Formatter`
* Better use of Stream Naming, not sure how but perhaps something with the `Formatter`
* Create examples of `try` `catch` use case
* Create examples of `set_error_handler` use case
* Create ZF2 DI use case example
* Create squelch option and use case examples
* Create method for chaining `formatter`
* Create example of using `formatter` to call a new instance of `Logs`