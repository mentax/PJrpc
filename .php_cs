<?php

$finder = PhpCsFixer\Finder::create()
	->exclude('var')
	->in(__DIR__);

return PhpCsFixer\Config::create()
	->setIndent("\t")
	->setRiskyAllowed(true)
	->setRules([
		'@Symfony' => true,
		'array_syntax' => ['syntax' => 'short'],
		'concat_space' => ['spacing' => 'one'],
		'cast_spaces' => ['space' => 'none'],
		'phpdoc_align' => false,
		'no_unneeded_curly_braces' => false,
		'phpdoc_var_without_name'=>false
	])
	->setFinder($finder);
