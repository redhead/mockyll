<?php

namespace Mockista\ClassGenerator;

/**
 * This class is part of Mockista library.
 * @see https://github.com/janmarek/mockista
 * @author Jiří Knesl, Jan Marek
 */
class ClassGeneratorException extends \RuntimeException
{

	const CODE_FINAL_CLASS_CANNOT_BE_MOCKED = 1;

}