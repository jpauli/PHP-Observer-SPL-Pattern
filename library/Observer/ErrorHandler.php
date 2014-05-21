<?php
/**
* Observer-SPL-PHP-Pattern
*
* Copyright (c) 2010, Julien Pauli <jpauli@php.net>.
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions
* are met:
*
* * Redistributions of source code must retain the above copyright
* notice, this list of conditions and the following disclaimer.
*
* * Redistributions in binary form must reproduce the above copyright
* notice, this list of conditions and the following disclaimer in
* the documentation and/or other materials provided with the
* distribution.
*
* * Neither the name of Julien Pauli nor the names of his
* contributors may be used to endorse or promote products derived
* from this software without specific prior written permission.
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
* @package Observer
* @author Julien Pauli <jpauli@php.net>
* @copyright 2010 Julien Pauli <jpauli@php.net>
* @license http://www.opensource.org/licenses/bsd-license.php BSD License
*/
namespace Observer;

use Observer\Pattern\Observer;
use Observer\Pattern\ErrorMessage;

/**
* Base class for error handling.

* This class may not be perfect but is a very good implementation
* of the Subject/Observer pattern in PHP.
*
* @package Observer
* @author Julien Pauli <jpauli@php.net>
* @copyright 2010 Julien Pauli <jpauli@php.net>
* @license http://www.opensource.org/licenses/bsd-license.php BSD License
* @version Release: @package_version@
*/
final class ErrorHandler implements Pattern\Subject, \IteratorAggregate, \Countable
{
    /**
     * Singleton instance
     *
     * @var ErrorHandler
     */
    private static $instance;

    /**
     * @var ErrorMessage
     */
    private $error;

    /**
     * Wether or not fallback to PHP internal
     * error handler
     *
     * @var bool
     */
    private $fallBackToPHPErrorHandler = false;

    /**
     * Wether or not rethrow the PHP Error
     *
     * @var bool
     */
    private $rethrowException = true;

    /**
     * Weither or not clear the last error after
     * sending it to Listeners
     *
     * @var bool
     */
    private $clearErrorAfterSending = true;

    /**
     * Should ErrorHandler catch its listeners exception
     * while dispatching them ?
     *
     * @var bool
     */
    private $catchListenersException = true;

    /**
     * Listeners classes namespace
     *
     * @var string
     */
    private const LISTENERS_NS = "Listeners";

    /**
     * @var \SplObjectStorage
     */
    private $observers;

    /**
     * Retrieves singleton instance
     *
     * @return ErrorHandler
     */
    public static function getInstance(bool $andStart = false) : self
    {
        if (!self::$instance) {
            self::$instance = new self;
            if ($andStart) {
                self::$instance->start();
            }
        }
        return self::$instance;
    }

    /**
     * Singleton : can't be cloned
     */
    private function __clone() { }

    /**
     * Singleton : can't be faked-created by unserialize()
     */
    public function __wakeup()
    {
        throw new ErrorHandlerException("Can't unserialize such object");
    }

    /**
     * Singleton constructor
     */
    private function __construct()
    {
        $this->observers = new \SplObjectStorage();
    }

    /**
     * Factory to build some Listeners
     *
     * @param string $listener
     * @param array $args
     * @return object|null
     */
    public static function factory(string $listener, array $args = []) : ?Pattern\Observer
    {
        $class = sprintf('%s\%s\%s', __NAMESPACE__, self::LISTENERS_NS, $listener);

        try {
            $reflect = new \ReflectionClass($class);
            if ($reflect->implementsInterface(Observer::class)) {
                return $reflect->newInstanceArgs($args);
            }
        } catch (\ReflectionException $e) { }
    }

    /**
     * Method run by PHP's error handler
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errctx
     * @return bool
     */
    public function __invoke($errno, $errstr, $errfile = '', $errline = 0)
    {
        if(error_reporting() == 0) { // @ errors ignored
            return;
        }
        $this->error = new ErrorMessage($errno, $errstr, $errfile, $errline);
        $this->notify();
        if ($this->fallBackToPHPErrorHandler) {
            return false;
        }
    }

    /**
     * Method run by PHP's exception handler
     * @param \Throwable $e
     */
    public function exceptionHandler(\Throwable $e)
    {
        if ($e instanceof \Error) {
            $this($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
            if ($this->rethrowException) {
                goto rethrow;
            }
        }
rethrow:
        throw $e;
    }

    /**
     * Wether or not fallback to PHP internal
     * error handler
     *
     * @param bool $bool
     * @return ErrorHandler
     */
    public function setFallBackToPHPErrorHandler(bool $bool) : self
    {
        $this->fallBackToPHPErrorHandler = $bool;
        return $this;
    }

    /**
     * Wether or not fallback to PHP internal
     * error handler
     *
     * @return bool
     */
    public function getRethrowException() : bool
    {
        return $this->rethrowException;
    }

    /**
     * Wether or not rethrow PHP error
     *
     * @param bool $bool
     * @return ErrorHandler
     */
    public function setRethrowException(bool $bool) : self
    {
        $this->rethrowException = $bool;
        return $this;
    }

    /**
     * Wether or not rethrow PHP Error
     *
     * @return bool
     */
    public function getFallBackToPHPErrorHandler() : bool
    {
        return $this->fallBackToPHPErrorHandler;
    }

    /**
     * Wether or not clear the last error after
     * sending it to Listeners
     *
     * @param bool $bool
     * @return ErrorHandler
     */
    public function setClearErrorAfterSending(bool $bool) : self
    {
        $this->clearErrorAfterSending = $bool;
        return $this;
    }

    /**
     * Wether or not clear the last error after
     * sending it to Listeners
     *
     * @return bool
     */
    public function getClearErrorAfterSending() : bool
    {
        return $this->clearErrorAfterSending;
    }

    /**
     * Wether or not the ErrorHandler should catch its
     * listeners' exceptions while notifying() them
     *
     * @param bool $bool
     * @return ErrorHandler
     */
    public function setCatchListenersException(bool $bool) : self
    {
        $this->catchListenersException = $bool;
        return $this;
    }

    /**
     * Wether or not the ErrorHandler should catch its
     * listeners' exceptions while notifying() them
     *
     * @return bool
     */
    public function getCatchListenersException() : bool
    {
        return $this->catchListenersException;
    }

    /**
     * Starts the ErrorHandler
     *
     * @return ErrorHandler
     */
    public function start() : self
    {
        set_error_handler($this);
        set_exception_handler([$this, 'exceptionHandler']);

        return $this;
    }

    /**
     * Stops the ErrorHandler
     *
     * @return ErrorHandler
     */
    public function stop() : self
    {
        restore_error_handler();
        set_exception_handler(null);

        return $this;
    }

    /**
     * Observer pattern : shared method
     * to all observers
     *
     * @return string
     */
    public function getError() : ?ErrorMessage
    {
        return $this->error;
    }

    /**
     * Resets the singleton instance
     *
     * @param bool $andRecreate
     * @return ErrorHandler|void
     */
    public static function resetInstance(bool $andRecreate = false) : ?self
    {
        self::$instance = null;
        return $andRecreate ? self::getInstance() : null;
    }

    /**
     * Observer pattern : attaches observers
     *
     * @param Pattern\Observer $obs
     * @return ErrorHandler
     */
    public function attach(?Pattern\Observer ...$obs) : self
    {
        foreach ($obs as $o) {
            $this->observers->attach($o);
        }
        return $this;
    }

    /**
     * Observer pattern : detaches observers
     *
     * @param Pattern\Observer $obs
     * @return ErrorHandler
     */
    public function detach(?Pattern\Observer ...$obs) : self
    {
        foreach ($obs as $o) {
            $this->observers->detach($o);
        }
        return $this;
    }

    /**
     * Observer pattern : notify observers
     *
     * @param Pattern\Observer $obs
     * @return int
     */
    public function notify() : int
    {
        $i = 0;
        try {
            foreach ($this as $observer) {
                $observer->update($this);
                $i++;
            }
        } catch(\Exception $e) {
            if (!$this->catchListenersException) {
                throw new ErrorHandlerException("Exception while notifying observer", null, $e);
            }
        } finally {
            if ($this->clearErrorAfterSending) {
                $this->error = null;
            }
        }

        return $i;
    }

    /**
     * IteratorAggregate
     *
     * @return \Iterator
     */
    public function getIterator() : \Iterator
    {
        return $this->observers;
    }

    /**
     * Countable
     *
     * @return int
     */
    public function count() : int
    {
        return count($this->observers);
    }

    /**
     * Hack for 1.attach('Listener')
     *          2.detach('Listener')
     *
     * @param string $funct
     * @param array $args
     * @return ErrorHandler
     * @throws \BadMethodCallException
     */
    public function __call($funct, $args)
    {
        if (preg_match('#(?P<prefix>at|de)tach(?P<listener>\w+)#', $funct, $matches)) {
            $meth     = $matches['prefix'] . 'tach';
            $listener = ucfirst(strtolower($matches['listener']));
            return $this->$meth(self::factory($listener, $args));
        }
        throw new \BadMethodCallException("unknown method $funct");
    }
}
