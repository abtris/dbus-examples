<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2009, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    Whitewashing
 * @author     Benjamin Eberlei <kontakt@beberlei.de>
 * @copyright  2009 Benjamin Eberlei <kontakt@beberlei.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */

/**
 * Use in PHPUnit XML Configuration
 * 
 * <?xml version="1.0" encoding="utf-8"?>
 * <phpunit>
 *     <listeners>
 *         <listener class="Whitewashing_PHPUnit_DbusListener" file="/home/benny/code/php/phpunit/dbus_listener.php" />
 *     </listeners>
 * </phpunit>
 */
class Whitewashing_PHPUnit_DbusListener implements PHPUnit_Framework_TestListener
{
    private $_errors = 0;
    private $_failures = 0;
    private $_startTime = null;
    private $_suiteName = "";
    private $_tests = 0;
    private $_startedSuites = 0;
    private $_endedSuites = 0;

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->_errors++;
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->_failures++;
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {

    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {

    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if($this->_startedSuites == 0) {
            $this->_startTime = time();
            $this->_suiteName = $suite->getName();
        }
        $this->_startedSuites++;
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->_endedSuites++;

        if($this->_startedSuites <= $this->_endedSuites) {
            $this->_notify();
        }
    }

    private function _notify()
    {
        if (extension_loaded('dbus') === false) {
            return;
        }

        $d = new Dbus(Dbus::BUS_SESSION);
        $n = $d->createProxy(
            "org.freedesktop.Notifications", // connection name
            "/org/freedesktop/Notifications", // object
            "org.freedesktop.Notifications" // interface
        );
        $n->Notify(
            'Whitewashing_PHPUnit', 
            new DBusUInt32(0),
            'phpunit', 
            'PHPUnit Test-Run Report',
            sprintf(
                "Suite: %s\n%d tests run in %s minutes.\n%d errors, %d failures...", 
                $this->_suiteName,
                $this->_tests, 
                (date('i:s', time() - $this->_startTime)),
                $this->_errors,
                $this->_failures
            ),
            new DBusArray(DBus::STRING, array()),
            new DBusDict(DBus::VARIANT, array()),
            1000
        );

    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->_tests++;
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {

    }
}