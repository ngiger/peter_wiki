<?php if (!defined('PmWiki')) exit();

/*
// 
// 2005-12-01  ksc  Initial release
//  
*/

Markup('{$local-variables}', '>{$fmt}',
  '/{\\$(Var1|Var2|title)}/e',
  "\$GLOBALS['$1']");
$GLOBALS['Var1'] = "Variable 1";
$GLOBALS['Var2'] = "Variable 2";
$GLOBALS['title'] = strtolower(FmtPageName('$WikiTitle', $pagename));

Markup('{$local-variables}', '>{$fmt}',
  '/{\\$(Var1|Var2|currentpage)}/e',
  "\$GLOBALS['$1']");
$GLOBALS['Var1'] = "Variable 1";
$GLOBALS['Var2'] = "Variable 2";
$GLOBALS['currentpage'] = strtolower(FmtPageName('$Namespaced', $pagename));

?>
