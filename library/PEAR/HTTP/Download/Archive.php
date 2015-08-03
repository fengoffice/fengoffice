<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * HTTP::Download::Archive
 * 
 * PHP versions 4 and 5
 *
 * @category   HTTP
 * @package    HTTP_Download
 * @author     Michael Wallner <mike@php.net>
 * @copyright  2003-2005 Michael Wallner
 * @license    BSD, revisewd
 * @version    CVS: $Id: Archive.php 7 2010-01-22 18:14:51Z acio $
 * @link       http://pear.php.net/package/HTTP_Download
 */

/**
 * Requires HTTP_Download
 */
require_once 'HTTP/Download.php';

/**
 * Requires System
 */
require_once 'System.php';

/** 
 * HTTP_Download_Archive
 * 
 * Helper class for sending Archives.
 *
 * @access   public
 * @version  $Revision: 7 $
 */
class HTTP_Download_Archive
{
    /**
     * Send a bunch of files or directories as an archive
     * 
     * Example:
     * <code>
     *  require_once 'HTTP/Download/Archive.php';
     *  HTTP_Download_Archive::send(
     *      'myArchive.tgz',
     *      '/var/ftp/pub/mike',
     *      HTTP_DOWNLOAD_BZ2,
     *      '',
     *      '/var/ftp/pub'
     *  );
     * </code>
     *
     * @see         Archive_Tar::createModify()
     * @static
     * @access  public
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     * @param   string  $name       name the sent archive should have
     * @param   mixed   $files      files/directories
     * @param   string  $type       archive type
     * @param   string  $add_path   path that should be prepended to the files
     * @param   string  $strip_path path that should be stripped from the files
     */
    function send($name, $files, $type = HTTP_DOWNLOAD_TGZ, $add_path = '', $strip_path = '')
    {
        $tmp = System::mktemp();
        
        switch ($type = strToUpper($type))
        {
            case HTTP_DOWNLOAD_TAR:
                include_once 'Archive/Tar.php';
                $arc = &new Archive_Tar($tmp);
                $content_type = 'x-tar';
            break;

            case HTTP_DOWNLOAD_TGZ:
                include_once 'Archive/Tar.php';
                $arc = &new Archive_Tar($tmp, 'gz');
                $content_type = 'x-gzip';
            break;

            case HTTP_DOWNLOAD_BZ2:
                include_once 'Archive/Tar.php';
                $arc = &new Archive_Tar($tmp, 'bz2');
                $content_type = 'x-bzip2';
            break;

            case HTTP_DOWNLOAD_ZIP:
                include_once 'Archive/Zip.php';
                $arc = &new Archive_Zip($tmp);
                $content_type = 'x-zip';
            break;
            
            default:
                return PEAR::raiseError(
                    'Archive type not supported: ' . $type,
                    HTTP_DOWNLOAD_E_INVALID_ARCHIVE_TYPE
                );
        }
        
        if ($type == HTTP_DOWNLOAD_ZIP) {
            $options = array(   'add_path' => $add_path, 
                                'remove_path' => $strip_path);
            if (!$arc->create($files, $options)) {
                return PEAR::raiseError('Archive creation failed.');
            }
        } else {
            if (!$e = $arc->createModify($files, $add_path, $strip_path)) {
                return PEAR::raiseError('Archive creation failed.');
            }
            if (PEAR::isError($e)) {
                return $e;
            }
        }
        unset($arc);
        
        $dl = &new HTTP_Download(array('file' => $tmp));
        $dl->setContentType('application/' . $content_type);
        $dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $name);
        return $dl->send();
    }
}
?>
