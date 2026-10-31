<?php

use PHPUnit\Framework\TestCase;

require_once 'Zend/Session.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Zend_Session_StartupTest extends TestCase
{
    public function testNamespaceReusesAnActiveLegacyFormatSession(): void
    {
        $legacyId = 'LegacySessionIDWithUppercase0123456789';
        $_COOKIE[session_name()] = $legacyId;

        try {
            \Zend_Session::start();
            $this->assertSame($legacyId, \Zend_Session::getId());

            $namespace = new \Zend_Session_Namespace('LoginRegression');
            $namespace->value = 'persisted';
            $secondNamespace = new \Zend_Session_Namespace('LoginRegression');

            $this->assertSame('persisted', $secondNamespace->value);
            $this->assertSame($legacyId, \Zend_Session::getId());
        } finally {
            \Zend_Session::destroy(true);
        }
    }

    public function testNamespaceReusesANewSession(): void
    {
        try {
            \Zend_Session::start();
            $sessionId = \Zend_Session::getId();
            $namespace = new \Zend_Session_Namespace('LoginRegression');
            $namespace->value = 'persisted';

            $this->assertSame($sessionId, \Zend_Session::getId());
            $this->assertSame('persisted', $namespace->value);
        } finally {
            \Zend_Session::destroy(true);
        }
    }

    public function testInvalidExplicitIdIsReplacedBeforeStartup(): void
    {
        try {
            \Zend_Session::setId('invalid$session');
            \Zend_Session::start();

            $this->assertNotSame('invalid$session', \Zend_Session::getId());
            $this->assertTrue(\Zend_Session::isRegenerated());
        } finally {
            \Zend_Session::destroy(true);
        }
    }

    public function testDestroyedSessionCannotBeRestarted(): void
    {
        \Zend_Session::start();
        \Zend_Session::destroy(true);

        $this->expectException(\Zend_Session_Exception::class);
        $this->expectExceptionMessage('The session was explicitly destroyed during this request');

        \Zend_Session::start(true);
    }
}
