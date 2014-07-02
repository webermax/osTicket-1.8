<?php
/*********************************************************************
    staff.php

    Evertything about staff members.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');

$staff=null;
if($_REQUEST['id'] && !($staff=Staff::lookup($_REQUEST['id'])))
    $errors['err']=__('Unknown or invalid agent.');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$staff){
                $errors['err']=__('Unknown or invalid agent.');
            }elseif($staff->update($_POST,$errors)){
                $msg=__('Agent updated successfully');
            }elseif(!$errors['err']){
                $errors['err']=__('Unable to update agent. Correct any error(s) below and try again!');
            }
            break;
        case 'create':
            if(($id=Staff::create($_POST,$errors))){
                $msg=sprintf(__('%s added successfully'),Format::htmlchars($_POST['firstname']));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=__('Unable to add agent. Correct any error(s) below and try again.');
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = __('You must select at least one agent.');
            } elseif(in_array($thisstaff->getId(),$_POST['ids'])) {
                $errors['err'] = __('You can not disable/delete yourself - you could be the only admin!');
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.STAFF_TABLE.' SET isactive=1 '
                            .' WHERE staff_id IN ('.implode(',', db_input($_POST['ids'])).')';

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = _N('Selected agent activated', 'Selected agents activated', $count);
                            else
                                $warn = sprintf(__('%1$d of %2$d selected agents activated'), $num, $count);
                        } else {
                            $errors['err'] = __('Unable to activate selected agents');
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.STAFF_TABLE.' SET isactive=0 '
                            .' WHERE staff_id IN ('.implode(',', db_input($_POST['ids'])).') AND staff_id!='.db_input($thisstaff->getId());

                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = _N('Selected agent disabled', 'Selected agents disabled', $count);
                            else
                                $warn = sprintf(__('%1$d of %2$d selected agents disabled'), $num, $count);
                        } else {
                            $errors['err'] = __('Unable to disable selected agents');
                        }
                        break;
                    case 'delete':
                        foreach($_POST['ids'] as $k=>$v) {
                            if($v!=$thisstaff->getId() && ($s=Staff::lookup($v)) && $s->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = _N('Selected agent deleted successfully', 'Selected agents deleted successfully', $count);
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d selected agents deleted'), $i, $count);
                        elseif(!$errors['err'])
                            $errors['err'] = __('Unable to delete selected agents.');
                        break;
                    default:
                        $errors['err'] = __('Unknown action. Get technical help!');
                }

            }
            break;
        default:
            $errors['err']=__('Unknown action');
            break;
    }
}

$page='staffmembers.inc.php';
$tip_namespace = 'staff.agent';
if($staff || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='staff.inc.php';
}

$nav->setTabActive('staff');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
