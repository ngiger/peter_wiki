<?php if(!defined('PmWiki'))exit;
/**
    An AJAX based support for editing pages in PmWiki
    Written by (c) Esteve Boix 2009-2011

    This text is written for PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version. See pmwiki.php for full details
    and lack of warranty.

    Copyright 2009-2011 Esteve Boix http://www.esteveb.com
*/
$RecipeInfo['AjaxEditSupport']['Version'] = '20110223';

# Tags added by this recipe
Markup('ajax-pages', 'directives',
    '/\\(:ajaxeditsupport\\s*(.*?):\\)/ei',
    "Keep(FmtAjaxEditSupport('$pagename',PSS('$1')))");

SDVA( $HandleActions, array(
    'ajaxeditsupport-pages' => 'HandleAjaxEditSupportPages',
    'ajaxeditsupport-attachments' => 'HandleAjaxEditSupportAttachments',
    'ajaxeditsupport-categories' => 'HandleAjaxEditSupportCategories',
    'ajaxeditsupport-edit' => 'HandleAjaxEditSupportEdit',
    'ajaxeditsupport-templates' => 'HandleAjaxEditSupportTemplates',
    )
);
SDVA(
        $AjaxEditSupportEditOptions,
        array(
        "attachments" =>
            array(
                "name"=>"Attachments",
                "regex"=>"/Attach:\\s*([^\\[\\]\\|\\s$]*)/m",
            ),
        "links" =>
            array(
                "name"=>"Links",
                "regex"=>"/\\[\\[\\s*(.*?)\\]\\]/",
            ),
        "headers" =>
            array(
                "name"=>"Headers",
                "regex"=>"/^\\!{1,6}.*$/m",
            ),
        "tables" =>
            array(
                "name"=>"Tables (& new rows)",
                "regex"=>"/^\\(\\:(table|cellnr).*\\:\\).*$/m",
            ),
    )
);

# pattern should be same as defined in markup
SDV($AjaxEditSupportTitlePattern,'/\\(:title\\s(.*?):\\)/i');
SDV($AjaxEditSupportPageBrowser,1);
SDV($AjaxEditSupportAttachmentBrowser,1);
SDV($AjaxEditSupportCategoryBrowser,1);
SDV($AjaxEditSupportEditBrowser,1);
SDV($AjaxEditSupportTemplatesBrowser,1);
SDV($AjaxEditSupportBrowserWidth,450);
SDV($AjaxEditSupportBrowserHeight,300);
SDV($AjaxEditSupportHidePages,'/(.*(GroupHeader|GroupFooter|RecentChanges)|Site.*)/');//
SDV($AjaxEditSupportShowChars,15);
SDVA($AjaxEditSupportTemplates,
    array(
        "site"=>array("name"=>"Site","pagename"=>'$SiteGroup.AESTemplates'),
        "group"=>array("name"=>"Group","pagename"=>'$Group.AESTemplates'),
        "personal"=>array("name"=>"Profile","pagename"=>'$AuthorGroup.$AuthId'),
    )
);
//SDV($AjaxEditSupportTemplatesRegEx,"/\\[\\[[\\#aes].*\\]\\]/");
SDV($AjaxEditSupportTemplatesRegEx,"/\\[\\[[\\#aes].*[^end]\\]\\]/");

SDV($AjaxEditSupportLoaderWidth,16);
SDV($AjaxEditSupportLoaderHeight,16);

$isAjaxEditSupportJsLoaded=false;

/**
 * Javascript code to display the lists on the editing page...
 */
function LoadAjaxEditSupportJs()
{
    global $isAjaxEditSupportJsLoaded,$PubDirUrl,$AjaxEditSupportPageBrowser,
           $AjaxEditSupportAttachmentBrowser,$AjaxEditSupportCategoryBrowser,$AjaxEditSupportEditBrowser,
           $AjaxEditSupportTemplatesBrowser,
           $AjaxEditSupportBrowserHeight,$AjaxEditSupportLoaderHeight,
           $AjaxEditSupportBrowserWidth,$AjaxEditSupportLoaderWidth ;
    $sid = session_id();
    @session_start();
    $_SESSION['ajaxeditsupport-cache']=array();

    if($isAjaxEditSupportJsLoaded==false)
    {
        $loaderTop=round($AjaxEditSupportBrowserHeight/2)-round($AjaxEditSupportLoaderHeight/2);
        $loaderLeft=round($AjaxEditSupportBrowserWidth/2)-round($AjaxEditSupportLoaderWidth/2);
        $html='
        <style>
        <!--
            tr.aesentry:hover { background-color: #e0e0ee; }
			tr.aestitle { background-color: #c0c0ff; }
            .aesmenuinfo {position:absolute;left:0px;top:20px;width:'.$AjaxEditSupportBrowserWidth.'px;height:'.$AjaxEditSupportBrowserHeight.'px;overflow:scroll;background:white;padding:5px;border:1px solid black;display:none;}
            .aesmenuloading {position:absolute;left:'.$loaderLeft.'px;top:'.$loaderTop.'px;width:'.$AjaxEditSupportLoaderWidth.'px;height:'.$AjaxEditSupportLoaderHeight.'px;background:white;display:none;}

        //-->
        </style>
        <script type="text/javascript" src="'.$PubDirUrl.'/ajaxeditsupport/jquery-1.5.min.js"></script>
        <script type="text/javascript" src="'.$PubDirUrl.'/ajaxeditsupport/jquery.caret.min.js"></script>
        <script>
        <!--
        var aesLastSentText="";        
        
        function closeWindows(e)
        {
            if($(e.target).closest("#ajaxeditsupportAttachments").get(0) == null && e.target.id!="linkAttachments") { $("#ajaxeditsupportAttachments").hide(); }
            if($(e.target).closest("#ajaxeditsupportPages").get(0) == null && e.target.id!="linkPages")             { $("#ajaxeditsupportPages").hide(); }
            if($(e.target).closest("#ajaxeditsupportCategories").get(0) == null && e.target.id!="linkCategories")   { $("#ajaxeditsupportCategories").hide(); }
            if($(e.target).closest("#ajaxeditsupportEdit").get(0) == null && e.target.id!="linkEdit")               { $("#ajaxeditsupportEdit").hide(); }
            if($(e.target).closest("#ajaxeditsupportTemplates").get(0) == null && e.target.id!="linkTemplates")     { $("#ajaxeditsupportTemplates").hide(); }
        }
        $(document).ready(function() {
            $("body").click(function(evt1) {closeWindows(evt1);});
            '.($AjaxEditSupportPageBrowser==1?'
            $("#linkPages").click(function(evt){
                evt.stopPropagation();
                closeWindows(evt);
                openSupportPagesWindow();
            });
            ':'').'
            '.($AjaxEditSupportAttachmentBrowser==1?'
            $("#linkAttachments").click(function(evt){
                evt.stopPropagation();
                closeWindows(evt);
                openSupportAttWindow();
            });
            ':'').'
            '.($AjaxEditSupportCategoryBrowser==1?'
            $("#linkCategories").click(function(evt){
                evt.stopPropagation();
                closeWindows(evt);
                openSupportCatWindow();
            });
            ':'').'
            '.(($AjaxEditSupportEditBrowser==1)?'
            document.getElementById("text").setAttribute("wrap","off");
            $("#text").css("overflow","scroll");
            $("#text").css("overflow","-moz-scrollbars-horizontal");
            $("#text").css("overflow-y","scroll");
            $("#text").css("overflow-x","scroll");

            $("#linkEdit").click(function(evt){
                evt.stopPropagation();
                closeWindows(evt);
                openSupportEditWindow();
            });
            ':'').'
            '.($AjaxEditSupportTemplatesBrowser==1?'
            $("#linkTemplates").click(function(evt){
                evt.stopPropagation();
                closeWindows(evt);
                openSupportTemplatesWindow();
            });
            ':'').'
        });

        '.($AjaxEditSupportPageBrowser==1?'
            
            function openSupportPagesWindow()
            {
                if($("#ajaxeditsupportPages").is(":visible"))
                {
                    $("#ajaxeditsupportPages").hide();
                    return;
                }
                aesDisplayPages("local");
            }
            function aesDisplayPages(mode,group)
            {
                $(document).ready(function(){
                    $("#ajaxeditsupportPages").show();
                    $("#ajaxeditsupportLoading").show();
                    $.get("?action=ajaxeditsupport-pages", { aesmode: mode, group: group },
                        function(data){
                            $("#ajaxeditsupportPages").html(data);
                            $("#ajaxeditsupportLoading").hide();
                        });
                 });
            }
            function aesInsertInclude(includedPage)
            {
                insMarkup("(:include "+includedPage+":)", "","");
                $("#ajaxeditsupportPages").hide();
            }
        ':'').'
        '.($AjaxEditSupportAttachmentBrowser==1?'
            function openSupportAttWindow()
            {
                if($("#ajaxeditsupportAttachments").is(":visible"))
                {
                    $("#ajaxeditsupportAttachments").hide();
                    return;
                }
                aesDisplayAttachments("viewfiles","");
            }
            function aesDisplayAttachments(mode,element)
            {
                $("#ajaxeditsupportAttachments").show();
                $("#ajaxeditsupportAttLoading").show();
                $(document).ready(function(){
                    $("#ajaxeditsupportAttLoading").show();
                    $.get("?action=ajaxeditsupport-attachments", { aesmode: mode, element: element},
                        function(data){
                            $("#ajaxeditsupportAttachments").html(data);
                            $("#ajaxeditsupportAttLoading").hide();
                        });
                 });
            }
            function displayPageAttachments(element)
            {
                aesDisplayAttachments("viewfiles",element);
            }
            function aesInsertAttachment(attachment)
            {
                insMarkup("Attach:"+attachment, "", "");
                $("#ajaxeditsupportAttachments").hide();
            }
        ':'').'
        '.($AjaxEditSupportCategoryBrowser==1?'
            function openSupportCatWindow()
            {
                if($("#ajaxeditsupportCategories").is(":visible"))
                {
                    $("#ajaxeditsupportCategories").hide();
                    return;
                }
                aesDisplayCategories();
            }
            function aesDisplayCategories()
            {
                $("#ajaxeditsupportCatLoading").show();
                $("#ajaxeditsupportCategories").show();
                $(document).ready(function(){
                    $.get("?action=ajaxeditsupport-categories", {},
                        function(data){
                            $("#ajaxeditsupportCategories").html(data);
                            $("#ajaxeditsupportCatLoading").hide();
                        });
                 });
            }
            function insertCategory(category)
            {
                insMarkup("[[!"+category+"]]", "","");
                $("#ajaxeditsupportCategories").hide();
            }
        ':'').'
        '.($AjaxEditSupportEditBrowser==1?'
            function openSupportEditWindow()
            {
                if($("#ajaxeditsupportEdit").is(":visible"))
                {
                    $("#ajaxeditsupportEdit").hide();
                    return;
                }
                aesDisplayEdit("none");
            }
            function aesDisplayEdit(findMode)
            {
                $(document).ready(function(){
                    if(aesLastSentText!=$("#text").val() && findMode!="none")
                    {
                        text=escape($("#text").val());
                        getFromCache=0;
                        aesLastSentText=$("#text").val();
                    }
                    else
                    {
                        text="";
                        getFromCache=1;
                    }

                    $("#ajaxeditsupportEdit").show();
                    $("#ajaxeditsupportEditLoading").show();
                    $.post("?action=ajaxeditsupport-edit", { wikitext: text,fromCache: getFromCache, mode: findMode},
                        function(data){
                            $("#ajaxeditsupportEdit").html(data);
                            $("#ajaxeditsupportEditLoading").hide();
                        })
                });
            }
            function aesSelect(startPos,endPos,line)
            {
                $("#ajaxeditsupportEdit").hide();
                var ta = $("#text");
                ta.focus();
                ta.caret(startPos,endPos);
                ta.scrollTop((line - 1) * (ta.height() / document.getElementById("text").rows));
                return;
            }
            ':'').'
        '.($AjaxEditSupportTemplatesBrowser==1?'
            function openSupportTemplatesWindow()
            {
                if($("#ajaxeditsupportTemplates").is(":visible"))
                {
                    $("#ajaxeditsupportTemplates").hide();
                    return;
                }
                aesDisplayTemplates("none");
            }
            function aesDisplayTemplates(which)
            {
                $(document).ready(function(){
                    $("#ajaxeditsupportTemplates").show();
                    $("#ajaxeditsupportTemplatesLoading").show();
                    $.post("?action=ajaxeditsupport-templates", {templates: which},
                        function(data){
                            $("#ajaxeditsupportTemplates").html(data);
                            $("#ajaxeditsupportTemplatesLoading").hide();
                        })
                });
            }
            function aesInsertTemplate(template)
            {
                insMarkup(template, "", "");
                $("#ajaxeditsupportTemplates").hide();
            }
        ':'').'

        function aesInsertLinkTo(page)
        {
            insMarkup("[["+page+"|", "]]", "Link name");
            '.($AjaxEditSupportPageBrowser==1?'$("#ajaxeditsupportPages").hide();':'').'
            '.($AjaxEditSupportAttachmentBrowser==1?'$("#ajaxeditsupportAttachments").hide();':'').'
        }
        //-->
        </script>
        ';
        $isAjaxEditSupportJsLoaded=true;
        return $html;
    }
    if (!$sid) session_write_close();
    return "";
}

/**
 *  HTML code with the buttons and loading divs
 */
function FmtAjaxEditSupportDiv($linkImage,$linkId,$linkName,$menuId,$loadingId)
{
    global $PubDirUrl,$AjaxEditSupportLoaderWidth,$AjaxEditSupportLoaderHeight;
    return '<span style="position:relative">
                <img src="'.$PubDirUrl.'/ajaxeditsupport/'.$linkImage.'" border="0" align="absmiddle"/><a href="#" id="'.$linkId.'">'.$linkName.'</a>
                <div id="'.$menuId.'" class="aesmenuinfo"></div>
                <div id="'.$loadingId.'" class="aesmenuloading"><img src="'.$PubDirUrl.'/ajaxeditsupport/loader16.gif" width="'.$AjaxEditSupportLoaderWidth.'" height="'.$AjaxEditSupportLoaderHeight.'"/></div>
        </span>';
}
function FmtAjaxEditSupport($pagename, $args)
{
    global $AjaxEditSupportPageBrowser,
        $AjaxEditSupportAttachmentBrowser,$AjaxEditSupportCategoryBrowser,
        $AjaxEditSupportEditBrowser,$AjaxEditSupportTemplatesBrowser
        ;
    $html = LoadAjaxEditSupportJs();
    $html.= '
		<br/>
        '.($AjaxEditSupportPageBrowser==1?FmtAjaxEditSupportDiv('aes-links.gif','linkPages','Pages','ajaxeditsupportPages','ajaxeditsupportLoading'):'').'
        '.($AjaxEditSupportAttachmentBrowser==1?FmtAjaxEditSupportDiv('aes-attach.gif','linkAttachments','Attachments','ajaxeditsupportAttachments','ajaxeditsupportAttLoading'):'').'
        '.($AjaxEditSupportCategoryBrowser==1?FmtAjaxEditSupportDiv('aes-categories.gif','linkCategories','Categories','ajaxeditsupportCategories','ajaxeditsupportCatLoading'):'').'
        '.($AjaxEditSupportEditBrowser==1?FmtAjaxEditSupportDiv('aes-edit.gif','linkEdit','Current page','ajaxeditsupportEdit','ajaxeditsupportEditLoading'):'').'
        '.($AjaxEditSupportTemplatesBrowser==1?FmtAjaxEditSupportDiv('aes-templates.gif','linkTemplates','Templates','ajaxeditsupportTemplates','ajaxeditsupportTemplatesLoading'):'').'
        ';

    return $html;
}

/**
 * Retrieves a list of all the available pages/groups so that we can insert a link.
 * It expects the request to contain:
 *  'aesmode'=>local for current group, groups to list all the groups, or 'listgroup' to
 *             list an actual group.
 *  'group' => The group listed in 'listgroup'.
 * @param string $pagename
 * @param string $auth
 */
function HandleAjaxEditSupportPages($pagename,$auth='read')
{
    global $WikiDir,$UploadPrefixFmt,$AjaxEditSupportTitlePattern,$PubDirUrl;
    $sid = session_id();
    @session_start();
    $groupPage=explode(".",$pagename);

    $pages=AjaxEditSupportGetFileList();
    $html="<img src='$PubDirUrl/ajaxeditsupport/format-justify-fill.png' width='16' height='16' border='0' align='absmiddle'/> <a href=# onClick='aesDisplayPages(\"local\");'>This group</a>
        | <img src='$PubDirUrl/ajaxeditsupport/document-open.png' width='16' height='16' border='0' align='absmiddle'/> <a href=# onClick='aesDisplayPages(\"groups\");'>List groups</a><hr size='1' color='black'/>";

    switch($_REQUEST['aesmode'])
    {
        case "local":
            if(isset($_SESSION['ajaxeditsupport-cache']['local']))
            {
                $htmlLocal=$_SESSION['ajaxeditsupport-cache']['local'];
            }
            else
            {
                $htmlLocal="Pages in this group ($groupPage[0]):<br/><table>";
                $htmlLocal.=AjaxEditSupportListGroup($pages,$groupPage[0],$pagename,
                        array(
                            array("aesInsertLinkTo","Link to"),
                            array("aesInsertInclude","Include")
                            )
                        );
                $_SESSION['ajaxeditsupport-cache']['local']=$htmlLocal;
            }
            break;
        case "groups":
            if(isset($_SESSION['ajaxeditsupport-cache']['groups']))
            {
                $htmlLocal=$_SESSION['ajaxeditsupport-cache']['groups'];
            }
            else
            {
                $htmlLocal="Available groups:<br/>";
                $htmlLocal.=AjaxEditSupportListGroups($pages,$pagename,"aesDisplayPages");
            }
            break;
        case "listgroup":
            $htmlLocal.="Pages in group ".$_REQUEST['group'].":<br/><table>";
            $htmlLocal.=AjaxEditSupportListGroup($pages,$_REQUEST['group'],$pagename,array(array("aesInsertLinkTo","Link to")));
            break;
    }
    $html.=$htmlLocal;
    print($html);
    if (!$sid) session_write_close();
}


/**
 * Retrieves a list of all the available attachments so that we can insert a link.
 * It expects the request to contain:
 *  'aesmode'=>"viewfiles" to list the attachments of files for a given page, groups to list all the groups,
 *              or 'listgroup' to list an actual group, 'listpage' to list an actual page of a group.
 *  'element' => The group listed in 'listgroup', the page for 'listpage'.
 * @param string $pagename
 * @param string $auth
 */
function HandleAjaxEditSupportAttachments($pagename,$auth='read')
{
    global $UploadPrefixFmt;
    global $UploadDir, $UploadPrefixFmt, $UploadUrlFmt,
            $EnableUploadVersions, $EnableDirectDownload,$PubDirUrl;


    $sid = session_id();
    @session_start();
    $groupPage=explode(".",$pagename);

    $pages=AjaxEditSupportGetFileList();
    $html="
        <img src='$PubDirUrl/ajaxeditsupport/blank-sheet.png' width='16' height='16' border='0' align='absmiddle'/> <a href=# onClick='aesDisplayAttachments(\"viewfiles\",\"\");'>This page</a>
        | <img src='$PubDirUrl/ajaxeditsupport/format-justify-fill.png' width='16' height='16' border='0' align='absmiddle'/> <a href=# onClick='aesDisplayAttachments(\"listgroup\",\"\");'>Pages in this group</a>
        | <img src='$PubDirUrl/ajaxeditsupport/document-open.png' width='16' height='16' border='0' align='absmiddle'/> <a href=# onClick='aesDisplayAttachments(\"groups\",\"\");'>List groups</a><hr size='1' color='black'/>";

    switch($_REQUEST['aesmode'])
    {
        case "viewfiles":
            if(isset($_SESSION['ajaxeditsupport-cache']['att-local']) && $_REQUEST['element']=="")
            {
                $htmlLocal=$_SESSION['ajaxeditsupport-cache']['att-local'];
            }
            else
            {
                if($_REQUEST['element']=="")
                {
                    $element=$pagename;
                }
                else
                {
                    $element=$_REQUEST['element'];
                }
                $htmlLocal=AjaxEditSupportListFiles($element,$pagename);
                if($_REQUEST['element']=="")
                {
                    $_SESSION['ajaxeditsupport-cache']['att-local']=$htmlLocal;
                }
            }
            break;
        case "groups":
            if(isset($_SESSION['ajaxeditsupport-cache']['groups-att']))
            {
                $htmlLocal=$_SESSION['ajaxeditsupport-cache']['groups-att'];
            }
            else
            {
                $htmlLocal="Available groups:<br/>";
                $htmlLocal.=AjaxEditSupportListGroups($pages, $pagename, "aesDisplayAttachments");
                $_SESSION['ajaxeditsupport-cache']['groups-att']=$htmlLocal;
            }
            break;
        case "listgroup":
            $htmlLocal="Pages in this group:<br/>";

            if($_REQUEST['element']=="")
            {
                $temp=explode(".",$pagename);
                $group=$temp[0];
            }
            else
            {
                $group=$_REQUEST['element'];
            }
            //$htmlLocal.=AjaxEditSupportListGroup($pages,$group,$page,"displayPageAttachments");
            $htmlLocal.=AjaxEditSupportListGroup($pages,$group,$page,array(array("displayPageAttachments","List attachments")));

            //$htmlLocal.=AjaxEditSupportListGroup($pages, $group, "displayPageAttachments");
            $_SESSION['ajaxeditsupport-cache']['group-att']=$htmlLocal;
            break;
    }
    $html.=$htmlLocal;
    print($html);
    if (!$sid) session_write_close();
}
/**
 *
 * Retrieves a list of all the available Categories so that we can insert a link.
 * @param <type> $pagename
 * @param <type> $auth
 */
function HandleAjaxEditSupportCategories($pagename,$auth='read')
{
    global $CategoryGroup;
    $sid = session_id();
    @session_start();
    $groupPage=explode(".",$pagename);

    $pages=AjaxEditSupportGetFileList();
    $html="";

    $htmlLocal.="Available categories:<br/><table width='100%'>";
    $htmlLocal.=AjaxEditSupportListGroup($pages,$CategoryGroup,$pagename,array(array("insertCategory","Insert")));
    $html.=$htmlLocal;
    print($html);
    if (!$sid) session_write_close();
}

function AjaxEditSupportGetFileList()
{
    global $WikiDir,$AjaxEditSupportHidePages;

    $sid = session_id();
    @session_start();
    if(isset($_SESSION['ajaxeditsupport-cache']['list']))
    {
        $pages=$_SESSION['ajaxeditsupport-cache']['list'];
    }
    else
    {
        $pages=$WikiDir->ls();
        sort($pages);

		foreach($pages as $id=>$page)
		{
			if(preg_match($AjaxEditSupportHidePages,$page)>0)
			{
				unset($pages[$id]);
			}
		}
        $_SESSION['ajaxeditsupport-cache']['list']=$pages;
    }
    if (!$sid) session_write_close();
    return $pages;
}

function AjaxEditSupportListGroup($pages,$groupToList,$page,$links)
{
    global $AjaxEditSupportTitlePattern,$PubDirUrl,$ScriptUrl,$CategoryGroup;
    $groupNameLen=strlen($groupToList);
    $pageParts=explode(".",$page);
    $html="";
    foreach($pages as $currentpage)
    {
        if(substr($currentpage,0,$groupNameLen)==$groupToList)//$groupPage[0])
        {
            $rp=RetrieveAuthPage($currentpage, 'read',false,READPAGE_CURRENT);
            if ($rp['=auth']['read']) {
                if ($AjaxEditSupportTitlePattern!="" && preg_match($AjaxEditSupportTitlePattern, $rp['text'], $matches))
                {
                    $title = htmlentities($matches[1]);
                }
                else
                {
                    $title = '';
                }

                $currentpageParts=explode(".",$currentpage);
                $linkStr=($currentpageParts[0]==$pageParts[0] || $groupToList==$CategoryGroup)?$currentpageParts[1]:$currentpage;
                $html.="<tr class='aesentry'><td><img src='$PubDirUrl/ajaxeditsupport/edit-find.png' width='16' height='16' border='0' align='absmiddle'/>"
                    .MakeLink($page, $currentpage,null,null,"<a href='\$LinkUrl' target='_blank'>$linkStr</a>")
                    ."</td>";
                if($groupToList!=$CategoryGroup)
                    $html.="<td>$title&nbsp;</td>";
                $html.="<td nowrap>";
                $i=0;
                foreach($links as $link)
                {
                    $html.="<a href=# onClick='$link[0](\"".$linkStr."\")'>$link[1]</a>";
                    if(isset($links[$i+1])){$html.=" | ";};
                    $i++;
                }
                $html.="</td></tr>";
            }
        }
    }
    return "<table width='100%'><tr class='aestitle'><th>Page name</th>".
        ($groupToList==$CategoryGroup?"":"<th>Title</th>")
        ."<th>Actions</th></tr>$html</table>";


}

function AjaxEditSupportListGroups($pages,$page,$link)
{
    global $CategoryGroup;

    $groupPage=explode(".",$page);
    $groups=array();
    foreach($pages as $currentpage)
    {
        $temp=explode(".",$currentpage);
        if(!isset($groups[$temp[0]]) && $groupPage[0]!=$temp[0] && (isset($CategoryGroup) && $temp[0]!=$CategoryGroup))
            $groups[$temp[0]]="";
    }
    $html="";
    foreach($groups as $group=>$data)
    {
        $html.="<tr class='aesentry'><td><a href=# onClick='$link(\"listgroup\",\"$group\")'>$group</a></td></tr>";
    }
    return "<table width='100%'><tr class='aestitle'><th>Group name</th></tr>$html</table>";
}

function AjaxEditSupportListFiles($page,$currentpage)
{
    global $UploadDir,$UploadPrefixFmt,$PageUrl,$EnableDirectDownload,$UploadUrlFmt,$PubDirUrl;

    // Check for auth
    $rp=RetrieveAuthPage($currentpage, 'read',false,READPAGE_CURRENT);
    if (!$rp['=auth']['read']) return "";

    $htmlLocal="Attachments in page $page:<br/><table width='100%'><tr class='aestitle'><th>File</th><th width='130'>Actions</th></tr>";

    ## locations
    $pageurl = FmtPageName( '$PageUrl', $page );
    $uploaddir = FmtPageName( "$UploadDir$UploadPrefixFmt", $page);
    $uploadurl = FmtPageName(
        IsEnabled($EnableDirectDownload, 1) ? "$UploadUrlFmt$UploadPrefixFmt/" : "\$PageUrl?action=download&amp;upname=",
        $page);

    $dirp = @opendir($uploaddir);
    $noAttachedFiles="<tr><td colspan='2'>No attached files</td></tr>";
    if ($dirp)
    {
        $filelist = array();
        while (($file = readdir($dirp))!==false)
        {
            if ( $file{0} == '.' ) continue;
            $filelist[$file] = $file;
        }
        closedir($dirp);
        natcasesort($filelist);
        if(count($filelist)==0)
        {
            $htmlLocal.=$noAttachedFiles;
        }
        else
        {
            foreach($filelist as $file)
            {
                if($page==$currentpage)
                {
                    $attachfile=$file;
                }
                else
                {
                    $groupPageName=explode(".",$page);
                    $currentGroupPageName=explode(".",$currentpage);
                    if($groupPageName[0]==$currentGroupPageName[0])
                    {
                        $attachfile=$groupPageName[1]."/".$file;
                    }
                    else
                    {
                        $attachfile=$page."/".$file;
                    }
                }

                $htmlLocal.="<tr class='aesentry'>
                    <td><img src='$PubDirUrl/ajaxeditsupport/edit-find.png' width='16' height='16' border='0' align='absmiddle'/><a href='".$uploadurl."$file' target='_blank'>$file</a></td>
                    <td><a href=# onClick='aesInsertAttachment(\"$attachfile\")'>Attach</a>
						| 
                    	<a href=# onClick='aesInsertLinkTo(\"Attach:$attachfile\")'>As link</a></td>

                </tr>";
            }
        }
    }
    else
    {
        $htmlLocal.=$noAttachedFiles;
    }
    $htmlLocal.="</table>";
    return $htmlLocal;

}

/**
 * Finds specific patterns on the wikitext it receives and creates an HTML with javascript code to "jump"
 * to those findings.
 *  'mode'=>local for current group, groups to list all the groups, or 'listgroup' to
 *             list an actual group.
 *  'wikitext' => The text to analyze
 * @param string $pagename
 * @param string $auth
 */
function HandleAjaxEditSupportEdit($pagename,$auth='read')
{
    global $WikiDir,$UploadPrefixFmt,$AjaxEditSupportTitlePattern,$PubDirUrl,$AjaxEditSupportEditOptions;
    $sid = session_id();
    session_start();

    $html="";
    $i=1;
    foreach($AjaxEditSupportEditOptions as $id=>$option)
    {
        $html.="<img src='$PubDirUrl/ajaxeditsupport/format-justify-fill.png' width='16' height='16' border='0' align='absmiddle'/> <a href=# onClick='aesDisplayEdit(\"$id\");'>".$option['name']."</a>";
        if($i!=count($AjaxEditSupportEditOptions)) $html.=" | ";
        $i++;
    }
    $html.="<hr size='1' color='black'/>";
    if($_REQUEST['fromCache']=="0")
    {
        $wikitext=$_REQUEST['wikitext'];
        $_SESSION['ajaxeditsupport-cache']['wikitext']=$wikitext;
    }
    else
    {
        $wikitext=$_SESSION['ajaxeditsupport-cache']['wikitext'];
    }
    if(isset($AjaxEditSupportEditOptions[$_REQUEST['mode']]))
    {
        $regex=$AjaxEditSupportEditOptions[$_REQUEST['mode']]['regex'];
        $results=AjaxEditSupportFindMatches(urldecode($wikitext),$regex);
        $html.=AjaxEditSupportConvertFindToHtml($results);

    }
    if (!$sid) session_write_close();
    print($html);
}

function AjaxEditSupportFindMatches($text,$regularExp)
{
    global $AjaxEditSupportShowChars;

    preg_match_all($regularExp, $text, $findings, PREG_PATTERN_ORDER);
    $results=array();
    $lastposition=0;
    $textLines=explode("\n",  $text);
    foreach($findings[0] as $finding)
    {
        $position=strpos($text,$finding,$lastposition);
        $partial=array();
        $partial['position']=$position;
        $partial['text']=$finding;
        // We have to determine the line where we've found the match
        $l=0;
        $la=0;
        $i=0;
        foreach($textLines as $line)
        {
            $la=$l;
            $l+=strlen($line)+1;
            if($l>$position)
            {
                break;
            }
            $i++;
        }
        $partial['line']=$i;
        $partial['preText']=substr(
                $text,
                ($position-$AjaxEditSupportShowChars)<$la?$la:$position-$AjaxEditSupportShowChars,
                ($position-$AjaxEditSupportShowChars)<$la?$position-$la:$AjaxEditSupportShowChars
                );
        $partial['postText']=substr(
                $text,
                $position+strlen($finding),
                ($position+strlen($finding)+$AjaxEditSupportShowChars)>$l?($l-$position-strlen($finding)):$AjaxEditSupportShowChars
                );
        $results[]=$partial;
        $lastposition=$position+strlen($finding);
    }
    return $results;
}

function AjaxEditSupportConvertFindToHtml($findings)
{
    global $AjaxEditSupportShowChars;

    $html="<table>";
    foreach($findings as $finding)
    {
        $html.="<tr>";
        $html.="<td><a href=# onClick='aesSelect(".$finding['position'].",".($finding['position']+strlen($finding['text'])).",".$finding['line'].");'>@".$finding['position'].": ".
            htmlentities((strlen($finding['preText'])<$AjaxEditSupportShowChars?"":"...").$finding['preText'])."<strong style='color:red'>".
            htmlentities(substr($finding['text'],0,strlen($finding['text']))).
            "</strong>".
            htmlentities($finding['postText'].(strlen($finding['postText'])<$AjaxEditSupportShowChars?"":"..."))."</a></td>";
        $html.="</tr>";

    }
    $html.="</table>";
    return $html;
}

/**
 * Finds specific patterns on the wikitext it receives and creates an HTML with javascript code to "jump"
 * to those findings.
 *  'mode'=>local for current group, groups to list all the groups, or 'listgroup' to
 *             list an actual group.
 *  'wikitext' => The text to analyze
 * @param string $pagename
 * @param string $auth
 */
function HandleAjaxEditSupportTemplates($pagename,$auth='read')
{
    global $WikiDir,$UploadPrefixFmt,$PubDirUrl,$AjaxEditSupportTemplates,$AjaxEditSupportTemplatesRegEx;
    $sid = session_id();
    session_start();

    $i=1;
    foreach($AjaxEditSupportTemplates as $id=>$option)
    {
        $html.="<img src='$PubDirUrl/ajaxeditsupport/format-justify-fill.png' width='16' height='16' border='0' align='absmiddle'/> <a href=# onClick='aesDisplayTemplates(\"$id\");'>".$option['name']."</a>";
        if($i!=count($AjaxEditSupportTemplates)) $html.=" | ";
        $i++;
    }
    $html.="<hr width='100%' color='black' size='1'/>";

    $option="site";
    if(key_exists($_REQUEST['templates'], $AjaxEditSupportTemplates))
    {
        $option=$_REQUEST['templates'];
    }
    $page=RetrieveAuthPage(FmtPageName($AjaxEditSupportTemplates[$option]['pagename'],$pagename), "read");
    preg_match_all($AjaxEditSupportTemplatesRegEx, $page['text'], $sections, PREG_PATTERN_ORDER);

    if(count($sections[0])>0)
    {
        $templates=array();
        foreach($sections[0] as $section)
        {
            $sectionStart=substr($section,2,strlen($section)-4);
            $sectionEnd=$sectionStart."end";
            $templates[]=TextSection($page['text'], $sectionStart.$sectionEnd);
        }
        $html.="<table width='100%'>";
        $html.="<tr class='aestitle'><th>Template source</th><th width='90'>Actions</th></tr>";
        $i=0;
        foreach($templates as $template)
        {
            $jsEntry=str_replace("\"", "\\\"", $template);
            $jsEntry=str_replace("\n", "\\n", $jsEntry);
            $html.="<tr class='aesentry'><td><pre>".htmlentities($template)."</pre></td><td><a href='#' onClick='aesInsertTemplate(\"".utf8_encode($jsEntry)."\", \"\", \"\")'>Insert</a></td></tr>";
            $i++;
        }
        $html.="</table>";
    }
    else
    {
        $html.="<br/><br/><div align='center'>No templates defined in this page...</div>";
    }
    if (!$sid) session_write_close();
    print($html);
}
