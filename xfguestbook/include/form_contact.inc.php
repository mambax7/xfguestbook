<?php
// $Id: include/form_contact.inc.php,v 1.11 2004/12/02 C. F�lix AKA the Cat
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

include XOOPS_ROOT_PATH."/class/xoopsformloader.php";

$form_contact = new XoopsThemeForm(_MD_XFGB_CONTACTAUTOR, "form_contact", "contact.php");

$title_text = new XoopsFormText(_MD_XFGB_TITLE, "title", 35, 100, $title);
//$title_text->setExtra("readonly = 'readonly'");
$form_contact->addElement($title_text, true);

$name_text = new XoopsFormText(_MD_XFGB_YOURNAME, "name_user", 35, 100, $name_user);
$form_contact->addElement($name_text, true);

$email_text = new XoopsFormText(_MD_XFGB_YOUREMAIL, "email_user", 35, 100, $email_user);
$form_contact->addElement($email_text,true);

if ($option['opt_icon'] == 0) {
$annonce_text = new XoopsFormTextArea(_MD_XFGB_YOURMESSAGE, "message", $message);
} else {
$annonce_text = new XoopsFormDhtmlTextArea(_MD_XFGB_YOURMESSAGE, "message", $message, 10 , 50);
}
$form_contact->addElement($annonce_text, true);
$button_tray = new XoopsFormElementTray('' ,'');
$button_tray->addElement(new XoopsFormCaptcha(), true);
$button_tray->addElement(new XoopsFormButton('', 'preview', _PREVIEW, 'submit'));
$button_tray->addElement(new XoopsFormButton('', 'post', _SEND, 'submit'));
$button_cancel = new XoopsFormButton('', 'cancel', _CANCEL, 'button');
$button_cancel->setExtra("' onclick='javascript:window.close();'");
$button_tray->addElement($button_cancel);

$form_contact->addElement($button_tray);

$form_contact->addElement(new XoopsFormHidden('email_author', $email_author));

$form_contact->display();
?>
