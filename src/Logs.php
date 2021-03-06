<?php


namespace JStormes\AWSwrapper;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\Result;

class Logs
{
    /** @var CloudWatchLogsClient  */
    private $cloudWatchLogsClient;

    /** @var string */
    private $sequenceToken = '0';

    /** @var string  */
    private $logGroup = '';

    /** @var string  */
    private $logStreamPrefix = '';

    /** @var string  */
    private $system = '';

    /** @var string  */
    private $application = '';

    /** @var array */
    private $formatters = [];

    /** @var GenericLogFormatter|null  */
    private $defaultMsgFormatter = null;

    /**
     * Logs constructor.
     * $config = [
     *    'profile' => 'default',
     *    'region' => 'us-west-2',
     *    'version' => 'latest',
     *    'logGroup' => "testGroup",
     *    'logStreamPrefix' => "StreamName",
     *    'system' => 'system',
     *    'application' => str_replace('.php','',basename(__FILE__))
     * ];
     * @param array $config
     * @throws \Exception
     */
    function __construct(array $config) {

        if (isset($config['logStreamPrefix'])){
            $this->setLogStreamPrefix($config['logStreamPrefix']);
        }
        else {
            throw new \Exception('"logStreamPrefix" not in config, "logStreamPrefix" is required.');
        }

        if (isset($config['system']))
            $this->setSystem($config['system']);
        else
            throw new \Exception('"system" no set in config, "system" is required.');
        $logGroup = '/'.$this->getSystem();

        if (isset($config['application'])){
            $this->setApplication($config['application']);
            $logGroup .= '/'.$this->getApplication();
        }

        if (isset($config['logGroup']))
            if (strlen($config['logGroup'])>1)
                $logGroup .= "/".$config['logGroup'];
        $this->setLogGroup($logGroup);

        if (isset($config['formatters'])) {
            if (is_array($config['formatters'])) {
                foreach ($config['formatters'] as $formatter) {
                    if (!is_subclass_of($formatter, FormatterInterface::class)) {
                        throw new \Exception('Invalid formatter ', $formatter);
                    }
                }
                $this->formatters= $config['formatters'];
            }
        }

        if (!isset($config['defaultFormatter'])) {
            $this->defaultMsgFormatter = new GenericLogFormatter();
        }

        $this->cloudWatchLogsClient = new CloudWatchLogsClient($config);
    }

    private function logGroupExists($groupName): bool {

        try {
            $this->cloudWatchLogsClient->describeLogStreams([
                "logGroupName" => $groupName
            ]);
        }
        catch(\Exception $ex) {
            return false;
        }
        return true;
    }

    private function createLogGroup($logGroup) {

        $this->cloudWatchLogsClient->createLogGroup([
            'logGroupName' => $logGroup,
        ]);
    }

    private function logStreamExists($groupName, $streamName):bool {

        $results = $this->cloudWatchLogsClient->describeLogStreams([
            "logGroupName" => $groupName,
            "logStreamNamePrefix" => $streamName
        ]);

        if (isset($results['logStreams'][0]["logStreamName"])) {
            if ($results['logStreams'][0]["logStreamName"] === $streamName) {
                return true;
            }
        }

        return false;
    }

    private function createLogStream($logGroup, $streamName) {

        if (!$this->logGroupExists($logGroup)) {
            $this->createLogGroup($logGroup);
        }

        if (!$this->logStreamExists($logGroup, $streamName)) {
            $this->cloudWatchLogsClient->createLogStream([
                'logGroupName' => $logGroup,
                'logStreamName' => $streamName
            ]);
        }

    }

    private function log( $message) : Result  {

        $retry = 10;

        for ($i=0;$i<$retry;$i++){
            try {
                $results = $this->cloudWatchLogsClient->putLogEvents(array(

                    'logGroupName' => $this->getLogGroup(),
                    'logStreamName' => $this->getLogStream(),
                    'logEvents' => array(
                        array(
                            'timestamp' => round(microtime(true) * 1000),
                            'message' => $message,
                        ),
                    ),
                    'sequenceToken' => $this->getSequenceToken()
                ));

                $this->setSequenceToken($results['nextSequenceToken']);

                return $results;
            }
            catch (\Exception $ex) {

                // Drill down into error message to the JSON formatted part.
                $jsonMsg = strstr($ex->getMessage(),"{");
                $jsonMsg = strstr(substr($jsonMsg,1), "{");

                $jsonError = json_decode($jsonMsg);

                switch ($jsonError->__type) {

                    case "DataAlreadyAcceptedException":
                    case "InvalidSequenceTokenException":
                        // sequenceToken cache miss
                        $this->setSequenceToken($jsonError->expectedSequenceToken);
                        break;
                    case "ResourceNotFoundException":
                        // logStream does not exists yet
                        $this->createLogStream($this->getLogGroup(),$this->getLogStream());
                        $this->setSequenceToken(0);
                        break;

                    default:
                        throw $ex;
                }

            }
        }

        throw new \Exception("Retries exceeded trying to write to log.");
    }

    private function logProxy(string $function, string $message, $context=null) {

        /** @var FormatterInterface $formatter */
        $formatter=null;
        foreach($this->formatters as $formatter) {
            if ($formatter->isCogent($function,$message, $context)) {
                $this->log($formatter->format($function,$message, $context));
                return;
            }
        }

        $this->log($this->defaultMsgFormatter->format($function, $message, $context));

    }

    /**
     * @param string $message
     * @param array $context
     */
    public function critical(string $message, $context=null) {
        $this->logProxy(__FUNCTION__, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function error(string $message, $context=null) {
        $this->logProxy(__FUNCTION__, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function warning(string $message, $context=null) {
        $this->logProxy(__FUNCTION__, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function monitor(string $message, $context=null) {
        $this->logProxy(__FUNCTION__, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function info(string $message, $context=null) {
        $this->logProxy(__FUNCTION__, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function debug(string $message, $context=null) {
        $this->logProxy(__FUNCTION__, $message, $context);
    }

    public function __call($function, $arg) {
        if (isset($arg)) {
            $message = '';
            $context=null;
            if (isset($arg[0]))
                $message = $arg[0];
            if (isset($arg[1]))
                $context = $arg[1];
            $this->logProxy($function, $message, $context);
            return;
        }

        throw new \Exception('invalid function call: '.$function);
    }

    /**
     * @return string
     */
    private function getSequenceToken(): string
    {
        return $this->sequenceToken;
    }

    /**
     * @param string $sequenceToken
     * @return Logs
     */
    private function setSequenceToken(string $sequenceToken): Logs
    {
        $this->sequenceToken = $sequenceToken;
        return $this;
    }

    /**
     * @return string
     */
    private function getLogGroup(): string
    {
        return $this->logGroup;
    }

    /**
     * @param string $logGroup
     * @return Logs
     */
    private function setLogGroup(string $logGroup): Logs
    {
        $this->logGroup = $logGroup;
        return $this;
    }

    /**
     * @return string
     */
    private function getLogStreamPrefix(): string
    {
        return $this->logStreamPrefix;
    }

    /**
     * @param string $logStreamPrefix
     * @return Logs
     */
    private function setLogStreamPrefix(string $logStreamPrefix): Logs
    {
        $this->logStreamPrefix = $logStreamPrefix;
        return $this;
    }

    private  function getLogStream() : string
    {
        $today = date("Y-m-d");
        return $this->getLogStreamPrefix()."_".$today;
    }

    /**
     * @return string
     */
    private function getSystem(): string
    {
        return $this->system;
    }

    /**
     * @param string $system
     * @return Logs
     */
    private function setSystem(string $system): Logs
    {
        $this->system = $system;
        return $this;
    }

    /**
     * @return string
     */
    private function getApplication(): string
    {
        return $this->application;
    }

    /**
     * @param string $application
     * @return Logs
     */
    private function setApplication(string $application): Logs
    {
        $this->application = $application;
        return $this;
    }

}