<?php
/*
* Logger class
*
* This is used for logging in the system
*
* @author Ken Lalobo
*
*/

namespace Mooti\Xizlr\Logger;

use \Psr\Log\LoggerInterface;
use \Psr\Log\LogLevel;

class Logger implements LoggerInterface
{
    private $id;
    private $logDate;
    private $applicationName;

    private $allowedLogLevels = array(
        LogLevel::EMERGENCY => LOG_EMERG,
        LogLevel::ALERT     => LOG_ALERT,
        LogLevel::CRITICAL  => LOG_CRIT,
        LogLevel::ERROR     => LOG_ERR,
        LogLevel::WARNING   => LOG_WARNING,
        LogLevel::NOTICE    => LOG_NOTICE,
        LogLevel::INFO      => LOG_INFO,
        LogLevel::DEBUG     => LOG_DEBUG
    );

    public function __construct()
    {
        $this->id               = uniqid();
        $this->logDate          = new \DateTime();
        $this->applicationName  = 'mooti';
    }

    /**
     * Set the applocation name. This helps in identifying log entries for this application
     *
     * @param string $applicationName
     * @return null
     */
    public function setApplicationName($applicationName)
    {
        $this->applicationName = $applicationName;
    }

    /**
     * Get the application. This helps in identifying log entries for this application
     *
     * @return string
     */
    public function getApplicationName()
    {
        return $this->applicationName;
    }

    /**
     * Set the log id. This helps in identifying related log entries
     *
     * @param string $id
     * @return null
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set the log id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the log date and time.
     *
     * @param DateTime $id
     * @return null
     */
    public function setDate(\DateTime $logDate)
    {
        $this->logDate = $logDate;
    }

    /**
     * Get the log date and time.
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->logDate;
    }

     /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return boolean
     */
    public function emergency($message, array $context = array())
    {
        return $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return boolean
     */
    public function alert($message, array $context = array())
    {
        return $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return boolean
     */
    public function critical($message, array $context = array())
    {
        return $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return boolean
     */
    public function error($message, array $context = array())
    {
        return $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return boolean
     */
    public function warning($message, array $context = array())
    {
        return $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return boolean
     */
    public function notice($message, array $context = array())
    {
        return $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return boolean
     */
    public function info($message, array $context = array())
    {
        return $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return boolean
     */
    public function debug($message, array $context = array())
    {
        return $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return boolean
     */
    public function log($level, $message, array $context = array())
    {
        if (array_key_exists($level, $this->allowedLogLevels) == false) {
            throw new LoggerException('Unknown log level: '.$level);
        }

        $backTrace = debug_backtrace();

        $locations = [];

        for ($i=2; $i>0; $i--) {
            if (empty($backTrace[$i]) == false) {
                $locations[] = [
                    'class'    => $backTrace[$i]['class'],
                    'function' => $backTrace[$i]['function'],
                    'line'     => (empty($backTrace[$i]['line'])?null:$backTrace[$i]['line'])
                ];
            }
        }

        $data = array(
            'id'          => $this->id,
            'logDate'     => $this->logDate->format('r'),
            'level'       => $level,
            'message'     => $message,
            'context'     => $context,
            'locations'   => $locations
        );

        $logMessage = $this->applicationName.'/xizlr['.$this->getMyPid().']: '.json_encode($data);

        $this->logToSystemlog($this->allowedLogLevels[$level], $logMessage);

        return true;
    }

    /**
     * Send a message to the system log
     *
     * @param integer $level
     * @param string  $message
     *
     * @return null
     */
    public function logToSystemlog($level, $message)
    {
        syslog($level, $logMessage);
    }

    /**
     * Get the process id
     *
     * @return integer
     */
    public function getMyPid()
    {
        return getmypid();
    }
}
