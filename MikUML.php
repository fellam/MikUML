<?php

/**
 * File holding the MikUML class
 *
 * @author Michele Fella <michele.fella@gmail.com>
 * @file
 * @ingroup MikUML
 */

/*
 *
 * NOTE: This extension currently works only on *NIX servers
 * Defines a specific parser functions that must clear the cache to be useful.
 *
 * <mikuml>umlcode</mikuml> Depends on plantuml extension.
 * process and returns the plant uml object AFTER processiong the raw wikitext (old limitation of plantuml, not verified on version 0.6)
 *
 * Author: Michele Fella [http://meta.wikimedia.org/wiki/User:Michele.Fella]
 * Version 1.0
 *
 */

// Not a valid entry point, skip unless MEDIAWIKI is defined
if (! defined ( 'MEDIAWIKI' )) {
	die ( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

// Avoid unstubbing $wgParser too early on modern (1.12+) MW versions, as per r35980
if (defined ( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' )) {
	$wgHooks ['ParserFirstCallInit'] [] = 'wfMikUML';
} else {
	$wgExtensionFunctions [] = 'wfMikUML';
}

$wgExtensionCredits ['parserhook'] [] = array (
		'name' => 'MikUML',
		'version' => '1.0',
		'url' => 'https://www.mediawiki.org/wiki/Extension:MikUML',
		'author' => 'Michele Fella',
		'description' => 'Defines parser for PlantUML.' 
);
function wfMikUML() {
	global $wgParser;
	// global $wgExtMikUML;
	
	// $wgExtMikUML = new ExtMikUML();
	// $wgParser->setFunctionHook( 'uml', array( &$wgExtMikUML, 'uml' ) );
	$wgParser->setHook ( 'mikuml', 'mikuml' );
}

// function wfMikUMLLanguageGetMagic( &$magicWords, $langCode ) {
// switch ( $langCode ) {
// default:
// $magicWords['uml'] = array( 0, 'uml' );
// }
// return true;
// }
function mikuml($umlcode, $args, Parser $parser, PPFrame $frame) {
	// $umlcode = str_replace ( "<br>", "\r\n", $umlcode );
	// $umlcode = str_replace ( "&gt;", ">", $umlcode );
	$umlcode = $parser->internalParse ( $umlcode );
	$umlcode = str_replace ( "<br>", "\r\n", $umlcode );
	$umlcode = str_replace ( "<br/>", "\r\n", $umlcode );
	$umlcode = str_replace ( "<br />", "\r\n", $umlcode );
	$umlcode = str_replace ( "&gt;", ">", $umlcode );
	$umlcode = str_replace ( "&lt;", "<", $umlcode );
	$umlcode = preg_replace ( "/<a\s(.+?)href=\"(.+?)\">(.+?)<\/a>/is", "[[$2 $3]]", $umlcode );
	$umlcode = $umlcode . "\n";
	if (is_null ( $umlcode ) || $umlcode === '') {
		throw new MikUMLException ( 'umlcode is null or empty' );
	}
	if (! function_exists ( 'mb_convert_encoding' )) {
		throw new MikUMLException ( 'PlantUML extension not found!' );
	}
	$result = "";
	$result = renderUML ( $umlcode, $args, $parser, $frame );
	return array (
			$result,
			'noparse' => true,
			'isHTML' => true 
	);
}
