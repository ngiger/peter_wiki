<?php
  # Simple page hit counter for pmwiki
  # 2004 bhoc@tiscali.ch
  # Modified 9-Nov-2004 by pmichaud for PmWiki 2
  # Modified 2007-05-19 by pmichaud for PmWiki 2.2
  # Modified 2007-11-24 by bhoc
  # Modified 2009-02-05 by DaveG -- corrected pageexists code.

  $RecipeInfo['SimplePageCounter']['Version'] = '2007-11-24';

  # if we don't have a page name, get one!
  $pagename = ResolvePageName($pagename);
  $PageCount = 0;

  # proceed only if the page exists
  if (PageExists($pagename)) {

    # determine the name of this page's counter file
    $counterfile = FmtPageName('$WorkDir/.counters/$FullName.count', $pagename);

    # get the current $PageCount
    if (($fp = @fopen($counterfile, 'r')))
      { $PageCount = intval(fgets($fp)); fclose($fp); }

    # if action is 'browse', update the stored $PageCount by one
    if ($action=='browse') {
      @$PageCount++;
      if (($fp = @fopen($counterfile, 'w')))
        { fputs($fp,$PageCount); fclose($fp); }
    }

  }

  $FmtPV['$PageCount'] = $PageCount;
