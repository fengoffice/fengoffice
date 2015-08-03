<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * HTTP::Header::Cache
 * 
 * PHP versions 4 and 5
 *
 * @category    HTTP
 * @package     HTTP_Header
 * @author      Wolfram Kriesing <wk@visionp.de>
 * @author      Michael Wallner <mike@php.net>
 * @copyright   2003-2005 The Authors
 * @license     BSD, revised
 * @version     CVS: $Id: Cache.php 7 2010-01-22 18:14:51Z acio $
 * @link        http://pear.php.net/package/HTTP_Header
 */

/**
 * Requires HTTP_Header
 */
require_once 'HTTP/Header.php';

/**
 * HTTP_Header_Cache
 * 
 * This package provides methods to easier handle caching of HTTP pages.  That 
 * means that the pages can be cached at the client (user agent or browser) and 
 * your application only needs to send "hey client you already have the pages".
 * 
 * Which is done by sending the HTTP-Status "304 Not Modified", so that your
 * application load and the network traffic can be reduced, since you only need
 * to send the complete page once.  This is really an advantage e.g. for 
 * generated style sheets, or simply pages that do only change rarely.
 * 
 * Usage:
 * <code>
 *  require_once 'HTTP/Header/Cache.php';
 *  $httpCache = new HTTP_Header_Cache(4, 'weeks');
 *  $httpCache->sendHeaders();
 *  // your code goes here
 * </code>
 * 
 * @package     HTTP_Header
 * @category    HTTP
 * @access      public
 * @version     $Revision: 7 $
 */
class HTTP_Header_Cache extends HTTP_Header
{
    /**
     * Constructor
     * 
     * Set the amount of time to cache.
     * 
     * @access  public
     * @return  object  HTTP_Header_Cache
     * @param   int     $expires 
     * @param   string  $unit
     */
    function HTTP_Header_Cache($expires = 0, $unit = 'seconds')
    {
        parent::HTTP_Header();
        $this->setHeader('Pragma', 'cache');
        $this->setHeader('Last-Modified', $this->getCacheStart());
        $this->setHeader('Cache-Control', 'private, must-revalidate, max-age=0');
        
        if ($expires) {
            if (!$this->isOlderThan($expires, $unit)) {
                $this->exitCached();
            }
            $this->setHeader('Last-Modified', time());
        }
    }

    /**
     * Get Cache Start
     * 
     * Returns the unix timestamp of the If-Modified-Since HTTP header or the
     * current time if the header was not sent by the client.
     * 
     * @access  public
     * @return  int     unix timestamp
     */
    function getCacheStart()
    {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && !$this->isPost()) {
            return strtotime(current($array = explode(';', 
                $_SERVER['HTTP_IF_MODIFIED_SINCE'])));
        }
        return time();
    }

    /**
     * Is Older Than
     * 
     * You can call it like this:
     * <code>
     *  $httpCache->isOlderThan(1, 'day');
     *  $httpCache->isOlderThan(47, 'days');
     * 
     *  $httpCache->isOlderThan(1, 'week');
     *  $httpCache->isOlderThan(3, 'weeks');
     * 
     *  $httpCache->isOlderThan(1, 'hour');
     *  $httpCache->isOlderThan(5, 'hours');
     * 
     *  $httpCache->isOlderThan(1, 'minute');
     *  $httpCache->isOlderThan(15, 'minutes');
     * 
     *  $httpCache->isOlderThan(1, 'second');
     *  $httpCache->isOlderThan(15);
     * </code>
     * 
     * If you specify something greater than "weeks" as time untit, it just 
     * works approximatly, because a month is taken to consist of 4.3 weeks.
     * 
     * @access  public
     * @return  bool    Returns true if requested page is older than specified.
     * @param   int     $time The amount of time.
     * @param   string  $unit The unit of the time amount - (year[s], month[s], 
     *                  week[s], day[s], hour[s], minute[s], second[s]).
     */
    function isOlderThan($time = 0, $unit = 'seconds')
    {
        if (!isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || $this->isPost()) {
            return true;
        }
        if (!$time) {
            return false;
        }
        
        switch (strtolower($unit))
        {
            case 'year':
            case 'years':
                $time *= 12;
            case 'month':
            case 'months':
                $time *= 4.3;
            case 'week':
            case 'weeks':
                $time *= 7;
            case 'day':
            case 'days':
                $time *= 24;
            case 'hour':
            case 'hours':
                $time *= 60;
            case 'minute':
            case 'minutes':
                $time *= 60;
        }
        
        return (time() - $this->getCacheStart()) > $time;
    }

    /**
     * Is Cached
     * 
     * Check whether we can consider to be cached on the client side.
     * 
     * @access  public
     * @return  bool    Whether the page/resource is considered to be cached.
     * @param   int     $lastModified Unix timestamp of last modification.
     */
    function isCached($lastModified = 0)
    {
        if ($this->isPost()) {
            return false;
        }
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && !$lastModified) {
            return true;
        }
        if (!$seconds = time() - $lastModified) {
            return false;
        }
        return !$this->isOlderThan($seconds);
    }
    
    /**
     * Is Post
     * 
     * Check if request method is "POST".
     * 
     * @access  public
     * @return  bool
     */
    function isPost()
    {
        return  isset($_SERVER['REQUEST_METHOD']) and
            'POST' == $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Exit If Cached
     * 
     * Exit with "HTTP 304 Not Modified" if we consider to be cached.
     * 
     * @access  public
     * @return  void
     * @param   int     $lastModified Unix timestamp of last modification.
     */
    function exitIfCached($lastModified = 0)
    {
        if ($this->isCached($lastModified)) {
            $this->exitCached();
        }
    }
    
    /**
     * Exit Cached
     * 
     * Exit with "HTTP 304 Not Modified".
     * 
     * @access  public
     * @return  void
     */
    function exitCached()
    {
        $this->sendHeaders();
        $this->sendStatusCode(304);
        exit;
    }
    
    /**
     * Set Last Modified
     * 
     * @access  public
     * @return  void
     * @param   int     $lastModified The unix timestamp of last modification.
     */
    function setLastModified($lastModified = null)
    {
        $this->setHeader('Last-Modified', $lastModified);
    }
}
?>
