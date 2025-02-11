<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2013 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Permet de gérer les fichier ayant une fin de ligne MAC (suite retour client) (http://stackoverflow.com/questions/4541749/fgetcsv-fails-to-read-line-ending-in-mac-formatted-csv-file-any-better-solution)

require 'config.php';

dol_include_once('/bankimport/class/bankimport.class.php');
dol_include_once('/compta/facture/class/facture.class.php');

global $db, $langs;

$langs->load('bills');

ini_set("auto_detect_line_endings", true);

$mesg = "";
$form = new Form($db);
$tpl = 'tpl/bankimport.new.tpl.php';

$import = new BankImport($db);
$format = GETPOST('bankimportmapping', 'inputFormat');


if (GETPOST('compare', 'alphanohtml')) {

    //erics import PDF via docwizon
    if (isset($_FILES) && isset($_FILES['bankimportfile'])) {
        $filename = $_FILES['bankimportfile']['tmp_name'];
        if (is_file($filename)) {
            $dstFile = dirname($_FILES['bankimportfile']['tmp_name']) . '/' . $_FILES['bankimportfile']['name'];
            // print "<p>Analyse de $filename, copy to $dstFile</p>";
            copy($_FILES['bankimportfile']['tmp_name'], $dstFile);

            dol_include_once('/scanconnect/class/scanConnect.class.php');
            // print "<p>include once ok</p>";
            $scanConnect = new scanConnect([
                'db'=>$db,
                'srcFileName'=> $dstFile,
                'ymlFileName' => '',
                'profile' => 'bank',
                'action' => 'default',
                'lang' => 'fra',
                'pluginName' => 'ATM+CAP-REL/BankImport+DocWizOn'
            ]);

            // print "<p>start analyse ...</p>";
            $res =$scanConnect->runAnalyze();
            if ($res) {
                $data = $scanConnect->getResult();
                //Ecrase le fichier "pdf" par le json
                file_put_contents($filename, json_encode($data['json'], JSON_PRETTY_PRINT));
                $jsonFilename = str_replace('.pdf', '.json', $_FILES['bankimportfile']['name']);
                $_FILES['bankimportfile']['name'] = $jsonFilename;
            } else {
                print "<p>ERREUR:</p>";
                print $scanConnect->error;
            }
        }
    }


    $datestart = dol_mktime(0, 0, 0, GETPOST('dsmonth', 'int'), GETPOST('dsday', 'int'), GETPOST('dsyear', 'int'));
    $dateend = dol_mktime(0, 0, 0, GETPOST('demonth', 'int'), GETPOST('deday', 'int'), GETPOST('deyear', 'int'));
    $numreleve = GETPOST('numreleve', 'alphanohtml');
    $hasHeader = GETPOST('hasheader', 'alphanohtml');

    if ($import->analyse(GETPOST('accountid', 'int'), 'bankimportfile', $datestart, $dateend, $numreleve, $hasHeader)) {
        $import->load_transactions(GETPOST('bankimportseparator', 'alphanohtml'), GETPOST('bankimportdateformat', 'alphanohtml'), GETPOST('bankimportmapping', 'alphanohtml'), '"', $format);
        $import->compare_transactions();

        $TTransactions = $import->TFile;

        global $bc;

        $langs->load('bankimport@bankimport');
        $var = true;
        $tpl = 'tpl/bankimport.check.tpl.php';
    }
} elseif (GETPOST('import', 'alphanohtml')) {
    if (
        $import->analyse(
            GETPOST('accountid', 'int'),
            GETPOST('filename', 'alpha'),
            GETPOST('datestart', 'int'),
            GETPOST('dateend', 'int'),
            GETPOST('numreleve', 'alphanohtml'),
            GETPOST('hasheader', 'alphanohtml')
        )
    ) {
        $import->load_transactions(GETPOST('bankimportseparator', 'alpha'), GETPOST('bankimportdateformat', 'alphanohtml'), GETPOST('bankimportmapping', 'alphanohtml'), '"', $format);
        $import->import_data(GETPOST('TLine', 'array'));
        $tpl = 'tpl/bankimport.end.tpl.php';
    }
}

llxHeader('', $langs->trans('TitleBankImport'));

print_fiche_titre($langs->trans("TitleBankImport"));

include($tpl);

llxFooter();
$db->close();
