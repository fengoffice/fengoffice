<?php
require_once 'example_autoload.php';

if ( count( $argc ) < 1 || count( $argc ) > 2 )
{
  echo "\nUsage: ezp32ezp4 <ezp3 xml filename> [<ezp4 xml filename>]\n";
  die();
}

$ezp3 = file_get_contents( $argv[1] );

$docEzp3 = new ezcDocumentXML( 'ezp3', $ezp3 );

$converter = new ezcDocumentEzp3ToEzp4( array( 'inline_custom_tags' => array( 'sub', 'sup', 'strike' ) ) );
$docEzp4 = $converter->convert( $docEzp3 );
$result = $docEzp4->getXML();

if ( isset( $argv[2] ) )
    file_put_contents( $argv[2], $result );
else
    echo "Docbook:\n" . $result;

?>