<?php

error_reporting(E_ALL);

/**
 * UriProvider implementation based on PHP microtime and rand().
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package common
 * @subpackage uri
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Any implementation of the AbstractUriProvider class aims at providing unique
 * to client code. It should take into account the state of the Knowledge Base
 * avoid collisions. The AbstractUriProvider::provide method must be implemented
 * subclasses to return a valid URI.
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 */
require_once('common/uri/class.AbstractUriProvider.php');

/* user defined includes */
// section 10-13-1-85--341437fc:13634d84b3e:-8000:000000000000199B-includes begin
// section 10-13-1-85--341437fc:13634d84b3e:-8000:000000000000199B-includes end

/* user defined constants */
// section 10-13-1-85--341437fc:13634d84b3e:-8000:000000000000199B-constants begin
// section 10-13-1-85--341437fc:13634d84b3e:-8000:000000000000199B-constants end

/**
 * UriProvider implementation based on PHP microtime and rand().
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package common
 * @subpackage uri
 */
class common_uri_MicrotimeRandUriProvider
    extends common_uri_AbstractUriProvider
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Generates a URI based on the value of PHP microtime() and rand().
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return string
     */
    public function provide()
    {
        $returnValue = (string) '';

        // section 10-13-1-85--341437fc:13634d84b3e:-8000:000000000000199E begin
        $modelUri = core_kernel_classes_Session::singleton()->getNameSpace();
		$dbWrapper = core_kernel_classes_DbWrapper::singleton();
		$uriExist = false;
		do{
			list($usec, $sec) = explode(" ", microtime());
        	$uri = $modelUri .'#i'. (str_replace(".","",$sec."".$usec)) . rand(0, 1000);
			$sqlResult = $dbWrapper->execSql(
				"select count(subject) as num from statements where subject = '".$uri."'"
			);
			if (!$sqlResult-> EOF){
				$found = (int)$sqlResult->fields['num'];
				if($found > 0){
					$uriExist = true;
				}
			}
		}while($uriExist);
		
		$returnValue = $uri;
        // section 10-13-1-85--341437fc:13634d84b3e:-8000:000000000000199E end

        return (string) $returnValue;
    }

} /* end of class common_uri_MicrotimeRandUriProvider */

?>