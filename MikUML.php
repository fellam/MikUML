<?php

/**
 * File holding the MikUML class
 *
 * @author Michele Fella <michele.fella@gmail.com>
 * @file
 * @ingroup MikUML
 */

/*
 
 NOTE: This extension currently works only on *NIX servers 
 Defines a specific parser functions that must clear the cache to be useful.
 
 {#uml:umlcode}} Depends on plantuml extension.
		process and returns the plant uml object AFTER processiong the raw wikitext (old limitation of plantuml, not verified on version 0.6)

 Author: Michele Fella [http://meta.wikimedia.org/wiki/User:Michele.Fella]
 Version 1.0 
 
*/
 
# Not a valid entry point, skip unless MEDIAWIKI is defined
if ( !defined( 'MEDIAWIKI' ) ) {
   die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}
 
$wgExtensionFunctions[] = 'wfMikUML';
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'MikUML',
	'version' => '1.0',
	'url' => 'https://www.mediawiki.org/wiki/Extension:MikUML',
	'author' => 'Michele Fella',   
	'description' => 'Defines parser for PlantUML.'
);
 
$wgHooks['LanguageGetMagic'][] = 'wfMikUMLLanguageGetMagic';
 
function wfMikUML() {
	global $wgParser, $wgExtMikUML;
 
	$wgExtMikUML = new ExtMikUML();
  $wgParser->setFunctionHook( 'uml', array( &$wgExtMikUML, 'uml' ) );
}
 
function wfMikFunctionsLanguageGetMagic( &$magicWords, $langCode ) {
	switch ( $langCode ) {
	default:
		$magicWords['uml']    = array( 0, 'uml' );
	}
	return true;
}
 
class ExtMikUML {
 
	function arg( &$parser, $name = '', $default = '' ) {
		global $wgRequest;
		$parser->disableCache();
		return $wgRequest->getVal($name, $default);
	}
 
  function uml( &$parser, $umlcode = "", $attrs = "", $imagetype = 'svg'  ) {
		//$parser->disableCache();
		global $plantumlImagetype;
		$replace = "<br>";
		$umlcode = str_replace($replace,"\r\n",$umlcode);
		if(is_null($umlcode)||$umlcode===''){
			throw new MikFunctionsException('umlcode is null or empty');
		}
		if(!function_exists('mb_convert_encoding')) {
			throw new MikFunctionsException('PlantUML extension not found!');
		}
		$result = "";
		if(!is_null($imagetype)) {
				$prev_plantumlImagetype=$plantumlImagetype;
				switch ($imagetype) {
					case 'svg':
					case 'png': 
    				$plantumlImagetype = $imagetype;
    			break;
				}
		}
		$result=renderUML($umlcode, array(), $parser);
		$plantumlImagetype=$prev_plantumlImagetype;
		foreach (explode(',',$attrs) as $key => $value) {
			$a=explode('=',$value);
			//print $a[0]."=".$a[1]."<br/>";
			if(count($a)>1){
				$result = preg_replace('/'.$a[0].':\d+px/i',''.$a[0].':'.$a[1],$result);
			}
			#print "$result <br/>";
		}	
		#exit;
		return array( $result, 'noparse' => true, 'isHTML' => true );
	}
	
}
