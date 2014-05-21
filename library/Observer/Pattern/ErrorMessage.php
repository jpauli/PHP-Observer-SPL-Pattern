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
 * @subpackage Pattern
 * @author Julien Pauli <jpauli@php.net>
 * @copyright 2010 Julien Pauli <jpauli@php.net>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace Observer\Pattern;

/**
 * ErrorMessage
 *
 * @package Observer
 * @subpackage Pattern
 * @author Julien Pauli <jpauli@php.net>
 * @copyright 2010 Julien Pauli <jpauli@php.net>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 */
class ErrorMessage
{
	/**
	 * @var int
	 */
	private $errno;

	/**
	 * @var string
	 */
	private $errstr;

	/**
	 * @var string
	 */
	private $errfile;

	/**
	 * @var int
	 */
	private $errline;

	/**
	 * @var array
	 */
	private static $errorConsts;

	/**
	 * @var string
	 */
	const DEFAULT_ERROR_FMT = "%s: %s in %s on line %d";

	/**
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 */
	public function __construct(int $errno, string $errstr, string $errfile, int $errline)
	{
		$this->errfile     = $errfile;
		$this->errno       = $errno;
		$this->errstr      = $errstr;
		$this->errline     = $errline;
		if (!self::$errorConsts) {
			self::$errorConsts = \get_defined_constants(true)['Core'];
		}
	}

	/**
	 * @return string
	 */
	public function getErrLvl() : string
	{
		return \array_search($this->errno, self::$errorConsts);
	}

	/**
	 * @return int
	 */
	public function getErrno() : int
	{
		return $this->errno;
	}

	/**
	 * @return string
	 */
	public function getErrstr() : string
	{
		return $this->errstr;
	}

	/**
	 * @return string
	 */
	public function getErrfile() : string
	{
		return $this->errfile;
	}

	/**
	 * @return int
	 */
	public function getErrline() : int
	{
		return $this->errline;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return sprintf(self::DEFAULT_ERROR_FMT,
				$this->getErrLvl(), $this->errstr, $this->errfile, $this->errno);
	}
}
