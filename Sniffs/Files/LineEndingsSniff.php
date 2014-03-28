<?php
/**
 * PHP Version 5
 */

/**
 * Ensures \r\n line endings on windows and \n line endings on Linux and other hosts.
 *
 */
class CakePHP_Sniffs_Files_LineEndingsSniff implements PHP_CodeSniffer_Sniff {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
		'PHP',
		'JS',
		'CSS'
	);

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(T_OPEN_TAG);
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param integer $stackPtr The position of the current token in the stack passed in $tokens.
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		// We are only interested if this is the first open tag.
		if ($stackPtr !== 0) {
			if ($phpcsFile->findPrevious(T_OPEN_TAG, ($stackPtr - 1)) !== false) {
				return;
			}
		}

		$found = $phpcsFile->eolChar;
		$found = str_replace("\n", '\n', $found);
		$found = str_replace("\r", '\r', $found);

		$eolChar = $this->_serverOS() === 1 ? '\r\n' : '\n';

		if ($found !== $eolChar) {
			// Check for single line files without an EOL. This is a very special
			// case and the EOL char is set to \n when this happens.
			if ($found === '\n') {
				$tokens = $phpcsFile->getTokens();
				$lastToken = ($phpcsFile->numTokens - 1);
				if ($tokens[$lastToken]['line'] === 1
					&& $tokens[$lastToken]['content'] !== "\n"
				) {
					return;
				}
			}

			$error = 'End of line character is invalid; expected "%s" but found "%s"';
			$expected = $eolChar;
			$expected = str_replace("\n", '\n', $expected);
			$expected = str_replace("\r", '\r', $expected);
			$data = array(
				$expected,
				$found,
			);
			$phpcsFile->addError($error, $stackPtr, 'InvalidEOLChar', $data);
		}
	}

	protected function _serverOS() {
		$sys = strtoupper(PHP_OS);

		if (substr($sys, 0, 3) == "WIN") {
			return 1;
		} elseif ($sys == "LINUX") {
			return 2;
		} else {
			return 3;
		}
	}

}
