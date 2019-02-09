<?php

/*
 * PHP-Foundation-Core (https://github.com/delight-im/PHP-Foundation-Core)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Delight\Foundation;

use Delight\Foundation\Throwable\TemplateNotFoundError;
use Delight\Foundation\Throwable\TemplateSyntaxError;

/** Template manager that renders views */
final class TemplateManager {

	const CHARSET_DEFAULT = 'UTF-8';

	/** @var \Twig_Environment the Twig instance */
	private $twig;

	/**
	 * @param string $templatesPath the path to the directory containing the templates
	 * @param string $templatesCachePath the path to the directory where templates may be cached
	 */
	public function __construct($templatesPath, $templatesCachePath) {
		// create a new Twig instance
		$this->twig = new \Twig_Environment(
			new \Twig_Loader_Filesystem($templatesPath),
			array(
				'cache' => $templatesCachePath,
				'charset' => isset($_ENV['APP_CHARSET']) ? $_ENV['APP_CHARSET'] : self::CHARSET_DEFAULT,
				'debug' => false,
				'auto_reload' => true,
				'autoescape' => 'html'
			)
		);
	}

	/**
	 * Renders the template with the specified name
	 *
	 * Optionally, you can provide an array of data that the template will receive
	 *
	 * @param string $viewName the name of the template to render
	 * @param array $data (optional) the data to send to the template
	 * @return string the rendered template (usually HTML)
	 */
	public function render($viewName, $data = array()) {
		try {
			return $this->twig->render($viewName, $data);
		}
		catch (\Twig_Error_Loader $e) {
			throw new TemplateNotFoundError($e->getMessage());
		}
		catch (\Twig_Error_Syntax $e) {
			throw new TemplateSyntaxError($e->getMessage(), 0, $e);
		}
	}

	/**
	 * Adds a filter that can then be used inside of all templates via the `{{ myVariable | myFilter }}` syntax
	 *
	 * @param string $name the name of the filter under which it should be available
	 * @param callable $callback the function that performs the filtering (with the input as its single parameter)
	 */
	public function addFilter($name, callable $callback) {
		$this->twig->addFilter(new \Twig_SimpleFilter($name, $callback));
	}

	/**
	 * Adds a global variable that can then be accessed inside of all templates just like every other variable, e.g. `{{ myGlobal }}`
	 *
	 * Array elements and object properties can be accessed after a dot (`.`), e.g. `{{ myGlobal.property }}`
	 *
	 * @param string $name the name of the global variable under which it should be available
	 * @param mixed $value the value of the global to be used in templates
	 */
	public function addGlobal($name, $value) {
		$this->twig->addGlobal($name, $value);
	}

	/**
	 * Adds a function that can then be used inside of all templates via the `{{ myFunction(...) }}` syntax
	 *
	 * @param string $name the name of the function under which it should be available
	 * @param callable $callback the implementation of the function to be used in templates
	 */
	public function addFunction($name, callable $callback) {
		$this->twig->addFunction(new \Twig_SimpleFunction($name, $callback));
	}

	/**
	 * Changes the syntax recognized by the template engine
	 *
	 * @param string $commentStart the start delimiter for comments
	 * @param string $commentEnd the end delimiter for comments
	 * @param string $blockStart the start delimiter for blocks
	 * @param string $blockEnd the end delimiter for blocks
	 * @param string $variableStart the start delimiter for variables
	 * @param string $variableEnd the end delimiter for variables
	 * @param string $interpolationStart the start delimiter for interpolation
	 * @param string $interpolationEnd the end delimiter for interpolation
	 */
	public function setSyntax(
		$commentStart = null,
		$commentEnd = null,
		$blockStart = null,
		$blockEnd = null,
		$variableStart = null,
		$variableEnd = null,
		$interpolationStart = null,
		$interpolationEnd = null
	) {
		$this->twig->setLexer(new \Twig_Lexer($this->twig, array(
			'tag_comment' => array(empty($commentStart) ? '{#' : $commentStart, empty($commentEnd) ? '#}' : $commentEnd),
			'tag_block' => array(empty($blockStart) ? '{%' : $blockStart, empty($blockEnd) ? '%}' : $blockEnd),
			'tag_variable' => array(empty($variableStart) ? '{{' : $variableStart, empty($variableEnd) ? '}}' : $variableEnd),
			'interpolation' => array(empty($interpolationStart) ? '#{' : $interpolationStart, empty($interpolationEnd) ? '}' : $interpolationEnd)
		)));
	}

}
