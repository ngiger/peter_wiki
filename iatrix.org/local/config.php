<?php if (!defined('PmWiki')) exit();
$FarmPubD = '/home/web/shared_wiki/';
$FarmD    = '/home/web/shared_wiki/';
if ( $_SERVER['HTTP_X_FORWARDED_HOST'] ) {
  $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
  $PubDirUrl = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].'/pub';
  $ScriptUrl = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].'/pmwiki.php';

} else {
  $FarmPubDirUrl = $_SERVER['HTTP_HOST'].'/pub';
}

# print("Config.php für iatrix.org auf HOST ".$_SERVER['HTTP_HOST'].".<br>");
# print("FarmPubD: $FarmPubD.<br>PubDirUrl ist $PubDirUrl<br>ScriptUrl ist $ScriptUrl<br>");

##  $WikiTitle is the name that appears in the browser's title bar.
$WikiTitle = 'Iatrix & Elexis (Docker)';

##  $ScriptUrl is your preferred URL for accessing wiki pages
##  $PubDirUrl is the URL for the pub directory.
# $ScriptUrl = "$HOST_NAME/pmwiki.php";
# $PubDirUrl = "$HOST_NAME/pub";
# $FarmPubDirUrl = "$HOST_NAME/pub";

##  If you want to use URLs of the form .../pmwiki.php/Group/PageName
##  instead of .../pmwiki.php?p=Group.PageName, try setting
##  $EnablePathInfo below.  Note that this doesn't work in all environments,
##  it depends on your webserver and PHP configuration.  You might also 
##  want to check http://www.pmwiki.org/wiki/Cookbook/CleanUrls more
##  details about this setting and other ways to create nicer-looking urls.
# $EnablePathInfo = 1;
$EnablePathInfo = 1;

## $PageLogoUrl is the URL for a logo image -- you can change this
## to your own logo if you wish.
# $PageLogoUrl = "$PubDirUrl/skins/pmwiki/pmwiki-32.gif";

$HTMLHeaderFmt['logo'] = '<link rel="shortcut icon" href="$HOST_NAME/favicon.ico" />';

## If you want to have a custom skin, then set $Skin to the name
## of the directory (in pub/skins/) that contains your skin files.
## See PmWiki.Skins and Cookbook.Skins.
$Skin = 'simple';
$EnableMenuBar = TRUE;

## FUSSZEILE
$SkinCopyright =  'kontakt: <a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;&#104;&#101;&#108;&#112;&#64;&#105;&#97;&#116;&#114;&#105;&#120;&#46;&#111;&#114;&#103;">help -at- iatrix.org</a>, 
<a href="http://www.schoenbucher.ch/Main/Elexis" title="Iatrix" >sch&ouml;bu, luzern</a> ';

## You'll probably want to set an administrative password that you
## can use to get into password-protected pages.  Also, by default 
## the "attr" passwords for the PmWiki and Main groups are locked, so
## an admin password is a good way to unlock those.  See PmWiki.Passwords
## and PmWiki.PasswordsAdmin.
# bis 2015-01-29 $DefaultPasswords['admin'] = crypt('$1$SeLL09KW$rrJroPod4FzxSUJ6u43uC.');
$DefaultPasswords['admin']='$6$0n8wqZvVyDBp$JBeMlicJDDPLTMXsNOUFBVWgnLZsYzE7qv0gPEU7pRI.c0BVImb5dKOfTWCBVTziXB6i.KfyGR6wd9LWmk75q0';

## Unicode (UTF-8) allows the display of all languages and all alphabets.
# include_once("scripts/xlpage-utf-8.php");

$EnableGUIButtons = 1;
include_once("$FarmD/cookbook/edittoolbar/edittoolbar.php");

## If you're running a publicly available site and allow anyone to
## edit without requiring a password, you probably want to put some
## blocklists in place to avoid wikispam.  See PmWiki.Blocklist.
$EnableBlocklist = 1;                    # enable manual blocklists  - aktiv 2015-01-25 sbu
# $EnableBlocklist = 10;                   # enable automatic blocklists

##  To enable markup syntax from the Creole common wiki markup language
##  (http://www.wikicreole.org/), include it here:
# include_once("scripts/creole.php");

##  Some sites may want leading spaces on markup lines to indicate
##  "preformatted text blocks", set $EnableWSPre=1 if you want to do
##  this.  Setting it to a higher number increases the number of
##  space characters required on a line to count as "preformatted text".
$EnableWSPre = 1;   # lines beginning with space are preformatted (default) - aktiv 2015-01-25 sbu
# $EnableWSPre = 4;   # lines with 4 or more spaces are preformatted
# $EnableWSPre = 0;   # disabled

##  If you want uploads enabled on your system, set $EnableUpload=1.
##  You'll also need to set a default upload password, or else set
##  passwords on individual groups and pages.  For more information
##  see PmWiki.UploadsAdmin.
$EnableUpload = 1;
$UploadExts['tif'] = 'application/tif';      # erweiterung durch sbu 
$UploadMaxSize = 100000;                     # heraufgesetzt durch sbu 
#$DefaultPasswords['upload'] = crypt;
# bis nach einbruch $DefaultPasswords['upload'] = crypt('$1$vxe4gSOz$eUmdxMS28IRp1.LYxWQRS1');
$DefaultPasswords['upload']='$6$0n8wqZvVyDBp$JBeMlicJDDPLTMXsNOUFBVWgnLZsYzE7qv0gPEU7pRI.c0BVImb5dKOfTWCBVTziXB6i.KfyGR6wd9LWmk75q0';

##  Setting $EnableDiag turns on the ?action=diag and ?action=phpinfo
##  actions, which often helps others to remotely troubleshoot 
##  various configuration and execution problems.
# $EnableDiag = 1;                         # enable remote diagnostics

##  By default, PmWiki doesn't allow browsers to cache pages.  Setting
##  $EnableIMSCaching=1; will re-enable browser caches in a somewhat
##  smart manner.  Note that you may want to have caching disabled while
##  adjusting configuration files or layout templates.
# $EnableIMSCaching = 1;                   # allow browser caching

##  Set $SpaceWikiWords if you want WikiWords to automatically 
##  have spaces before each sequence of capital letters.
$SpaceWikiWords = 0;                     # turn on WikiWord spacing

##  Set $EnableWikiWords if you want to allow WikiWord links.
##  For more options with WikiWords, see scripts/wikiwords.php .
$EnableWikiWords = 0;                      # enable WikiWord links

##  $DiffKeepDays specifies the minimum number of days to keep a page's
##  revision history.  The default is 3650 (approximately 10 years).
$DiffKeepDays=1444;                        # keep page history at least 30 days

## By default, viewers are prevented from seeing the existence
## of read-protected pages in search results and page listings,
## but this can be slow as PmWiki has to check the permissions
## of each page.  Setting $EnablePageListProtect to zero will
## speed things up considerably, but it will also mean that
## viewers may learn of the existence of read-protected pages.
## (It does not enable them to access the contents of the
## pages.)
$EnablePageListProtect = 0;

##  The refcount.php script enables ?action=refcount, which helps to
##  find missing and orphaned pages.  See PmWiki.RefCount.
if ($action == 'refcount') include_once("scripts/refcount.php");

##  The feeds.php script enables ?action=rss, ?action=atom, ?action=rdf,
##  and ?action=dc, for generation of syndication feeds in various formats.
# if ($action == 'rss')  include_once("scripts/feeds.php");  # RSS 2.0
# if ($action == 'atom') include_once("scripts/feeds.php");  # Atom 1.0
# if ($action == 'dc')   include_once("scripts/feeds.php");  # Dublin Core
# if ($action == 'rdf')  include_once("scripts/feeds.php");  # RSS 1.0

##  In the 2.2.0-beta series, {$var} page variables were absolute, but now
##  relative page variables provide greater flexibility and are recommended.
##  (If you're starting a new site, it's best to leave this setting alone.)
$EnableRelativePageVars = 1; # 1=relative; 0=absolute  - aktiv 2015-01-25 sbu

##  By default, pages in the Category group are manually created.
##  Uncomment the following line to have blank category pages
##  automatically created whenever a link to a non-existent
##  category page is saved.  (The page is created only if
##  the author has edit permissions to the Category group.)
# $AutoCreate['/^Category\\./'] = array('ctime' => $Now);

##  PmWiki allows a great deal of flexibility for creating custom markup.
##  To add support for '*bold*' and '~italic~' markup (the single quotes
##  are part of the markup), uncomment the following lines. 
##  (See PmWiki.CustomMarkup and the Cookbook for details and examples.)
# Markup("'~", "inline", "/'~(.*?)~'/", "<i>$1</i>");        # '~italic~'
# Markup("'*", "inline", "/'\\*(.*?)\\*'/", "<b>$1</b>");    # '*bold*'

##  If you want to have to approve links to external sites before they
##  are turned into links, uncomment the line below.  See PmWiki.UrlApprovals.
##  Also, setting $UnapprovedLinkCountMax limits the number of unapproved
##  links that are allowed in a page (useful to control wikispam).
# $UnapprovedLinkCountMax = 10;
# include_once("scripts/urlapprove.php");

$GUIButtons['h2'] = array(400, '\\n!! ', '\\n', '$[Heading]',
                    '$GUIButtonDirUrlFmt/h2.gif"$[Heading]"');
$GUIButtons['h3'] = array(402, '\\n!!! ', '\\n', '$[Subheading]',
                     '$GUIButtonDirUrlFmt/h3.gif"$[Subheading]"');
$GUIButtons['indent'] = array(500, '\\n->', '\\n', '$[Indented text]',
                     '$GUIButtonDirUrlFmt/indent.gif"$[Indented text]"');
$GUIButtons['outdent'] = array(510, '\\n-<', '\\n', '$[Hanging indent]',
                    '$GUIButtonDirUrlFmt/outdent.gif"$[Hanging indent]"');
$GUIButtons['ol'] = array(520, '\\n# ', '\\n', '$[Ordered list]',
                     '$GUIButtonDirUrlFmt/ol.gif"$[Ordered (numbered) list]"');
$GUIButtons['ul'] = array(530, '\\n* ', '\\n', '$[Unordered list]',
                     '$GUIButtonDirUrlFmt/ul.gif"$[Unordered (bullet) list]"');
$GUIButtons['hr'] = array(540, '\\n----\\n', '', '',
                     '$GUIButtonDirUrlFmt/hr.gif"$[Horizontal rule]"');
$GUIButtons['table'] = array(600,
                       '||border=1 width=80%\\n||!Hdr ||!Hdr ||!Hdr ||\\n||     ||     ||     ||\\n||     ||     ||     ||\\n', '', '', 
                     '$GUIButtonDirUrlFmt/table.gif"$[Table]"');

