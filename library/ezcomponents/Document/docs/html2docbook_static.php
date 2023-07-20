<?php
require_once 'html2docbook_autoload.php';

if ( count( $argc ) < 1 || count( $argc ) > 2 )
{
  echo "\nUsage: html2docbook <html filename> [<docbook filename>]\n";
  die();
}

$xhtml = file_get_contents( $argv[1] );

$docXhtml = new ezcDocumentXML( 'xhtml', $xhtml );
$docDocbook = ezcDocumentXhtmlToDocbook::convert( $docXhtml );
$result = $docDocbook->getXML();

if ( isset( $argv[2] ) )
    file_put_contents( $argv[2], $result );
else
    echo "Docbook:\n" . $result;

?>