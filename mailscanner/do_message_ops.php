<?php

/*
 MailWatch for MailScanner
 Copyright (C) 2003-2011  Steve Freegard (steve@freegard.name)
 Copyright (C) 2011  Garrod Alwood (garrod.alwood@lorodoes.com)
 Copyright (C) 2014-2015  MailWatch Team (https://github.com/orgs/mailwatch/teams/team-stable)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 In addition, as a special exception, the copyright holder gives permission to link the code of this program
 with those files in the PEAR library that are licensed under the PHP License (or with modified versions of those
 files that use the same license as those files), and distribute linked combinations including the two.
 You must obey the GNU General Public License in all respects for all of the code used other than those files in the
 PEAR library that are licensed under the PHP License. If you modify this program, you may extend this exception to
 your version of the program, but you are not obligated to do so.
 If you do not wish to do so, delete this exception statement from your version.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once('./functions.php');
require_once('MDB2.php');
require_once('./filter.inc');

session_start();
require('login.function.php');

$refresh = html_start("Operation Results");

echo '<table border="0" width="100%" class="maildetail">' . "\n";
echo ' <tr>' . "\n";
echo '  <th colspan="3">Spam Learn Results</th>' . "\n";
echo ' </tr>' . "\n";
echo '  <tr>' . "\n";
echo '  <td colspan="3" class="detail">' . "\n";

// Iterate through the POST variables
unset($_POST['SUBMIT']);
if (isset($_POST) && !empty($_POST)) {
    foreach ($_POST as $k => $v) {
        if (preg_match('/^OPT-(.+)$/', $k, $Regs)) {
            $id = $Regs[1];
            $mta = get_conf_var('mta');
            if ($mta == 'postfix') {
                $id = str_replace('_', '.', $id);
            }
        } else {
            continue;
        }
        switch ($v) {
            case 'S':
                $type = 'spam';
                break;
            case 'H':
                $type = 'ham';
                break;
            case 'F':
                $type = 'forget';
                break;
            case 'R':
                $type = 'release';
                break;
            default:
                continue;
        }
        $items = quarantine_list_items($id, RPC_ONLY);
        // Commenting out the below line since it shouldn't make a table for every message
        // echo "<TABLE WIDTH=\"100%\">\n";
        if (count($items) > 0) {
            $num = 0;
            $itemnum = array($num);
            if ($type == 'release') {
                if ($quarantined = quarantine_list_items($id, RPC_ONLY)) {
                    $to = $quarantined[0]['to'];
                }
                echo "<tr><td><a href=\"detail.php?id=$id\">$id</a></td><td>$type</td><td>" . quarantine_release(
                        $quarantined,
                        $itemnum,
                        $to,
                        RPC_ONLY
                    ) . "</td></tr>\n";
            } else {
                echo '<tr><td><a href="detail.php?id=' . $id . '">' . $id . '</a></td><td>' . $type . '</td><td>' . quarantine_learn(
                        $items,
                        $itemnum,
                        $type,
                        RPC_ONLY
                    ) . '</td></tr>' . "\n";
            }
        }
    }
} else {
    echo '<tr><td colspan="3">Message not found in quarantine</td></tr>' . "\n";
}
echo '</table>' . "\n";


echo '  </td>' . "\n";
echo ' </tr>' . "\n";
echo ' </table>' . "\n";

echo '<p><a href="javascript:history.back(1)">Back</a>' . "\n";

//Add footer
html_end();
//Close database connection
dbclose();
