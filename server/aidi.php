<?php 
include 'utils.php';

date_default_timezone_set('UTC');

// -----------------------------------------------------------------------------------------------
// Hardcoded parameter (These parameters can be overwritten by creating the file /config/options.php)
$GLOBALS['config']['DATADIR'] = 'data'; // Data subdirectory
$GLOBALS['config']['CONFIG_FILE'] = $GLOBALS['config']['DATADIR'].'/config.php'; // Configuration file (user login/password)
$GLOBALS['config']['DATASTORE'] = $GLOBALS['config']['DATADIR'].'/datastore.php'; // Data storage file.
$GLOBALS['config']['LINKS_PER_PAGE'] = 20; // Default links per page.
$GLOBALS['config']['IPBANS_FILENAME'] = $GLOBALS['config']['DATADIR'].'/ipbans.php'; // File storage for failures and bans.
$GLOBALS['config']['BAN_AFTER'] = 4;        // Ban IP after this many failures.
$GLOBALS['config']['BAN_DURATION'] = 1800;  // Ban duration for IP address after login failures (in seconds) (1800 sec. = 30 minutes)
$GLOBALS['config']['OPEN_SHAARLI'] = false; // If true, anyone can add/edit/delete links without having to login
$GLOBALS['config']['HIDE_TIMESTAMPS'] = false; // If true, the moment when links were saved are not shown to users that are not logged in.
$GLOBALS['config']['ENABLE_THUMBNAILS'] = true; // Enable thumbnails in links.
$GLOBALS['config']['CACHEDIR'] = 'cache'; // Cache directory for thumbnails for SLOW services (like flickr)
$GLOBALS['config']['PAGECACHE'] = 'pagecache'; // Page cache directory.
$GLOBALS['config']['ENABLE_LOCALCACHE'] = true; // Enable Shaarli to store thumbnail in a local cache. Disable to reduce webspace usage.
$GLOBALS['config']['PUBSUBHUB_URL'] = ''; // PubSubHubbub support. Put an empty string to disable, or put your hub url here to enable.
$GLOBALS['config']['UPDATECHECK_FILENAME'] = $GLOBALS['config']['DATADIR'].'/lastupdatecheck.txt'; // For updates check of Shaarli.
$GLOBALS['config']['UPDATECHECK_INTERVAL'] = 86400 ; // Updates check frequency for Shaarli. 86400 seconds=24 hours
                                          // Note: You must have publisher.php in the same directory as Shaarli index.php
// -----------------------------------------------------------------------------------------------
// You should not touch below (or at your own risks !)
// Optionnal config file.
if (is_file($GLOBALS['config']['DATADIR'].'/options.php')) require($GLOBALS['config']['DATADIR'].'/options.php');

define('shaarli_version','0.0.41 beta');
define('PHPPREFIX','<?php /* '); // Prefix to encapsulate data in php code.
define('PHPSUFFIX',' */ ?>'); // Suffix to encapsulate data in php code.
// http://server.com/x/shaarli --> /shaarli/
define('WEB_PATH', substr($_SERVER["REQUEST_URI"], 0, 1+strrpos($_SERVER["REQUEST_URI"], '/', 0)));


// Directories creations (Note that your web host may require differents rights than 705.)
if (!is_writable(realpath(dirname(__FILE__)))) die('<pre>ERROR: Shaarli does not have the right to write in its own directory ('.realpath(dirname(__FILE__)).').</pre>');
if (!is_dir($GLOBALS['config']['DATADIR'])) { mkdir($GLOBALS['config']['DATADIR'],0705); chmod($GLOBALS['config']['DATADIR'],0705); }
if (!is_dir('tmp')) { mkdir('tmp',0705); chmod('tmp',0705); } // For RainTPL temporary files.
if (!is_file($GLOBALS['config']['DATADIR'].'/.htaccess')) { file_put_contents($GLOBALS['config']['DATADIR'].'/.htaccess',"Allow from none\nDeny from all\n"); } // Protect data files.
// Second check to see if Shaarli can write in its directory, because on some hosts is_writable() is not reliable.
if (!is_file($GLOBALS['config']['DATADIR'].'/.htaccess')) die('<pre>ERROR: Shaarli does not have the right to write in its data directory ('.realpath($GLOBALS['config']['DATADIR']).').</pre>');
if ($GLOBALS['config']['ENABLE_LOCALCACHE'])
{
    if (!is_dir($GLOBALS['config']['CACHEDIR'])) { mkdir($GLOBALS['config']['CACHEDIR'],0705); chmod($GLOBALS['config']['CACHEDIR'],0705); }
    if (!is_file($GLOBALS['config']['CACHEDIR'].'/.htaccess')) { file_put_contents($GLOBALS['config']['CACHEDIR'].'/.htaccess',"Allow from none\nDeny from all\n"); } // Protect data files.
}




class linkdb implements Iterator, Countable, ArrayAccess
{
    public $days; // Assoc array of date (20141001) to (an assoc array of linkdate to link)
    // not saved, but computed on readdb()
    public $links; // List of links (associative array. Key=linkdate (eg. "20110823_124546"), value= associative array (keys:title,description...)
    public $urls;  // List of all recorded URLs (key=url, value=linkdate) for fast reserve search (url-->linkdate)
    public $keys;  // List of linkdate keys (for the Iterator interface implementation)
    public $position; // Position in the $this->keys array. (for the Iterator interface implementation.)
    private $loggedin; // Is the used logged in ? (used to filter private links)
    private $secret;

    // Constructor:
    function __construct($isLoggedIn)
    // Input : $isLoggedIn : is the used logged in ?
    {
        $this->loggedin = $isLoggedIn;
        $this->checkdb(); // Make sure data file exists.
        $this->readdb();  // Then read it.
    }

    // ---- Countable interface implementation
    public function count() { return count($this->links); }

    // ---- ArrayAccess interface implementation
    public function offsetSet($offset, $value)
    {
        if (!$this->loggedin) die('You are not authorized to add a link.');
        if (empty($value['linkdate']) || empty($value['url'])) die('Internal Error: A link should always have a linkdate and url.');
        if (empty($offset)) die('You must specify a key.');
        $this->links[$offset] = $value;
        $this->urls[$value['url']]=$offset;
    }
    public function offsetExists($offset) { return array_key_exists($offset,$this->links); }
    public function offsetUnset($offset)
    {
        if (!$this->loggedin) die('You are not authorized to delete a link.');
        $url = $this->links[$offset]['url']; unset($this->urls[$url]);
        unset($this->links[$offset]);
    }
    public function offsetGet($offset) { return isset($this->links[$offset]) ? $this->links[$offset] : null; }

    // ---- Iterator interface implementation
    function rewind() { $this->keys=array_keys($this->links); rsort($this->keys); $this->position=0; } // Start over for iteration, ordered by date (latest first).
    function key() { return $this->keys[$this->position]; } // current key
    function current() { return $this->links[$this->keys[$this->position]]; } // current value
    function next() { ++$this->position; } // go to next item
    function valid() { return isset($this->keys[$this->position]); }    // Check if current position is valid.

    // ---- Misc methods
    private function checkdb() // Check if db directory and file exists.
    {
        if (!file_exists($GLOBALS['config']['DATASTORE'])) // Create a dummy database for example.
        {
            $this->links = array();
            $link = array('title'=>'About Daniel','url'=>"http://about.me/daniel.low",'description'=>'Test','private'=>0,'linkdate'=>'1406069989000','tags'=>'cool-dude');
            $this->links[$link['linkdate']] = $link;
            file_put_contents($GLOBALS['config']['DATASTORE'], PHPPREFIX.base64_encode(gzdeflate(serialize(array('secret'=>$this->secret, 'links'=>$this->links)))).PHPSUFFIX); // Write database to disk
        }
        if (!isset($this->secret))
            $this->secret = md5("AIDIpassword");
    }

    // Read database from disk to memory
    private function readdb()
    {
        // Read data
        $temp=(file_exists($GLOBALS['config']['DATASTORE']) ? unserialize(gzinflate(base64_decode(substr(file_get_contents($GLOBALS['config']['DATASTORE']),strlen(PHPPREFIX),-strlen(PHPSUFFIX))))) : array() );
        // var_dump($temp);
        // Note that gzinflate is faster than gzuncompress. See: http://www.php.net/manual/en/function.gzdeflate.php#96439

        $this->links = isset($temp['links']) ? $temp['links']: array();
        $this->secret = isset($temp['secret'])? $temp['secret'] : md5("AIDIpassword");


        // If user is not logged in, filter private links.
        if (!$this->loggedin)
        {
            $toremove=array();
            foreach($this->links as $link) { if ($link['private']!=0) $toremove[]=$link['linkdate']; }
            foreach($toremove as $linkdate) { unset($this->links[$linkdate]); }
        }

        // Keep the list of the mapping URLs-->linkdate up-to-date.
        $this->urls=array();
        $this->days = convertFilteredToDays($this->links);
        foreach($this->links as $link) { 
            $this->urls[$link['url']]=$link['linkdate'];  // make $urls
        }

    }

    // Save database from memory to disk.
    public function savedb()
    {
        if (!$this->loggedin) die('You are not authorized to change the database.');
        file_put_contents($GLOBALS['config']['DATASTORE'], PHPPREFIX.base64_encode(gzdeflate(serialize(array('secret'=>$this->secret, 'links'=>$this->links)))).PHPSUFFIX);
        invalidateCaches();
    }

    // Returns the link for a given URL (if it exists). false it does not exist.
    public function getLinkFromUrl($url)
    {
        if (isset($this->urls[$url])) return $this->links[$this->urls[$url]];
        return false;
    }

    public function getLinkFromDate($linkDate){
        return $this->links[$linkDate];
    }

    // Case insentitive search among links (in url, title and description). Returns filtered list of links.
    // eg. print_r($mydb->filterFulltext('hollandais'));
    public function filterFulltext($searchterms)
    {
        // FIXME: explode(' ',$searchterms) and perform a AND search.
        // FIXME: accept double-quotes to search for a string "as is" ?
        $filtered=array();
        $s = strtolower($searchterms);
        foreach($this->links as $l)
        {
            $found=   (strpos(strtolower($l['title']),$s)!==false)
                   || (strpos(strtolower($l['description']),$s)!==false)
                   || (strpos(strtolower($l['url']),$s)!==false)
                   || (strpos(strtolower($l['tags']),$s)!==false);
            if ($found) $filtered[$l['linkdate']] = $l;
        }
        krsort($filtered);
        return convertFilteredToDays($filtered);
    }



    // Filter by tag.
    // You can specify one or more tags (tags can be separated by space or comma).
    // eg. print_r($mydb->filterTags('linux programming'));
    public function filterTags($tags,$casesensitive=false)
    {
        $t = str_replace(',',' ',($casesensitive?$tags:strtolower($tags)));
        $searchtags=explode(' ',$t);
        $filtered=array();
        foreach($this->links as $l)
        {
            $linktags = explode(' ',($casesensitive?$l['tags']:strtolower($l['tags'])));
            if (count(array_intersect($linktags,$searchtags)) == count($searchtags))
                $filtered[$l['linkdate']] = $l;
        }
        krsort($filtered);
        return convertFilteredToDays($filtered);

        // return $filtered;
    }

    // Filter by day. Day must be in the form 'YYYYMMDD' (eg. '20120125')
    // Sort order is: older articles first.
    // eg. print_r($mydb->filterDay('20120125'));
    public function filterDay($day)
    {
        $filtered=array();
        foreach($this->links as $l)
        {
            if (date("Ymd", $l['linkdate']/1000) == $day) $filtered[$l['linkdate']] = $l;
        }
        ksort($filtered);
        return $filtered;
    }
    // Filter by smallHash.
    // Only 1 article is returned.
    public function filterSmallHash($smallHash)
    {
        $filtered=array();
        foreach($this->links as $l)
        {
            if ($smallHash==smallHash($l['linkdate'])) // Yes, this is ugly and slow
            {
                $filtered[$l['linkdate']] = $l;
                return $filtered;
            }
        }
        return $filtered;
    }

    // Returns the list of all tags
    // Output: associative array key=tags, value=0
    public function allTags()
    {
        $tags=array();
        foreach($this->links as $link)
            foreach(explode(' ',$link['tags']) as $tag)
                if (!empty($tag)) $tags[$tag]=(empty($tags[$tag]) ? 1 : $tags[$tag]+1);
        arsort($tags); // Sort tags by usage (most used tag first)
        return $tags;
    }

    // Returns the list of days containing articles (oldest first)
    // Output: An array containing days (in format YYYYMMDD).
    public function days()
    {
        $linkdays=array();
        foreach(array_keys($this->links) as $day)
        {
            $linkdays[substr($day,0,8)]=0;
        }
        $linkdays=array_keys($linkdays);
        sort($linkdays);
        return $linkdays;
    }

    // Delete link

    public function deleteLink($linkdate){
        unset($this->links[$linkdate]);
        $this->savedb(); // save to disk
    }

    public function checkSecret($secret){
        // return $this->secret;
        return ($secret == $this->secret);
    }

    public function changeSecret($secret){
        // echo $secret;
        if (substr($secret, 0, strlen($this->secret)) == $this->secret) {
            $this->secret = substr($secret, strlen($this->secret));
            $this->savedb();
            return true;
        }
        else {
            return false;
        }
    }
}

// Invalidate caches when the database is changed or the user logs out.
// (eg. tags cache).
function invalidateCaches()
{
    // unset($_SESSION['tags']);  // Purge cache attached to session.
    // pageCache::purgeCache();   // Purge page cache shared by sessions.
}

 ?>