# This file is cookbook/.htaccess -- the default distribution contains this
# file to prevent cookbook/ scripts from being accessed directly by browsers
# (this is a potential, albeit very unlikely, security hole).
#
# If you alter or replace this file, it will likely be overwritten when
# you upgrade from one version of PmWiki to another.  Be sure to save
# a copy of your alterations in another location so you can restore them,
# and you might try changing this file to be read-only to prevent a PmWiki
# upgrade from overwriting your altered version.

Order Deny,Allow
Deny from all
<?php

Markup('GoogleCalendar', 'directives',  '/\\(:GoogleCalendar (.*?):\\)/e', 'DspGCal("$1")');

function DspGCal($opts) {
  $args = ParseArgs($opts);

  if (!$args['calendar']) { $output = "Undefined Google Calendar"; }
    else { // valid calendar
  
  $args['calendar'] = str_replace("@","%40",$args['calendar']);  
  if (!$args['title']) { $args['title'] = 'My%20Calendar'; }
    else { $args['title'] = str_replace(" ","%20",strip_quotes($args['title'])); }
  if (!$args['width']) { $args['width'] = '640'; }
  if (!$args['height']) { $args['height'] = '610'; }
  if (!$args['items']) { $args['items'] = '5'; }
  if ($args['week'] != 'Sun'
     && $args['week'] != 'Mon'
     && $args['week'] != 'Sat') { $args['week'] = 'Sun'; }
  if ($args['control'] != 'full'
    && $args['control'] != 'navonly'
    && $args['control'] != 'none') { $args['control'] = 'full'; }
  if ($args['mode'] != 'month'
    && $args['mode'] != 'agenda') { $args['mode'] = 'month'; }
  if ($args['border'] != 'on'
    && $args['border'] != 'off') { $args['border'] = 'off'; }


  $output  = '<iframe src="http://www.google.com/calendar/embed?';
  $output .= 'src=' . $args['calendar'];
  $output .= '&title=' . $args['title'];
  if ($args['control'] == 'navonly') { $output .= '&chrome=NAVIGATION'; }
    elseif ($args['control'] == 'none') { $output .= '&chrome=NONE'; }
  if ($args['mode'] == 'agenda') { $output .= '&mode=AGENDA'; }
  $output .= '&epr=' . $args['items'];
  $output .= '&wkst=';
  if ($args['week'] == 'Sun') { $output .= '1'; }
    elseif ($args['week'] == 'Mon') { $output .= '2'; }
    else  { $output .= '7'; }
  if ($args['bgcolor']) { $output .= '&bgcolor=%23' . $args['bgcolor']; }
  $output .= '&height=' . $args['height'] . '"';
  if ($args['border'] == 'off') { $output .= ' style="border-width:0"'; }
    else { $output .= ' style="border:solid 1px #777"'; }
  $output .= ' width=' . $args['width'];
  $output .= ' frameborder="0"';
  $output .= ' height=' . $args['height'];
  $output .= '></iframe>';

 } // end valid calendar

return Keep($output);
 }

function strip_quotes($string) {
  $string = stripslashes($string);
  $string = str_replace("'", "", $string);
  $string = str_replace('"', '', $string);
  return $string;
  }

?>