<?php
namespace Test\Mooti\Xizlr\Logger;

require dirname(__FILE__).'/../vendor/autoload.php';

use Mooti\Xizlr\Logger\Logger;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function settersAndGettersSucceed()
    {
        $applicationName = 'test-app';
        $id = uniqid();
        $date = new \DateTime;

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $logger->setApplicationName($applicationName);
        $logger->setId($id);
        $logger->setDate($date);

        self::assertSame($applicationName, $logger->getApplicationName());
        self::assertSame($id, $logger->getId());
        self::assertSame($date, $logger->getDate());
    }

    /**
     * @test
     * @dataProvider logLevels
     */
    public function loglevelsSucceed($level, $message, $context)
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();

        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo($level),
                $this->equalTo($message),
                $this->equalTo($context)
            )
            ->will($this->returnValue(true));

        self::assertSame(true, $logger->$level($message, $context));
    }

    /**
     * @test
     */
    public function logSucceeds()
    {
        $level           = 'emergency';
        $sysLevel        = LOG_EMERG;
        $message         = 'hello';
        $context         = ['hello'];
        $date            = new \DateTime;
        $applicationName = 'test-app';
        $id              = uniqid();
        $pid             = 1001;

        $sysMessage = 'test-app/xizlr[1001]: {"id":"'.$id.'","logDate":"'.$date->format('r').'","level":"emergency","message":"hello","context":["hello"]';

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['logToSystemlog', 'getMyPid'])
            ->getMock();

        $logger->setDate($date);
        $logger->setApplicationName($applicationName);
        $logger->setId($id);

        $logger->expects($this->once())
            ->method('getMyPid')
            ->will($this->returnValue($pid));

        $logger->expects($this->once())
            ->method('logToSystemlog')
            ->with(
                $this->equalTo($sysLevel),
                $this->stringContains($sysMessage)
            );

        self::assertSame(true, $logger->log($level, $message, $context));
    }

    /**
     * @test
     * @expectedException Mooti\Xizlr\Logger\LoggerException
     */
    public function logSucceedsThrowsLoggerException()
    {
        $level = 'unknown';

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $logger->log($level, '', []);
    }

    /**
     * data providers
     */

    public function logLevels()
    {
        return [
            ['emergency', 'this is a big emergency',  ['emergency', 'context']],
            ['alert', 'this is a big alert',  ['alert', 'context']],
            ['critical', 'this is a big critical',  ['critical', 'context']],
            ['error', 'this is a big error',  ['error', 'context']],
            ['warning', 'this is a big warning',  ['warning', 'context']],
            ['notice', 'this is a big notice',  ['notice', 'context']],
            ['info', 'this is a big info',  ['info', 'context']],
            ['debug', 'this is a big debug',  ['debug', 'context']]
        ];
    }
}
