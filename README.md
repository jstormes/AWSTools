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



## PHP in Docker

TBD

# To Do

* Allow `Exception` class to be passed as context and create a sample `Formatter`
* Better use of Stream Naming, not sure how but perhaps something with the `Formatter`
* Create examples of `try` `catch` use case
* Create examples of `set_error_handler` use case
* Create squelch option and use case examples