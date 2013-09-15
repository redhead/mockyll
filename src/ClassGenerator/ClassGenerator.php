<?php

namespace Mockista\ClassGenerator;

/**
 * This class is part of Mockista library.
 * @see https://github.com/janmarek/mockista
 * @author Jiří Knesl, Jan Marek
 */
class ClassGenerator extends BaseClassGenerator
{

	function generate($inheritedClass, $newName)
	{
		$extends = class_exists($inheritedClass) ? "extends" : "implements";
		$methods = $this->methodFinder->methods($inheritedClass);

		list($out, $inheritedClass) = $this->namespaceCheck("", $inheritedClass);

		$isFinal = $this->isFinal($inheritedClass);
		if ($isFinal) {
			throw new ClassGeneratorException("Cannot mock final class", ClassGeneratorException::CODE_FINAL_CLASS_CANNOT_BE_MOCKED);
		}

		$out .= "class $newName $extends $inheritedClass\n{\n";
		$out .= '
    public $__mockController;
    
    public $__calledPartials = array();
    
	function __construct($callParent = false, $arguments = null)
	{
        if($callParent) {
            call_user_func_array(array("parent", "__construct"), $arguments);
        }
	}

	function __call($name, $args)
	{
        if(isset($this->__calledPartials[$name])) {
            return call_user_func_array(array("parent", $name), $args);
        }
		return $this->__mockController->onCall($this, $name, $args);
	}
';
		foreach ($methods as $name => $method) {
			if ("__call" == $name || "__construct" == $name || $method['final']) {
				continue;
			}
			if ("__destruct" == $name) {
				$out .= '
	function __destruct()
	{
	}
'; 			continue;
			}
			$out .= $this->generateMethod($name, $method);
		}
		$out .= "}\n";

		return $out;
	}

	private function isFinal($inheritedClass)
	{
		if (!class_exists($inheritedClass)) {
			return false;
		}
		$klass = new \ReflectionClass($inheritedClass);

		return $klass->isFinal();
	}

	private function generateMethod($methodName, $method)
	{
		$params = $this->generateParams($method['parameters']);
		$static = $method['static'] ? 'static ' : '';
		$passedByReference = $method['passedByReference'] ? '&' : '';
		$out = "
	{$static}function $passedByReference$methodName($params)
	{
        if(isset(\$this->__calledPartials['$methodName'])) {
            return call_user_func_array(array('parent', '$methodName'), func_get_args());
        }
        return \$this->__mockController->onCall(\$this, '$methodName', func_get_args());
	}
";

		return $out;
	}

}