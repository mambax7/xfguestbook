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

use Xmf\Module\Admin;

require_once __DIR__ . '/admin_header.php';
require_once dirname(__DIR__) . '/include/cp_functions.php';
require_once dirname(__DIR__, 3) . '/class/xoopslists.php';

$msgHandler = $helper->getHandler('Message');
require_once __DIR__ . '/admin_header.php';

$op = \Xmf\Request::getCmd('op', 'show');

switch ($op) {
    case 'delete':
        if (\Xmf\Request::hasVar('imglist_id', 'POST')) {
            $img_count = count($_POST['imglist_id']);
            for ($i = 0; $i < $img_count; $i++) {
                unlink(XOOPS_UPLOAD_PATH . '/' . $xoopsModule->getVar('dirname') . '/' . $_POST['imglist_id'][$i]);
            }
            redirect_header('img_manager.php', 1, $i . AM_XFGUESTBOOK_IMG_DELETED);
        }
        break;
    case 'show':
    default:
        xoops_cp_header();
        $adminObject = Admin::getInstance();
        $adminObject->displayNavigation(basename(__FILE__));
        $cpt1    = $cpt2 = 0;
        $all_img = \XoopsLists::getImgListAsArray(XOOPS_UPLOAD_PATH . '/' . $xoopsModule->getVar('dirname') . '/');
        echo AM_XFGUESTBOOK_ORPHEAN_DSC . '<br>';
        $msg_img = $msgHandler->getMsgImg();
        if (count($all_img) > count($msg_img)) {
            echo "<form action='img_manager.php' method='post' name='imglist' id='imglist'><input type='hidden' name='op' value='delete'>";
            echo $GLOBALS['xoopsSecurity']->getTokenHTML();
            echo "<table width='100%' border='0' cellspacing='1' cellpadding='4' class='outer'><tr>
            <th align='center'><input type='checkbox' name='imglist_checkall' id='imglist_checkall' ' checked onclick='xoopsCheckAll(\"imglist\", \"imglist_checkall\");'></th>
            <th align='center'>" . AM_XFGUESTBOOK_IMG . "</th>
            <th align='center'>" . AM_XFGUESTBOOK_IMG_FILE . '</th></tr>';
            foreach ($all_img as $one_img) {
                if (0 === count($msg_img) || count($msg_img) > 0 && !in_array($one_img, $msg_img)) {
                    (0 == $cpt1 % 2) ? $class = 'even' : $class = 'odd';
                    echo "<tr class='$class'><td align='center'><input type='checkbox' name='imglist_id[]' id='imglist_id[]' value='" . $one_img . '\' checked=\'checked\'></td>';
                    echo '<td><img src = "' . XOOPS_UPLOAD_URL . '/' . $xoopsModule->getVar('dirname') . '/' . $one_img . '">';
                    echo '</td>';
                    echo '<td>' . $one_img . '</td></tr>';
                    $cpt1++;
                } else {
                    $cpt2++;
                }
            }
            echo "<tr class='foot'><td>&nbsp;</td>";
            echo "<td ><input type='hidden' name='op' value='delete'><input type='submit' value='" . _DELETE . '\'></td>';
            echo '<td>&nbsp;' . $cpt1 . AM_XFGUESTBOOK_IMG_ORPHEAN . '</td></tr>';
            echo '</table>';
            echo "</form>\n";
        } else {
            echo '<br>' . AM_XFGUESTBOOK_NO_ORPHEAN;
        }
        require_once __DIR__ . '/admin_footer.php';
        //xoops_cp_footer();
        break;
}
