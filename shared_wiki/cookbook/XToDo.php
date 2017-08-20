<?php if (!defined('PmWiki')) exit();

//
// Copyright (C) 2005 Julian I. Kamil <julian.kamil@gmail.com>
// No warranty is provided.  Use at your own risk.
//
// Commercial support is available through ESV Media Group
// which can be reached at: http://www.ESV-i.com/.
//
// Name: XToDo.php
// Author: Julian I. Kamil <julian.kamil@gmail.com>
// Created: 2005-09-29
// Description:
//     This is a todo list management implementation for PmWiki.
//     It is meant to be a replacement for an earlier version,
//     and is not backward compatible with that version.
//     Please see:
//         http://www.madhckr.com/project/PmWiki/XToDo
//     for a live example and doumentation.
//
// This file is part of XToDo.
// This file is not part of PmWiki.
// 
// XToDo is free software; you can redistribute it and / or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
// 
// XToDo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with XToDo; if not, write to the Free Software
// Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
// 
// $Id: XToDo.php,v 1.20 2005/11/11 17:18:34 julian Exp $
//
// History:
//     2005-09-29  jik  Created.
//     2005-10-02  jik  Added simple list view.
//                      Fixed the border-collapse of list view.
//     2005-10-03  jik  Fixed the incompatibility with 
//                      LinkWikiWords setting.
//                      Fixed the issue with style customization.
//     2005-10-04  jik  Checked for empty lists before sorts.
//     2005-10-05  jik  Added summary max length in simple list.
//                      Added sort by markup argument.
//     2005-10-10  jik  Fixed sort by date problem.
//     2005-10-11  jik  Added date-based selection criteria.
//     2005-10-12  jik  Added status field display in simple list.
//                      Simplified color highlight code.
//                      Fixed list page regular expression.
//                      Added notes attachments.
//                      Added show option processing.
//     2005-11-02  jik  Fixed the issue with empty list (as reported
//                      by nbk).
//                      Fixed the issue with create date field
//                      title (as reported by juytter).
//                      Fixed the issue with empty group name
//                      when a list is loaded on the default page
//                      invoked with a clean URL (as reported 
//                      by David).
//     2005-11-03  jik  Added '[-|+]today' date selection criteria.
//                      Replaced the status code with status icons.
//     2005-11-04  jik  Added an owner field.
//                      Added the to do item ID on the edit form page.
//     2005-11-06  jik  Added the completed date to the title of
//                      completed to do item icon flyover, and the
//                      number of days to due date for those that
//                      are not completed yet.
//     2005-11-08  jik  Fixed the issue with items marked overdue
//                      when they are due today.
//                      Implemented multiple category specification
//                      in the form markup.
//                      Added owner selection and sort criteria.
//     2005-11-11  jik  Added license notice in the image directory.
//

define(TODO_VERSION, '0.5.1');

SDV($todo_priority_names,        array('5' => 'High', '4' => 'Medium high', '3' => 'Medium', '2' => 'Medium low', '1' => 'Low'));
SDV($todo_urgency_names,         array('5' => 'High', '4' => 'Medium high', '3' => 'Medium', '2' => 'Medium low', '1' => 'Low'));
SDV($todo_status_names,          array('Open', 'In progress', 'On hold', 'Completed', 'Overdue'));
SDV($todo_status_codes,          array('Open' => 'O', 'In progress' => '-', 'On hold' => '#', 'Completed' => '~', 'Overdue' => '!'));
SDV($todo_completed_status_name, 'Completed');
SDV($todo_overdue_status_name,   'Overdue');
SDV($todo_category_names,        array('Personal', 'Business', 'Other'));
SDV($todo_field_names,           array('ID', 'Category', 'Status', 'Priority', 'Urgency', 'Create date', 'Due date', 'Description', 'Owner'));
SDV($todo_delay_names,           array('tomorrow', 'next week'));

SDV($todo_date_format, 'Y-m-d');
SDV($todo_summary_length, 76);

SDV($HandleActions['todo'],  'XToDoFormHandler');
SDV($ActionTitleFmt['todo'], '| $[To Do Form Handler]');

SDV($TableRowIndexMax, 2);
SDV($TableRowAttrFmt, "class='row\$TableRowIndex'");

// Markup.

Markup('todoform',       'inline',    '/\\(:todoform\\s*(.*?):\\)/e',       "Keep(XToDoFormMarkup('$pagename', '$1'),'')");
Markup('todolist',       'directive', '/\\(:todolist\\s*(.*?):\\)/e',       "XToDoListMarkup('$pagename', '$1')");
Markup('todosimplelist', 'directive', '/\\(:todosimplelist\\s*(.*?):\\)/e', "XToDoSimpleListMarkup('$pagename', '$1')");

// Icons.

global $PubDirUrl;

$todo_status_icons = array(
    'Open'        => "{$PubDirUrl}/XToDo/XToDo-Open.png", 
    'In progress' => "{$PubDirUrl}/XToDo/XToDo-InProgress.png", 
    'On hold'     => "{$PubDirUrl}/XToDo/XToDo-OnHold.png", 
    'Completed'   => "{$PubDirUrl}/XToDo/XToDo-Completed.png", 
    'Overdue'     => "{$PubDirUrl}/XToDo/XToDo-Overdue.png"
    );

function XToDoGetShowOptions($options) {
    $option_items = explode(',', $options);
    foreach($option_items as $item) $todo_show_options[$item] = TRUE;
    return $todo_show_options;
}

function XToDoFormMarkup($pagename, $args) {
    $args = ParseArgs($args);
    global $DefaultPage;
    if (empty($pagename)) $pagename = $DefaultPage;
    return XToDoFormDisplay($pagename, $category = $args['category']);
}

function XToDoListMarkup($pagename, $args) {
    $args = ParseArgs($args);

    if (!empty($args['status']))    $criteria['status']    = $args['status'];
    if (!empty($args['category']))  $criteria['category']  = $args['category'];
    if (!empty($args['sort']))      $criteria['sort']      = $args['sort'];
    if (!empty($args['completed'])) $criteria['completed'] = $args['completed'];
    if (!empty($args['due']))       $criteria['due']       = $args['due'];
    if (!empty($args['created']))   $criteria['created']   = $args['created'];
    if (!empty($args['show']))      $options               = XToDoGetShowOptions($args['show']);

    global $DefaultPage;
    if (empty($pagename)) $pagename = $DefaultPage;

    return XToDoListDisplay($pagename, $colorize = ($args[''][0] === 'colorize'), $criteria, $options);
}

function XToDoSimpleListMarkup($pagename, $args) {
    $args = ParseArgs($args);

    if (!empty($args['status']))    $criteria['status']    = $args['status'];
    if (!empty($args['category']))  $criteria['category']  = $args['category'];
    if (!empty($args['sort']))      $criteria['sort']      = $args['sort'];
    if (!empty($args['completed'])) $criteria['completed'] = $args['completed'];
    if (!empty($args['due']))       $criteria['due']       = $args['due'];
    if (!empty($args['created']))   $criteria['created']   = $args['created'];
    if (!empty($args['show']))      $options               = XToDoGetShowOptions($args['show']);

    global $DefaultPage;
    if (empty($pagename)) $pagename = $DefaultPage;

    return XToDoSimpleListDisplay($pagename, $colorize = ($args[''][0] === 'colorize'), $criteria, $options);
}

// Entry form.

function XToDoFormPriority($selected) {
    global $todo_priority_names;

    $todo_priority_low  = $todo_priority_names[min(array_keys($todo_priority_names))];
    $todo_priority_high = $todo_priority_names[max(array_keys($todo_priority_names))];

    for ($index = 1; $index <= count($todo_priority_names); $index++) {
        $selection_code = ($index == $selected) ? 'checked' : '';
        $output[] = "<input type='radio' name='todo-priority' value='$index' {$selection_code} />\n";
    }

    return "{$todo_priority_low} &laquo; ".implode('', $output)."&raquo; {$todo_priority_high}";
}

function XToDoFormUrgency($selected) {
    global $todo_urgency_names;

    $todo_urgency_low   = $todo_urgency_names[min(array_keys($todo_urgency_names))];
    $todo_urgency_high  = $todo_urgency_names[max(array_keys($todo_urgency_names))];

    for ($index = 1; $index <= count($todo_urgency_names); $index++) {
        $selection_code = ($index == $selected) ? 'checked' : '';
        $output[] = "<input type='radio' name='todo-urgency' value='$index' {$selection_code} />\n";
    }

    return "{$todo_urgency_low} &laquo; ".implode('', $output)."&raquo; {$todo_urgency_high}";
}

function XToDoFormStatus($selected) {
    global $todo_status_names;

    $output[] = '<select name="todo-status">';

    for ($index = 0; $index < count($todo_status_names); $index++) {
        $selection_code = ($todo_status_names[$index] === $selected) ? 'selected' : '';
        $output[] = "<option {$selection_code}>$todo_status_names[$index]</option>\n";
    }

    $output[] = '</select>';
    return implode('', $output);
}

function XToDoFormCategory($selected, $category) {
    global $todo_category_names;

    $category_names = empty($category) ? $todo_category_names : explode(',', $category);
    $output[] = '<select name="todo-category">';
        
    for ($index = 0; $index < count($category_names); $index++) {
        $selection_code = ($category_names[$index] ===  $selected) ? 'selected' : '';
        $output[] = "<option {$selection_code}>$category_names[$index]</option>\n";
    }
    
    $output[] = '</select>';
    return implode('', $output);
}

function XToDoFormDueDate($selected) {
    global $todo_date_format;

    if (empty($selected)) {
        $default_checked = 'checked'; 
        $selection_checked = '';
        $selection_value = '';
    }
    else {
        $default_checked = ''; 
        $selection_checked = 'checked';
        $selection_value = date($todo_date_format, $selected);
    }

    $tomorrow_date  = date($todo_date_format, mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')));
    $next_week_date = date($todo_date_format, mktime(0, 0, 0, date('m'), date('d') + 7, date('Y')));

    $output[] = "<div><input type='radio' name='todo-due-date' value='{$tomorrow_date}' {$default_checked}> {$tomorrow_date} (tomorrow) </div>";
    $output[] = "<div><input type='radio' name='todo-due-date' value='{$next_week_date}'> {$next_week_date} (next week) </div>";
    $output[] = "<div><input type='radio' name='todo-due-date' value='0' {$selection_checked}><input type='text' name='todo-due-specific-date' size='11' value='$selection_value'></div>";

    return implode('', $output);
}

function XToDoFormDescription($text) {
    $output[] = "<input type='text' name='todo-description' size='72' value=\"".stripslashes($text)."\" />";

    return implode('', $output);
}

function XToDoFormOwner($selected, $owners) {
    global $todo_owner_names;

    $owner_names = empty($owners) ? $todo_owner_names : array($owners);
    $output[] = '<select name="todo-owner">';
        
    if (empty($selected)) $output[] = "<option selected></option>\n";

    for ($index = 0; $index < count($owner_names); $index++) {
        $selection_code = ($owner_names[$index] ===  $selected) ? 'selected' : '';
        $output[] = "<option {$selection_code}>$owner_names[$index]</option>\n";
    }
    
    $output[] = '</select>';
    return implode('', $output);
}

function XToDoForm($item_id, $create_date, $action, $next_page, $group_name, $category, $owners, $selection, $submit_text) {
    global $todo_field_names;

    $output[] = 
"<form method='post' action='$action'>
<input type='hidden' name='todo-next-page'  value='{$next_page}' />
<input type='hidden' name='todo-group-name' value='{$group_name}'>
<input type='hidden' name='todo-item-id'    value='{$item_id}' />
<table cellspacing='0' cellpadding='0' class='todo-form'>
    <tr>
        <td class='heading'>$todo_field_names[1]:</td>
        <td>".XToDoFormCategory($selection['category'], $category)." &nbsp; $todo_field_names[2]: ".XToDoFormStatus($selection['status'])." &nbsp; $todo_field_names[8]: ".XToDoFormOwner($selection['owner'], $owners)."</td>
    </tr>
    <tr><td class='heading'>$todo_field_names[3]:</td><td>".XToDoFormPriority($selection['priority'])."</td></tr>
    <tr><td class='heading'>$todo_field_names[4]:</td><td>".XToDoFormUrgency($selection['urgency'])."</td></tr>
    <tr><td class='heading'>$todo_field_names[5]:</td><td>{$create_date}</td></tr>
    <tr valign='top'><td class='heading'>$todo_field_names[6]:</td><td>".XToDoFormDueDate($selection['due_date'])."</td></tr>
    <tr><td class='heading'>$todo_field_names[7]:</td><td>".XToDoFormDescription($selection['description'])."</td></tr>
    <tr><td class='heading'></td><td><input type='submit' value='$submit_text' /></td></tr>
</table>
</form>";

    return implode('', $output);
}

function XToDoFormDisplay($pagename, $category) {
    global $todo_date_format;

    $action = "{$_SERVER['REQUEST_URI']}?action=todo&do=create-todo-item";
    $next_page = "{$_SERVER['REQUEST_URI']}";
    $group_name = FmtPageName('$Group', $pagename);

    return XToDoForm(0, date($todo_date_format), $action, $next_page, $group_name, $category, $owners, array(), 'Submit');
}

// Action handler.

function XToDoCreateToDoItem($pagename) {
    $todo_group = "XToDo{$_POST['todo-group-name']}";
    $todo_group_length = strlen($todo_group);

    Lock(2);

    foreach (ListPages("/^$todo_group\\.\\d{6}$/") as $i) $todo = max(@$todo, substr($i, $todo_group_length + 1));
    $todo_page_name = sprintf("$todo_group.%06d", @$todo + 1);

    if ($_POST['todo-due-date'] === '0') {
        $todo_due_specific_date = trim($_POST['todo-due-specific-date']);

        if (! empty($todo_due_specific_date)) $due_date = strtotime($todo_due_specific_date);
        if (empty($due_date) || ($due_date == 0) || ($due_date == -1) || ($due_date == FALSE)) $due_date = time();
    }
    else $due_date = strtotime($_POST['todo-due-date']);

    $page['create_date']  = time();
    $page['due_date']     = $due_date;
    $page['owner']        = $_POST['todo-owner'];
    $page['category']     = $_POST['todo-category'];
    $page['status']       = $_POST['todo-status'];
    $page['priority']     = empty($_POST['todo-priority']) ? '1' : $_POST['todo-priority'];
    $page['urgency']      = empty($_POST['todo-urgency'])  ? '1' : $_POST['todo-urgency'];
    $page['description']  = $_POST['todo-description'];

    WritePage($todo_page_name, $page);
    header("Location: {$_POST['todo-next-page']}");
}

function XToDoUpdateToDoItem($pagename) {
    $todo_page_name = "XToDo{$_POST['todo-group-name']}.{$_POST['todo-item-id']}";
    $todo_page = ReadPage($todo_page_name);

    if ($_POST['todo-due-date'] === '0') {
        $todo_due_specific_date = trim($_POST['todo-due-specific-date']);

        if (! empty($todo_due_specific_date)) $due_date = strtotime($todo_due_specific_date);
        if (empty($due_date) || ($due_date == 0) || ($due_date == -1) || ($due_date == FALSE)) $due_date = time();
    }
    else $due_date = strtotime($_POST['todo-due-date']);

    global $todo_completed_status_name;

    if (($_POST['todo-status'] === $todo_completed_status_name) && ($todo_page['todo-status'] !== $todo_completed_status_name))
        $todo_page['completed_date'] = time();
    else $todo_page['completed_date'] = 0;

    $todo_page['due_date']     = $due_date;
    $todo_page['owner']        = $_POST['todo-owner'];
    $todo_page['category']     = $_POST['todo-category'];
    $todo_page['status']       = $_POST['todo-status'];
    $todo_page['priority']     = $_POST['todo-priority'];
    $todo_page['urgency']      = $_POST['todo-urgency'];
    $todo_page['description']  = $_POST['todo-description'];

    $todo_page_name = "XToDo{$_POST['todo-group-name']}.{$_POST['todo-item-id']}";
    WritePage($todo_page_name, $todo_page);

    header("Location: {$_POST['todo-next-page']}");
}

function XToDoEditToDoItem($pagename) {
    global $FmtV, $HandleBrowseFmt, $PageStartFmt, $PageEndFmt, $PageRedirectFmt;
    global $Group, $Name;
    global $todo_date_format;

    $url_parts = explode('?', $_SERVER['REQUEST_URI']);

    $action = "{$url_parts[0]}?action=todo&do=update-todo-item";
    $next_page = "{$url_parts[0]}";

    $Group = FmtPageName('$Group', $pagename); $Name = FmtPageName('$Name', $pagename);

    $todo_page_name = "{$_GET['todo-group']}.{$_GET['todo-item']}";
    $todo_page = ReadPage("{$_GET['todo-group']}.{$_GET['todo-item']}");

    $form_text = XToDoForm($_GET['todo-item'], date($todo_date_format, $todo_page['create_date']), $action, $next_page, $Group, $category, $owners, $todo_page, 'Update');

    $notes_text = "! Notes";

    if (PageExists($todo_page_name.'-Notes')) {
	$todo_notes_page = ReadPage($todo_page_name.'-Notes');
	$notes_text .= "\n\n".$todo_notes_page['text'];
    }

    $notes_text .= "\n\n[[{$todo_page_name}-Notes?action=edit | Edit Notes]]";
    $notes_text = MarkupToHTML($todo_page_name.'-Notes', $notes_text);
    $FmtV['$PageText'] = "<h1 class='todo-edit-form-title'>To Do {$_GET['todo-item']}</h1>".$form_text.$notes_text;
    SDV($HandleBrowseFmt, array(&$PageStartFmt, &$PageRedirectFmt, '$PageText', &$PageEndFmt));
    PrintFmt($new_pagename, $HandleBrowseFmt);
}

function XToDoFormHandler($pagename) {
    $do = empty($_POST['do']) ? $_GET['do'] : $_POST['do'];

    if      ($do === 'create-todo-item') XToDoCreateToDoItem($pagename);
    else if ($do === 'edit-todo-item'  ) XToDoEditToDoItem($pagename);
    else if ($do === 'update-todo-item') XToDoUpdateToDoItem($pagename);
}

// List display.

function XToDoAbbreviateText($text_to_display, $max_length) {
    if (strlen($text_to_display) > $max_length) {
       preg_match("/.* /", substr($text_to_display,0,$max_length), $found);
       $text_to_display_abbr = substr("{$found[0]}", 0, -1)."...";
    }

    if ($text_to_display_abbr) return $text_to_display_abbr;
    else return $text_to_display;
}

// Date criteria support:
//     this month, this year
//         d=a     date
//         d=a-b   date range
//         d=a-    date to the 31st
//     this year
//         m=a     month 
//         m=a-b   month range
//         m=a-    month to december
//     1-1 through 12-31
//         y=a     year
//         y=a-b   year range
//         y=a-    year to 9999
//     relative to today
//         today   only today
//         -today  only before today
//         +today  only after today

function XToDoGetDateBoundaries($date_criteria) {
    $begin_time = 0; $end_time = mktime(24, 60, 60, 12, 31, 9999);

    if (strpos($date_criteria, '=') === FALSE) {
	preg_match('/^(\+|\-){0,1}today$/', $date_criteria, $matches);

	if (!empty($matches)) {
	    if (count($matches) == 2) {
		if ($matches[1] === '-') $end_time   = mktime(23, 59, 59, date('m'), date('d') - 1, date('Y'));
		if ($matches[1] === '+') $begin_time = mktime( 0,  0,  0, date('m'), date('d') + 1, date('Y'));
	    }
	    else {
		$begin_time = mktime( 0,  0,  0, date('m'), date('d'), date('Y'));
		$end_time   = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
	    }
	}

	return array($begin_time, $end_time);
    }

    $spec = explode('=', $date_criteria);

    if (empty($spec[1])) return array($begin_time, $end_time);

    $this_month = date('m'); $this_year = date('Y');

    if (strpos($spec[1], '-') === FALSE) {
        switch($spec[0]) {
        case 'd':
            $begin_time = mktime( 0,  0,  0, $this_month, $spec[1], $this_year);
            $end_time   = mktime(24, 60, 60, $this_month, $spec[1], $this_year);
            break;
        case 'm':
            $begin_time = mktime( 0,  0,  0, $spec[1],  1, $this_year);
            $end_time   = mktime(24, 60, 60, $spec[1], 31, $this_year);
            break;
        case 'y':
            $begin_time = mktime( 0,  0,  0,  1,  1, $spec[1]);
            $end_time   = mktime(24, 60, 60, 12, 31, $spec[1]);
            break;
        }
    }
    else {
        $bounds = explode('-', $spec[1]);
    
        if (empty($bounds[1])) {
            switch($spec[0]) {
            case 'd':
                $begin_time = mktime( 0,  0,  0, $this_month, $bounds[0], $this_year);
                $end_time   = mktime(24, 60, 60, $this_month,         31, $this_year);
                break;
            case 'm':
                $begin_time = mktime( 0,  0,  0, $bounds[0],  1, $this_year);
                $end_time   = mktime(24, 60, 60,         12, 31, $this_year);
                break;
            case 'y':
                $begin_time = mktime( 0,  0,  0,  1,  1, $bounds[0]);
                break;
            }
        }
        else {
            switch($spec[0]) {
            case 'd':
                $begin_time = mktime( 0,  0,  0, $this_month, $bounds[0], $this_year);
                $end_time   = mktime(24, 60, 60, $this_month, $bounds[1], $this_year);
                break;
            case 'm':
                $begin_time = mktime( 0,  0,  0, $bounds[0],  1, $this_year);
                $end_time   = mktime(24, 60, 60, $bounds[1], 31, $this_year);
                break;
            case 'y':
                $begin_time = mktime( 0,  0,  0,  1,  1, $bounds[0]);
                $end_time   = mktime(24, 60, 60, 12, 31, $bounds[1]);
                break;
            }
        }
    }

    return array($begin_time, $end_time);
}

function XToDoItemSelected($criteria, $item) {
    $final_met = $met = TRUE;

    if (!empty($criteria['status'])) {
        $met = FALSE;
        $status = explode(',', $criteria['status']);
        foreach ($status as $criterion) $met = ($met || (trim($criterion) === $item['status']));
        $final_met = $met;
    }
    
    if (!empty($criteria['category'])) {
        $met = FALSE;
        $category = explode(',', $criteria['category']);
        foreach ($category as $criterion) $met = ($met || (trim($criterion) === $item['category']));
        $final_met = $final_met && $met;
    }

    if (!empty($criteria['owner'])) {
        $met = FALSE;
        $owners = explode(',', $criteria['owner']);
        foreach ($owners as $owner) $met = ($met || (trim($owner) === $item['owner']));
        $final_met = $final_met && $met;
    }

    if (!empty($criteria['due'])) {
        list($begin_date, $end_date) = XToDoGetDateBoundaries($criteria['due']);

	$final_met = $final_met &&
	    ($begin_date <= $item['due_date']) && ($item['due_date'] <= $end_date);
    }

    if (!empty($criteria['completed'])) {
        list($begin_date, $end_date) = XToDoGetDateBoundaries($criteria['completed']);

	$final_met = $final_met &&
	    ($begin_date <= $item['completed_date']) && ($item['completed_date'] <= $end_date);
    }

    if (!empty($criteria['created'])) {
        list($begin_date, $end_date) = XToDoGetDateBoundaries($criteria['created']);

	$final_met = $final_met &&
	    ($begin_date <= $item['create_date']) && ($item['create_date'] <= $end_date);
    }

    return $final_met;
}

$sort_keys = array(
    'id'          => 'name', 
    'priority'    => 'priority',
    'urgency'     => 'urgency',
    'p*u'         => 'p*u',
    'status'      => 'status',
    'created'     => 'create_date',
    'due'         => 'due_date',
    'completed'   => 'completed_date',
    'category'    => 'category',
    'description' => 'description',
    'owner'       => 'owner',
    );

$sort_order_keys = array('+' => SORT_ASC, '-' => SORT_DESC);

function XToDoGetSortCriteria($criteria) {
    global $sort_keys, $sort_order_keys;

    if (!empty($criteria['sort'])) {
        $sort_field_name = substr($criteria['sort'], 1);
        $sort_order = substr($criteria['sort'], 0, 1);

        if (empty($sort_keys[$sort_field_name])) $sort_field_name = 'name';
        else $sort_field_name = $sort_keys[$sort_field_name];
        
        if (empty($sort_order_keys[$sort_order])) $sort_order = SORT_ASC;
        else $sort_order = $sort_order_keys[$sort_order];
    }
    else {
        $sort_field_name = 'name';
        $sort_order = SORT_ASC;
    }

    return array($sort_field_name, $sort_order);
}

function XToDoListGet($todo_group, $criteria) {
    global $todo_date_format;
    $todo_group_length = strlen($todo_group);
    $todo_items = array();
    $todo_list = ListPages("/^$todo_group\\.\\d{6}$/");

    if (empty($todo_list)) return array();

    list($sort_field_name, $sort_order) = XToDoGetSortCriteria($criteria);

    $index = 0;

    foreach ($todo_list as $todo_item) {
        $page = ReadPage($todo_item);
	$notes = PageExists($todo_item.'-Notes') ? "[[{$todo_item}-Notes | Notes]]" : "";

        if (XToDoItemSelected($criteria, $page)) {
            $todo_items[] = array(
                'name'           => substr($todo_item, $todo_group_length + 1), 
                'create_date'    => date($todo_date_format, $page['create_date']),
                'due_date'       => date($todo_date_format, $page['due_date']),
                'completed_date' => $page['completed_date'] == 0 ? '' : date($todo_date_format, $page['completed_date']),
                'priority'       => $page['priority'],
                'urgency'        => $page['urgency'],
                'p*u'            => ($page['priority'] * $page['urgency']),
                'status'         => $page['status'],
                'category'       => $page['category'],
		'owner'          => $page['owner'],
                'description'    => stripslashes($page['description']),
		'notes'          => $notes,
                );

            $sort_field[] = $todo_items[$index][$sort_field_name];
            $index++;
        }
    }

    if (! empty($todo_items)) {
        array_multisort($sort_field, $sort_order, $todo_items);
    }

    return $todo_items;
}

function XToDoIsItemOverdue($todo_item) {
    global $todo_completed_status_name, $todo_date_format;

    return ($todo_item['status'] !== $todo_completed_status_name) 
	&& (strtotime(date($todo_date_format)) > strtotime($todo_item['due_date']));
}

function XToDoGetDisplayHighlight($todo_item) {
    global $todo_completed_status_name;

    $completed_flag = ''; $closing_completed_flag = '';
    $overdue_flag = ''; $closing_overdue_flag = '';

    if (XToDoIsItemOverdue($todo_item)) { 
	$overdue_flag = "%class=todo-overdue-text%"; $closing_overdue_flag = "%%"; 
    }
    else if ($todo_item['status'] == $todo_completed_status_name) { 
	$completed_flag = "%class=todo-completed-text%"; $closing_completed_flag = "%%";
    }

    return array(array($overdue_flag, $closing_overdue_flag), array($completed_flag, $closing_completed_flag));
}

function XToDoSimpleListDisplay($pagename, $colorize, $criteria, $options) {
    global $todo_completed_status_name, $todo_overdue_status_name, $todo_summary_length, $todo_status_codes;
    global $todo_status_icons;

    $group_name = FmtPageName('$Group', $pagename);
    $todo_group = "XToDo{$group_name}";
    $todo_items = XToDoListGet($todo_group, $criteria);

    $output[] = "\n||class=todo-simple-list \n";

    foreach ($todo_items as $todo_item) {
	$days_to_due = (int) ceil((strtotime($todo_item['due_date']) - time()) / 86400);
        if ($colorize) list($overdue, $completed) = XToDoGetDisplayHighlight($todo_item);
        $checked = ($todo_item['status'] === $todo_completed_status_name) ? "\"Completed on: {$todo_item['completed_date']}\"" : "\"Due in: {$days_to_due} days ({$todo_item['due_date']})\"";
        $display_description = XToDoAbbreviateText($todo_item['description'], $todo_summary_length);
	$todo_status_code = XToDoIsItemOverdue($todo_item) ? $todo_status_icons[$todo_overdue_status_name] : $todo_status_icons[$todo_item['status']];
	$todo_status_code = " || {$todo_status_code}{$checked} ";
	$notes = empty($todo_item['notes']) ? '' : "&raquo; [- {$todo_item['notes']} -]";
        $output[] = "|| [- [[{$pagename}?action=todo&do=edit-todo-item&todo-item={$todo_item['name']}&todo-group={$todo_group}&todo-group-name={$group_name} | Edit]] -] {$todo_status_code}||[{$todo_item['priority']},{$todo_item['urgency']}] {$overdue[0]}{$completed[0]}{$display_description}{$completed[1]}{$overdue[1]} {$notes} ||\n";
    }

    return FmtPageName(implode('', $output), $pagename);
}

function XToDoListDisplay($pagename, $colorize, $criteria, $options) {
    global $todo_completed_status_name, $todo_priority_names, $todo_urgency_names;
    
    $group_name = FmtPageName('$Group', $pagename);
    $todo_group = "XToDo{$group_name}";
    $todo_items = XToDoListGet($todo_group, $criteria);

    $output[] = 
        "\n||align=center cellpadding=0 cellspacing=0 class=todo-list \n||! ID ||! Priority ||! Urgency ||! P*U ||! Status ||! Created ||! Due ||! Completed ||\n";

    foreach ($todo_items as $todo_item) {
        if ($colorize) list($overdue, $completed) = XToDoGetDisplayHighlight($todo_item);
	$notes = empty($todo_item['notes']) ? '' : "&raquo; [- {$todo_item['notes']} -]";
	$owner = empty($todo_item['owner']) ? '' : "({$todo_item['owner']})";

        $output[] = "|| [[{$pagename}?action=todo&do=edit-todo-item&todo-item={$todo_item['name']}&todo-group={$todo_group}&todo-group-name={$group_name} | {$todo_item['name']}]] ||".$todo_priority_names[$todo_item['priority']]." ||".$todo_urgency_names[$todo_item['urgency']]." || {$todo_item['p*u']} ||{$overdue[0]}{$completed[0]}{$todo_item['status']}{$completed[1]}{$overdue[1]} || {$todo_item['create_date']} || {$overdue[0]}{$todo_item['due_date']}{$overdue[1]} || {$completed[0]}{$todo_item['completed_date']}{$completed[1]} || \n|| ||%class=todo-category-text%{$todo_item['category']}%% {$owner} &mdash; %class=todo-description-text%{$todo_item['description']}%% {$notes} |||||||||||||| \n";
    }

    $output[] = "(:div class='todo-legend':)\nLegend: P*U: Priority * Urgency\n(:divend:)";

    return FmtPageName(implode('', $output), $pagename);
}

// Style.

$HTMLStylesFmt['todo'] = <<< EOT
.todo-form { border: none; }
.todo-form tr td { border: none; font-weight: plain; text-align: left; padding: 4px; }
.todo-form tr td.heading { text-align: right; width: 140px; padding-right: 6px; }
table.todo-list { border: 2px solid #ccc; }
table.todo-list tr.row1 { background-color: #eee; color: #555; }
table.todo-list tr.row1 td { border-bottom: 2px solid #ccc; }
table.todo-list tr:last-child.row1 td { border-bottom: none; }
table.todo-list th { background-color: #ddd; padding: 3px; font-weight: normal; border: 1px solid #ccc; color: #444; }
table.todo-list tr td { color: #666; }
table.todo-simple-list { margin-left: 0px; }
table.todo-simple-list tr td { border: none; padding: 4px; }
.todo-category-text { color: #666; border-bottom: 1px solid #ccc; }
.todo-description-text { color: #444; }
.todo-overdue-text { color: #f66; border-bottom: 1px solid #ccc; }
.todo-completed-text { color: #446600; border-bottom: 1px solid #ccc; }
.todo-legend { text-align: center; color: #555; font-size: smaller; }
EOT;

?>
