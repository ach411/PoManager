<?php

namespace Ach\PoManagerBundle\VariableSubstitute;

class AchVariableSubstitute
{
/**
* Take a generic message text pattern
* and replace all the variables by their value
* a variable is designated by % sign: %variable%
* 
* @param string $msgPattern, array $substitutes
*/

    public function varSub($msgPattern, $substitutes)
    {
	$search = array('QTY' => '%qty%', 'PN' => '%pn%', 'DESC' => '%desc%', 'LINK' => '%link%');
	
	//$replace = array($substitutes['QTY'], $substitutes['PN'], $substitutes['DESC'], $substitutes['LINK']);
	$replace = $substitutes;

	return str_replace($search, $replace, $msgPattern);
	
    }

}