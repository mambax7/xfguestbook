<?php

namespace XoopsModules\Xfguestbook\Form;

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

require_once dirname(__DIR__, 4) . '/class/xoopsform/formselect.php';

/**
 * Class FormSelectCountry
 */
class FormSelectCountry extends \XoopsFormSelect
{
    /**
     * FormSelectCountry constructor.
     * @param      $caption
     * @param      $name
     * @param null $value
     * @param int  $size
     * @param bool $nullopt
     */
    public function __construct($caption, $name, $value = null, $size = 1, $nullopt = false)
    {
        /** @var \XoopsMySQLDatabase $db */
        $db = \XoopsDatabaseFactory::getDatabaseConnection();
        parent::__construct($caption, $name, $value, $size);
        $sql    = 'SELECT country_code, country_name FROM ' . $db->prefix('xfguestbook_country') . ' ORDER BY country_name';
        $result = $db->query($sql);
        if ($nullopt) {
            $this->addOption('', '-');
        }
        $this->addOption('other', MI_XFGUESTBOOK_OTHER);
        while (false !== ($myrow = $db->fetchArray($result))) {
            $this->addOption($myrow['country_code'], $myrow['country_name']);
        }
    }
}
