<?php
// $Id: block/xfguestbook_new.php,v 1.11 2004/12/02 C. Félix AKA the Cat
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

/**
 * @param $options
 * @return array
 */
function b_xfguestbook_show($options)
{
    global $xoopsModule, $xoopsModuleConfig, $xoopsDB;
    if (empty($xoopsModule) || 'xfguestbook' !== $xoopsModule->getVar('dirname')) {
        /** @var XoopsModuleHandler $moduleHandler */
        $moduleHandler = xoops_getHandler('module');
        $module        = $moduleHandler->getByDirname('xfguestbook');
        $configHandler = xoops_getHandler('config');
        $config        = $configHandler->getConfigsByCat(0, $module->getVar('mid'));
    } else {
        $module =& $xoopsModule;
        $config =& $xoopsModuleConfig;
    }

    $block = [];
    if (0 != $options[1]) {
        $block['full_view'] = true;
    } else {
        $block['full_view'] = false;
    }

    $msg_hnd  = xoops_getModuleHandler('msg', 'xfguestbook');
    $criteria = new Criteria('moderate', '0', '=');
    $criteria->setSort('post_time');
    $criteria->setOrder('DESC');
    $criteria->setLimit($options[0]);
    $nbmsg = $msg_hnd->countMsg($criteria);

    $a_item = [];

    if ($nbmsg > 0) {
        $msg = $msg_hnd->getObjects($criteria);
        $ts  = MyTextSanitizer::getInstance();
        foreach ($msg as $onemsg) {
            $msg_id          = $onemsg->getVar('msg_id');
            $a_item['id']    = $msg_id;
            $a_item['title'] = $onemsg->getVar('title');
            if (!XOOPS_USE_MULTIBYTES) {
                $length = strlen($onemsg->getVar('title'));
                if ($length >= $options[1]) {
                    $a_item['title'] = substr($a_item['title'], 0, $options[1] - $length) . '...';
                }
            }
            $a_item['name']   = $onemsg->getVar('uname');
            $a_item['date']   = formatTimestamp($onemsg->getVar('post_time'), 's');
            $block['items'][] = $a_item;
            unset($a_item);
        }
    } else {
        $block['nbmsg'] = 0;
    }

    return $block;
}

/**
 * @param $options
 * @return string
 */
function b_xfguestbook_edit($options)
{
    $form = '' . MB_XFGB_DISP . '&nbsp;';
    $form .= '<input type="text" name="options[]" value="' . $options[0] . '" />&nbsp;' . MB_XFGB_NBMSG . '';
    $form .= '&nbsp;<br>' . MB_XFGB_CHARS . "&nbsp;<input type='text' name='options[]' value='" . $options[1] . '\' />&nbsp;' . MB_XFGB_LENGTH . '';

    return $form;
}