<?php if (!defined('PmWiki')) exit();
$FarmPubD = '/home/web/shared_wiki/';
$FarmD    = '/home/web/shared_wiki/';
if ( $_SERVER['HTTP_X_FORWARDED_HOST'] ) {
  $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
  $PubDirUrl = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].'/wk/pub';
  $ScriptUrl = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].'/wk/pmwiki.php';

} else {
  $FarmPubDirUrl = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/pub';
}

#2013-09-30 v sbu kopiert von peter.schoenbucher.ch
#
#include_once('cookbook/e-protect.php');                     //'verschluesselt' mail-adressen beim speichern einer seite automatisch: 
include_once("$FarmD/cookbook/counter.php");                       //seiten-editier-zähler
#include_once('cookbook/XToDo.php');                         //x todo 
#include_once('cookbook/googlecalendar.php');                 // google-calender einbinden
#include_once('pub/fckeditor/FCKEditor/fckeditor.php');       // FCkEditor
#include_once('cookbook/edit_fckeditor.php');                 // HTML -> Markup translator & wiki adapter 
#include_once('cookbook/includeurl.php');                     // erlaubt fremde seiten einzuschliessen
# include_once("$FarmD/cookbook/edittoolbar/edittoolbar.php");

##  This is a sample config.php file.  To use this file, copy it to
##  local/config.php, then edit it for whatever customizations you want.
##  Also, be sure to take a look at http://www.pmichaud.com/wiki/Cookbook
##  for more details on the types of customizations that can be added
##  to PmWiki. 

##  $WikiTitle is the name that appears in the browser's title bar.
$WikiTitle = 'PraxisUnion intern (Docker)';

##  If you want to use URLs of the form .../pmwiki.php/Group/PageName
##  instead of .../pmwiki.php?p=Group.PageName, try setting
##  $EnablePathInfo below.  Note that this doesn't work in all environments,
##  it depends on your webserver and PHP configuration.  You might also 
##  want to check http://www.pmwiki.org/wiki/Cookbook/CleanUrls more
##  details about this setting and other ways to create nicer-looking urls.
$EnablePathInfo = 1;

## $PageLogoUrl is the URL for a logo image -- you can change this
## to your own logo if you wish.
$PageLogoUrl = "$PubDirUrl/skins/logo/1b.jpg";

## If you want to have a custom skin, then set $Skin to the name
## of the directory (in pub/skins/) that contains your skin files.
## See PmWiki.Skins and Cookbook.Skins.
$Skin = 'sbu';

## You'll probably want to set an administrative password that you
## can use to get into password-protected pages.  Also, by default 
## the "attr" passwords for the PmWiki and Main groups are locked, so
## an admin password is a good way to unlock those.  See PmWiki.Passwords
## and PmWiki.PasswordsAdmin.
## ...PasswordsAdmin?action=crypt
# $DefaultPasswords['admin'] = crypt(' ');
# ganz alt: $DefaultPasswords['admin']='$1$9gADOd5m$Hv45FzNFFNjIewWbxmMpN0';
# alt: $DefaultPasswords['admin']='$1$r8V5oZUo$iH3Pb8ZENih/T79I7s8.6.';
$DefaultPasswords['admin']='$1$QNrmTIef$OVE4mCLukepsIcPZ/0X0z/';

##  PmWiki comes with graphical user interface buttons for editing;
##  to enable these buttons, set $EnableGUIButtons to 1.  
$EnableGUIButtons = 1;

##  If you want uploads enabled on your system, set $EnableUpload=1.
##  You'll also need to set a default upload password, or else set
##  passwords on individual groups and pages.  For more information
##  see PmWiki.UploadsAdmin.
$EnableUpload = 1;                       
# $DefaultPasswords['upload'] = crypt(' ');
#  $DefaultPasswords['upload']='$1$SeLL09KW$rrJroPod4FzxSUJ6u43uC.';


##  Setting $EnableDiag turns on the ?action=diag and ?action=phpinfo
##  actions, which often helps the PmWiki authors to troubleshoot 
##  various configuration and execution problems.
$EnableDiag = 1;                         # enable remote diagnostics

##  By default, PmWiki doesn't allow browsers to cache pages.  Setting
##  $EnableIMSCaching=1; will re-enable browser caches in a somewhat
##  smart manner.  Note that you may want to have caching disabled while
##  adjusting configuration files or layout templates.
# $EnableIMSCaching = 1;                   # allow browser caching

##  Set $SpaceWikiWords if you want WikiWords to automatically 
##  have spaces before each sequence of capital letters.
$SpaceWikiWords = 0;                     # turn on WikiWord spacing

##  Set $LinkWikiWords if you want to allow WikiWord links.
#$LinkWikiWords = 1;                      # enable WikiWord links

##  If you want only the first occurrence of a WikiWord to be converted
##  to a link, set $WikiWordCountMax=1.
# $WikiWordCountMax = 1;                   # converts only first WikiWord
# $WikiWordCountMax = 0;                   # another way to disable WikiWords

##  The $WikiWordCount array can be used to control the number of times
##  a WikiWord is converted to a link.  This is useful for disabling
##  or limiting specific WikiWords.
# $WikiWordCount['PhD'] = 0;               # disables 'PhD'
# $WikiWordCount['PmWiki'] = 1;            # convert only first 'PmWiki'

##  By default, PmWiki is configured such that only the first occurrence
##  of 'PmWiki' in a page is treated as a WikiWord.  If you want to 
##  restore 'PmWiki' to be treated like other WikiWords, uncomment the
##  line below.
# unset($WikiWordCount['PmWiki']);

##  If you want to disable WikiWords matching a pattern, you can use 
##  something like the following.  Note that the first argument has to 
##  be different for each call to Markup().  The example below disables
##  WikiWord links like COM1, COM2, COM1234, etc.
Markup('COM\d+', '<wikilink', '/\\bCOM\\d+/', "Keep('$0')");

##  $DiffKeepDays specifies the minimum number of days to keep a page's
##  revision history.  The default is 3650 (approximately 10 years).
# $DiffKeepDays=30;                        # keep page history at least 30 days

## By default, viewers are able to see the names (but not the
## contents) of read-protected pages in search results and
## page listings.  Set $EnablePageListProtect to keep read-protected
## pages from appearing in search results.
$EnablePageListProtect = 1;

##  The refcount.php script enables ?action=refcount, which helps to
##  find missing and orphaned pages.  See PmWiki.RefCount.
if ($action == 'refcount') include_once('scripts/refcount.php');

##  The feeds.php script enables ?action=rss, ?action=atom, ?action=rdf,
##  and ?action=dc, for generation of syndication feeds in various formats.
# if ($action == 'rss') include_once('scripts/feeds.php');   # RSS 2.0
# if ($action == 'atom') include_once('scripts/feeds.php');  # Atom 1.0
# if ($action == 'dc') include_once('scripts/feeds.php');    # Dublin Core
# if ($action == 'rdf') include_once('scripts/feeds.php');   # RSS 1.0

##  PmWiki allows a great deal of flexibility for creating custom markup.
##  To add support for '*bold*' and '~italic~' markup (the single quotes
##  are part of the markup), uncomment the following lines. 
##  (See PmWiki.CustomMarkup and the Cookbook for details and examples.)
Markup("'~", "inline", "/'~(.*?)~'/", "<i>$1</i>");        # '~italic~'
Markup("'*", "inline", "/'\\*(.*?)\\*'/", "<b>$1</b>");    # '*bold*'
#Markup("+", "inline", "/'\\+(.+?)\\+'/", "<b>$1</b>");    # +bold+
Markup("+", "inline", "/\\+(.+?)\\+/", "<b>$1</b>");    # +bold+

##  If you want to have to approve links to external sites before they
##  are turned into links, uncomment the line below.  See PmWiki.UrlApprovals.
##  Also, setting $UnapprovedLinkCountMax limits the number of unapproved
##  links that are allowed in a page (useful to control wikispam).
# include_once('scripts/urlapprove.php');
# $UnapprovedLinkCountMax = 10;

##  The following lines make additional editing buttons appear in the
##  edit page for subheadings, lists, tables, etc.

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

## SPRACHE der Bedienungselemente 
# Deutsche Sprache
XLPage('de','PmWikiDe.XLPage');

# Markup('googlesearch', 'directives', '/\\(:googlesearch:\\)/e',"Keep(\"
#<FORM method=GET action='http://www.google.ch/search'>
#<TABLE><tr><td>
#<A HREF='http://www.google.ch'>
#<IMG SRC='http://www.google.ch/logos/Logo_40wht.gif' border='0'
#ALT='Google' align='absmiddle'></A>
#<INPUT TYPE=text name=q size=20 maxlength=255 value=''>
#<INPUT TYPE=hidden name=hl value=de>
#<INPUT type=submit name=btnG VALUE='Google Search'>
#</td></tr></TABLE>
#</FORM>
#\")");

## ToDo - add on (http://www.pmwiki.org/wiki/Cookbook/ToDo) 
# variablen
#$todo_category_names = array('Kriens', 'Praxis', 'andere');
#$todo_owner_names = array('sbu', 'mpa', 'andere');
#$HTMLStylesFmt['todo'] = <<< EOT
#.todo-form { border: none; }
#.todo-form tr td { border: none; font-weight: plain; 
#    text-align: left; padding: 4px; }
#.todo-form tr td.heading { text-align: right; 
#    width: 140px; padding-right: 6px; }
#table.todo-list { border: 2px solid #ccc; }
#table.todo-list tr.row1 { background-color: #eee; color: #555; }
#table.todo-list tr.row1 td { border-bottom: 2px solid #ccc; }
#table.todo-list tr:last-child.row1 td { border-bottom: none; }
#table.todo-list th { background-color: #ddd; padding: 3px; 
#    font-weight: normal; border: 1px solid #ccc; color: #444; }
#table.todo-list tr td { color: #666; }
#table.todo-simple-list tr td { border: none; padding: 4px; }
#.todo-category-text { color: #666; border-bottom: 1px solid #ccc; }
#.todo-description-text { color: #444; }
#.todo-overdue-text { color: #f66; border-bottom: 1px solid #ccc; }
#.todo-completed-text { color: #446600; border-bottom: 1px solid #ccc; }
#.todo-legend { text-align: center; color: #555; font-size: smaller; }
#EOT;
