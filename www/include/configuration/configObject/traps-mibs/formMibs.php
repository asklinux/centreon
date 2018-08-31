<?php
/*
 * Copyright 2005-2018 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

if (!isset($centreon)) {
    exit();
}

/*
 * Debug Flag
 */
$debug = 0;
    $max_characters = 20000;

/*
 * Database retrieve information for Manufacturer
 */

function myDecodeMib($arg)
{
    $arg = html_entity_decode($arg, ENT_QUOTES, "UTF-8");
    return($arg);
}

/*
 * Init Formulary
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
$form->addElement('header', 'title', _("Import SNMP traps from MIB file"));

/*
 * Manufacturer information
 */
$attrManufacturer= array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_manufacturer&action=list',
    'multiple' => false,
    'linkedObject' => 'centreonManufacturer'
);
$attrManufacturer1 = array_merge(
    $attrManufacturer,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_manufacturer&action=defaultValues&target=traps&field=manufacturer_id&id=')
);
$form->addElement('select2', 'mnftr', _("Vendor Name"), array(), $attrManufacturer1);

$form->addElement('file', 'filename', _("File (.mib)"));

/*
 * Formulary Rules
 */
$form->applyFilter('__ALL__', 'myTrim');
$form->addRule('mnftr', _("Compulsory Name"), 'required');
$form->addRule('filename', _("Compulsory Name"), 'required');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);



$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"');
# prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

/*
 * Just watch a Command information
 */
$subA = $form->addElement('submit', 'submit', _("Import"), array("class" => "btc bt_success"));
$form->addElement('header', 'status', _("Status"));
$valid = false;
$msg = null;
$stdout = null;
if ($form->validate()) {
    $ret = $form->getSubmitValues();

    $fileObj = $form->getElement('filename');

    if ($fileObj->isUploadedFile()) {
        /*
		 * Upload File
		 */
        $values = $fileObj->getValue();
        $msg .= str_replace("\n", "<br />", $stdout);
        $msg .= "<br />Moving traps in DataBase...";

        if ($debug) {
            print("@CENTREONTRAPD_BINDIR@/centFillTrapDB -f '".$values["tmp_name"]."' -m ".htmlentities($ret["mnftr"], ENT_QUOTES, "UTF-8")." --severity=info 2>&1");
        }

        $stdout = shell_exec("@CENTREONTRAPD_BINDIR@/centFillTrapDB -f '".$values["tmp_name"]."' -m ".htmlentities($ret["mnftr"], ENT_QUOTES, "UTF-8")." --severity=info 2>&1");
        unlink($values['tmp_name']);
        $msg .= "<br />".str_replace("\n", "<br />", $stdout);
        $msg .= "<br />Generate Traps configuration files from Monitoring Engine configuration form!";
        if ($msg) {
            if (strlen($msg) > $max_characters) {
                $msg = substr($msg, 0, $max_characters)."...".sprintf(_("Message truncated (exceeded %s characters)"), $max_characters);
            }
            $tpl->assign('msg', $msg);
        }
    }
    $valid = true;
}

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("formMibs.ihtml");
