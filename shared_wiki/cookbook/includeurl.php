<?php if (!defined('PmWiki')) exit ();
/*  copyright 2007 Hans Bracker and Dominique Faure.
    This file is distributed under the terms of the GNU General Public 
    License as published by the Free Software Foundation; either 
    version 2 of the License, or (at your option) any later version.  

    This module enables inclusion of html pages in wiki pages. 
    
    Width and height can be set with parameters inside the markup.
    Syntax: 
    (:includeurl http://example.com/page.html [width=...] [height=...] [border=...] [type=...]:)
    or
    (:includeurl /samplesite/path/page.html:)
    
    Setting $EnableExternalResource = 0; will prohibit display of urls starting with http or https.
    The default permits inclusion of external urls.
    
    The parameter iframe=1 or iframe=true will result in the use of the <iframe ..> html tag 
    instead of the <object ..> tag. But still use optional parameter border=.. if needed, not frameborder.
    If iframe option is not specified, <object> will be used for all browsers except IE, which uses <iframe>
    
*/
# Version date
$RecipeInfo['IncludeUrl']['Version'] = '2014-12-01';

SDV($EnableExternalResource, 1);

# add markup (:includeurl ... :)
if (function_exists(Markup_e)) {
	Markup_e('includeurl', 'directives',
  '/\\(:includeurl\\s*(.*?)\\s*:\\)/i',
  "IncludeUrl(\$pagename, \$m[1])");
} else {
Markup('includeurl', 'directives',
  '/\\(:includeurl\\s*(.*?)\\s*:\\)/ei',
  "IncludeUrl(\$pagename, PSS('$1'))");
}

function IncludeUrl($pagename, $opt) {
  global $IncludeUrlDefaults, $EnableExternalResource,
         $HandleActions, $EnableUrlApprovalRequired;
  SDVA($IncludeUrlDefaults, array(
    'width' => '100%',
    'height' => '400px',
    'border' => '1',
    'type' => 'text/html',
    'iframe' => '',
    'standby' => FmtPageName(' $[Page is loading...] ', $pagename),
    'errormsg' => FmtPageName(' $[Display of external pages is not allowed] ', $pagename),
  ));
  $opt = ParseArgs($opt);
  if($opt[''][0])
    $tgt = $opt[''][0];
  else
    foreach($opt as $k => $v)
      if(! array_key_exists($k, $IncludeUrlDefaults)
      && ! in_array($k, array('', '#', '+', '-'))) {
        $tgt = "$k:$v";
        break;
      }
  $opt = array_merge($IncludeUrlDefaults, $opt);
  if(!IsEnabled($EnableExternalResource, 1) && preg_match('/^https?/', $tgt))
    return Keep("<h5>{$opt['errormsg']}</h5>");

  $url = MakeLink($pagename, $tgt, NULL, NULL, '$LinkUrl');

  if($HandleActions['approvesites']
  && IsEnabled($EnableUrlApprovalRequired, 1)
  && preg_match('/<a[^>]+action=approvesites[^>]+>/', $url))
    return Keep($url);

  $out = array();
  if(!$opt['iframe'])
     $out[] = "<!--[if !IE]> Firefox and others will use outer object -->
     <object data=\"$url\"
             width=\"{$opt['width']}\"
             height=\"{$opt['height']}\"
             border=\"{$opt['border']}\"
             type=\"{$opt['type']}\"
             standby=\"{$opt['standby']}\">
     <!--<![endif]-->
       <!-- MSIE (Microsoft Internet Explorer) will use inner iframe -->";
     $out[] = "  <iframe src=\"$url\"
             width=\"{$opt['width']}\"
             height=\"{$opt['height']}\"
             frameborder=\"{$opt['border']}\">
         <h3>Your browser does not support including other html pages</h3>
       </iframe>";
  if(!$opt['iframe'])
     $out[] = "<!--[if !IE]> close outer object -->
     </object>
     <!--<![endif]-->";
  return Keep(implode("\n", $out));
}
