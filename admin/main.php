<?php
//
//  ------------------------------------------------------------------------ //
//             XF Guestbook                                                  //
// ------------------------------------------------------------------------- //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

include __DIR__ . '/../../../include/cp_header.php';
include_once __DIR__ . '/../include/cp_functions.php';
include_once __DIR__ . '/admin_header.php';

if (!isset($GLOBALS['xoopsModuleConfig']['flagdir'])) {
    redirect_header(XOOPS_URL . '/modules/system/admin.php?fct=modulesadmin&op=update&module=' . $GLOBALS['xoopsModule']->dirname(), 4, AM_XFGUESTBOOK_MUST_UPDATE);
}

include_once __DIR__ . '/../class/util.php';
//include_once("../class/msg.php");

$op     = \Xmf\Request::getCmd('op', 'show');
$msg_id = \Xmf\Request::getArray('msg_id', 0);
/*
if (isset($_GET['op'])) {
    $op = $_GET['op'];
} elseif (isset($_POST['op'])) {
    $op = $_POST['op'];
} else {
    $op = 'show';
}

if (isset($_GET['msg_id'])) {
    $msg_id = (int)$_GET['msg_id'];
} elseif (isset($_POST['msg_id'])) {
    $msg_id = (int)$_POST['msg_id'];
} else {
    $msg_id = 0;
}
*/
$msgHandler = xoops_getModuleHandler('msg');

function delete()
{
    global $msgHandler;
    //$msg_count = (!empty($_POST['msg_id']) && is_array($_POST['msg_id'])) ? count($_POST['msg_id']) : 0;
    $msg_id = Xmf\Request::getArray('msg_id', array(), 'POST');
    $msg_id = array_map('intval', $msg_id);
    $msg_count = count($msg_id);
    if ($msg_count > 0) {
        if (!$xoopsSecurity->check()) {
            redirect_header($_SERVER['PHP_SELF'], 3, implode('<br>', $xoopsSecurity->getErrors()));
        }
        $messagesent = AM_XFGUESTBOOK_MSGDELETED;
        for ($i = 0; $i < $msg_count; ++$i) {
            $msg      = $msgHandler->get($_POST['msg_id'][$i]);
            //$filename = $msg->getVar('title');
            $filename = $msg->getVar('photo');
            if (!$msgHandler->delete($msg)) {
                $messagesent = AM_XFGUESTBOOK_ERRORDEL;
            }
            if ('' !== $filename) {
                $filename = realpath(XOOPS_UPLOAD_PATH . '/' . $GLOBALS['xoopsModule']->getVar('dirname') . '/' . $filename);
                unlink($filename);
            }
        }
    } else {
        $messagesent = AM_XFGUESTBOOK_NOMSG;
    }
    redirect_header($_SERVER['PHP_SELF'], 2, $messagesent);
}

function approve()
{
    $msgHandler = xoops_getModuleHandler('msg');
    $msg_id     = Xmf\Request::getArray('msg_id', array(), 'POST');
    $msg_id     = array_map('intval', $msg_id);
    $msg_count  = count($msg_id);
    //$msg_count  = (!empty($_POST['msg_id']) && is_array($_POST['msg_id'])) ? count($_POST['msg_id']) : 0;
    if ($msg_count > 0) {
        if (!$xoopsSecurity->check()) {
            redirect_header($_SERVER['PHP_SELF'], 3, implode('<br>', $xoopsSecurity->getErrors()));
        }
        $messagesent = AM_XFGUESTBOOK_VALIDATE;
        for ($i = 0; $i < $msg_count; ++$i) {
            $msg = $msgHandler->get($_POST['msg_id'][$i]);
            $msg->setVar('moderate', 0);
            if (!$msgHandler->insert($msg)) {
                $messagesent = AM_XFGUESTBOOK_ERRORVALID;
            }
        }
    } else {
        $messagesent = AM_XFGUESTBOOK_NOMSG;
    }
    redirect_header($_SERVER['PHP_SELF'], 2, $messagesent);
}

function banish()
{
    $msgHandler = xoops_getModuleHandler('msg');
    $msg_count  = (!empty($_POST['msg_id']) && is_array($_POST['msg_id'])) ? count($_POST['msg_id']) : 0;
    if ($msg_count > 0) {
        if (!$xoopsSecurity->check()) {
            redirect_header($_SERVER['PHP_SELF'], 3, implode('<br>', $xoopsSecurity->getErrors()));
        }
        $messagesent = AM_XFGUESTBOOK_BANISHED;
        for ($i = 0; $i < $msg_count; ++$i) {
            $msg    = $msgHandler->get($_POST['msg_id'][$i]);
            $ip[$i] = $msg->getVar('poster_ip');
            $msg->setVar('moderate', 1);
            if (!$msgHandler->insert($msg)) {
                $messagesent = AM_XFGUESTBOOK_ERRORBANISHED;
            }
        }
        $ip     = array_unique($ip);
        $badips = XfguestbookUtil::get_badips();
        foreach ($ip as $oneip) {
            if (!in_array($oneip, $badips)) {
                $sql    = 'INSERT INTO ' . $GLOBALS['xoopsDB']->prefix('xfguestbook_badips') . " (ip_value) VALUES ('$oneip')";
                $result = $GLOBALS['xoopsDB']->query($sql);
            }
        }
    } else {
        $messagesent = AM_XFGUESTBOOK_NOMSG;
    }

    redirect_header($_SERVER['PHP_SELF'], 2, $messagesent);
}

function show()
{
    global $msgHandler, $pathIcon16;
    $pick              = Xmf\Request::getInt('pick', 0, 'get');
    $start             = Xmf\Request::getInt('start', 0, 'get');
    $sel_status        = Xmf\Request::getInt('sel_status', 0, 'get');
    $sel_order         = Xmf\Request::getInt('sel_order', 0, 'get');
    /*
    $pick              = isset($_GET['pick']) ? (int)$_GET['pick'] : 0;
    $start             = isset($_GET['start']) ? (int)$_GET['start'] : 0;
    $sel_status        = isset($_GET['sel_status']) ? $_GET['sel_status'] : 0;
    $sel_order         = isset($_GET['sel_order']) ? $_GET['sel_order'] : 0;
    */
    $limit             = 10;
    $status_option0    = '';
    $status_option1    = '';
    $status_option2    = '';
    $order_option_asc  = '';
    $order_option_desc = '';

    switch ($sel_status) {
        case 0:
            $status_option0 = 'selected';
            $title          = AM_XFGUESTBOOK_ALLMSG;
            $criteria       = new Criteria('msg_id', 0, '>');
            $criteria->setSort('post_time');
            break;

        case 1:
            $status_option1 = 'selected';
            $title          = AM_XFGUESTBOOK_PUBMSG;
            $criteria       = new Criteria('moderate', '0');
            $criteria->setSort('post_time');
            break;

        case 2:
            $status_option2 = 'selected';
            $title          = AM_XFGUESTBOOK_WAITMSG;
            $criteria       = new Criteria('moderate', '1');
            $criteria->setSort('post_time');
            break;

    }

    switch ($sel_order) {
        case 1:
            $order_option_asc = 'selected';
            $criteria->order = 'ASC';
            break;

        default:
        case 0:
            $order_option_desc = 'selected';
            $criteria->order = 'DESC';
            break;
    }

    //$totalcount = $msgHandler->countMsg($criteria);
    //$criteria->setOrder('DESC');
    $criteria->setLimit($limit);
    $criteria->setStart($start);
    $msg        = $msgHandler->getObjects($criteria);
    $totalcount = empty($msg) ? 0 : count($msg);

    $badips = XfguestbookUtil::get_badips();

    /* -- Code to show selected terms -- */
    echo "<form name='pick' id='pick' action='" . $_SERVER['PHP_SELF'] . '\' method=\'GET\' style=\'margin: 0;\'>';

    echo "
        <table width='100%' cellspacing='1' cellpadding='2' border='0' style='border-left: 1px solid silver; border-top: 1px solid silver; border-right: 1px solid silver;'>
            <tr>
                <td><span style='font-weight: bold; font-size: 12px; font-variant: small-caps;'>" . $title . ' : ' . $totalcount . "</span></td>
                <td align='right'>
                " . AM_XFGUESTBOOK_DISPLAY . " :
                    <select name='sel_status' onchange='submit()'>
                        <option value = '0' $status_option0>" . AM_XFGUESTBOOK_ALLMSG . " </option>
                        <option value = '1' $status_option1>" . AM_XFGUESTBOOK_PUBMSG . " </option>
                        <option value = '2' $status_option2>" . AM_XFGUESTBOOK_WAITMSG . ' </option>
                    </select>
                ' . AM_XFGUESTBOOK_SELECT_SORT . "
                    <select name='sel_order' onchange='submit()'>
                        <option value = '1' $order_option_asc>" . AM_XFGUESTBOOK_SORT_ASC . "</option>
                        <option value = '0' $order_option_desc>" . AM_XFGUESTBOOK_SORT_DESC . '</option>
                    </select>
                </td>
            </tr>
        </table>
        </form>';
    /* -- end code to show selected terms -- */

    echo "<table class='width100' border='1' cellpadding ='2' cellspacing='1'>";
    echo "<tr class='bg3'>";
    echo "<td class='center bold'></td>";
    echo "<td class='center bold'><input type='hidden' name='op' value='delete'></td>";
    echo "<td class='center bold'>" . AM_XFGUESTBOOK_NAME . '</td>';
    echo "<td class='center bold'>" . AM_XFGUESTBOOK_TITLE . '</td>';
    echo "<td class='center bold'>" . AM_XFGUESTBOOK_MESSAGE . '</td>';
    echo "<td class='center bold'>" . AM_XFGUESTBOOK_DATE . '</td>';
    echo "<td class='center bold'>" . AM_XFGUESTBOOK_ACTION . '</td>';
    echo '</tr>';

    if ($totalcount > 0) {
        echo '<form name="msglist" id="list" action="' . $_SERVER['PHP_SELF'] . '" method="POST" style="margin: 0;">';

        foreach ($msg as $onemsg) {
            $all_msg              = [];
            $all_msg['post_time'] = formatTimestamp($onemsg->getVar('post_time'));
            $all_msg['msg_id']    = $onemsg->getVar('msg_id');
            $all_msg['user']      = ($onemsg->getVar('user_id') > 0) ? XoopsUser::getUnameFromId($onemsg->getVar('user_id')) : $onemsg->getVar('uname');
            $all_msg['action']    = "<a href='main.php?op=edit&amp;msg_id=" . $onemsg->getVar('msg_id') . '\'><img src=\'' . $pathIcon16 . "/edit.png'></a>";
            $img_status           = "<img src='" . XOOPS_URL . '/modules/' . $GLOBALS['xoopsModule']->dirname() . '/assets/images/';
            if ($onemsg->getVar('moderate')) {
                $img_status .= "ic15_question.gif'>";
            } else {
                $img_status .= "ic15_ok.gif'>";
            }
            $all_msg['title']   = "<a href='../main.php?op=show_one&msg_id=" . $onemsg->getVar('msg_id') . '\'>' . $onemsg->getVar('title') . '</a>';
            $all_msg['message'] = $onemsg->getVar('message');

            if ($onemsg->getVar('photo')) {
                $all_msg['message'] = '<img src="'
                                      . XOOPS_UPLOAD_URL
                                      . '/'
                                      . $GLOBALS['xoopsModule']->getVar('dirname')
                                      . '/'
                                      . $onemsg->getVar('photo')
                                      . '" align = "left" hspace ="10">'
                                      . $onemsg->getVar('message');
            } else {
                $all_msg['message'] = $onemsg->getVar('message');
            }

            echo '<tr>';
            echo "<td class='center even'><input type='checkbox' name='msg_id[]' id='msg_id[]' value='" . $all_msg['msg_id'] . '\'></td>';
            echo "<td class='center head'><b>" . $img_status . '</b></td>';
            echo "<td class='center even'>" . $all_msg['user'] . '</td>';
            echo "<td class='left odd'>" . $all_msg['title'] . '</td>';
            echo "<td class='left even'>" . $all_msg['message'] . '</td>';
            echo "<td class='odd'>" . $all_msg['post_time'] . '<br>';
            if (in_array($onemsg->getVar('poster_ip'), $badips)) {
                echo "<span style='color: #FF0000; '><b>" . $onemsg->getVar('poster_ip') . '</b></span></td>';
            } else {
                echo $onemsg->getVar('poster_ip') . '</td>';
            }
            echo "<td class='center even'>" . $all_msg['action'] . '</td>';
            echo '</tr>';
            unset($all_msg);
        }
        echo "<tr class='foot'><td><select name='op'>";
        if (1 != $sel_status) {
            echo "<option value='approve'>" . AM_XFGUESTBOOK_PUB . '</option>';
        }
        echo "<option value='delete'>" . _DELETE . '</option>';
        echo "<option value='banish'>" . AM_XFGUESTBOOK_BAN . '</option>';
        echo '</select>&nbsp;</td>';
        echo "<td colspan='6'>" . $GLOBALS['xoopsSecurity']->getTokenHTML() . "<input type='submit' value='" . _GO . '\'>';
        echo '</td></tr>';
        echo '</form>';
    } else {
        echo "<tr ><td colspan ='10' class='head center bold'>" . AM_XFGUESTBOOK_NOMSG . '</td></tr>';
    }
    echo '</table><br>';
    if ($totalcount > $limit) {
        include_once XOOPS_ROOT_PATH . '/class/pagenav.php';
        $pagenav = new XoopsPageNav($totalcount, $limit, $start, 'start', 'sel_status=' . $sel_status . '&sel_order=' . $sel_order);
        echo "<div class='center head'>" . $pagenav->renderNav() . '</div><br>';
    } else {
        echo '';
    }
    echo '<br>';
}

switch ($op) {
    case 'save':
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header('index.php', 2, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        $msgstop = '';
        $msg     = $msgHandler->get($msg_id);
        $del_img = isset($_POST['del_img']) ? (int)$_POST['del_img'] : 0;
        if ($del_img) {
            if (!$xoopsSecurity->check()) {
                redirect_header($_SERVER['PHP_SELF'], XformsConstants::REDIRECT_DELAY_MEDIUM, implode('<br>', $xoopsSecurity->getErrors()));
            }
            $filename = XOOPS_UPLOAD_PATH . '/' . $GLOBALS['xoopsModule']->getVar('dirname') . '/' . $msg->getVar('photo');
            unlink($filename);
            $msg->setVar('photo', '');
        } elseif (!empty($_FILES['photo']['name'])) {
            //@FIXME - $preview_name isn't set here. need to return value from XfguestbookUtil::upload()
            //@FIXME - needs to do $xoopsSecurity referrer check too
            XfguestbookUtil::upload();
            $photo      = str_replace('tmp_', 'msg_', $preview_name);
            $photos_dir = XOOPS_UPLOAD_PATH . '/' . $GLOBALS['xoopsModule']->getVar('dirname') . '/';
            rename($photos_dir . $preview_name, $photos_dir . $photo);
            if ('' !== $msg->getVar('photo')) {
                $filename = XOOPS_UPLOAD_PATH . '/' . $GLOBALS['xoopsModule']->getVar('dirname') . '/' . $msg->getVar('photo');
                unlink($filename);
            }
            $msg->setVar('photo', $photo);
        }
        if (!empty($msgstop)) {
            redirect_header('main.php?op=edit&msg_id=' . $msg_id, 2, $msgstop);
        }
        $uname    = Xmf\Request::getString('uname', '', 'POST');
        $email    = Xmf\Request::getEmail('email', '', 'POST');
        $url      = Xmf\Request::getUrl('url', '', 'POST');
        $title    = Xmf\Request::getString('title', '', 'POST');
        $message  = Xmf\Request::getString('message', '', 'POST');
        $note     = Xmf\Request::getString('note', '', 'POST');
        $gender   = Xmf\Request::getString('gender', '', 'POST');
        $country  = Xmf\Request::getString('country', '', 'POST');
        $other    = Xmf\Request::getString('other', '', 'POST');
        $moderate = Xmf\Request::getInt('moderate', 0, 'POST');
        /*
        $uname    = isset($_POST['uname']) ? $_POST['uname'] : '';
        $email    = isset($_POST['email']) ? $_POST['email'] : '';
        $url      = isset($_POST['url']) ? $_POST['url'] : '';
        $title    = isset($_POST['title']) ? $_POST['title'] : '';
        $message  = isset($_POST['message']) ? $_POST['message'] : '';
        $note     = isset($_POST['note']) ? $_POST['note'] : '';
        $gender   = isset($_POST['gender']) ? $_POST['gender'] : '';
        $country  = isset($_POST['country']) ? $_POST['country'] : '';
        $other    = isset($_POST['other']) ? $_POST['other'] : '';
        $moderate = isset($_POST['moderate']) ? (int)$_POST['moderate'] : 0;
        */
        $msg->setVar('uname', $uname);
        $msg->setVar('email', $email);
        $msg->setVar('url', $url);
        $msg->setVar('title', $title);
        $msg->setVar('message', $message);
        $msg->setVar('note', $note);
        $msg->setVar('gender', $gender);
        if ('' !== $country) {
            $msg->setVar('country', $country);
            $msg->setVar('flagdir', $GLOBALS['xoopsModuleConfig']['flagdir']);
        }
        $msg->setVar('other', $other);
        $msg->setVar('moderate', $moderate);
        if ($msgHandler->insert($msg)) {
            redirect_header('main.php?op=show', 1, AM_XFGUESTBOOK_MSGMOD);
        } else {
            redirect_header('main.php?op=show', 2, AM_XFGUESTBOOK_MSGERROR);
        }
        break;

    case 'edit':
        xoops_cp_header();
        $index_admin = new ModuleAdmin();
        echo $index_admin->addNavigation(basename(__FILE__));
        //xfguestbook_admin_menu(0);
        $msg = $msgHandler->get($msg_id);
        include_once realpath(__DIR__ . '/../include/form_edit.inc.php');
        $msg_form->display();
        include __DIR__ . '/admin_footer.php';
        //xoops_cp_footer();
        break;

    case 'approve':
        approve();
        break;

    case 'delete':
        delete();
        break;

    case 'banish':
        banish();
        break;

    case 'show':
    default:
        xoops_cp_header();
        $index_admin = new ModuleAdmin();
        echo $index_admin->addNavigation(basename(__FILE__));
        //xfguestbook_admin_menu(0);
        show();
        include __DIR__ . '/admin_footer.php';
        //xoops_cp_footer();
        break;
}
