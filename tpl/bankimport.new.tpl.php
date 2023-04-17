
<h2>Import de votre extrait de compte PDF (via DocWizOn)</h2>

<form method="post" enctype="multipart/form-data" name="bankimportPDF">
	<input type="hidden" name="inputFormat" id="inputFormat" value="json">
	<input type="hidden" name="bankimportdateformat" id="bankimportdateformat" value="Y-m-d">
	<table class="border" width="100%">
		<tr>
			<td width="200"><label class="fieldrequired" for="selectaccountidPDF"><?php echo $langs->trans("BankAccount") ?></label></td>
			<td><?php echo $form->select_comptes( ($import->account) ? $import->account->id : -1,'accountid',0,'courant <> 2',1) ?></td>
			<td width="200"><label class="fieldrequired" for="bankimportfilePDF"><?php echo $langs->trans("BankImportFile") ?></label></td>
			<td><input type="file" id="bankimportfile" name="bankimportfile" accept=".pdf" /></td>
			<!-- <td><?php echo $form->textwithpicto(
				'<label for="BankImportDateFormatPDF" >' . $langs->trans("BankImportDateFormatPDF") . '</label>',
				$langs->trans("BankImportDateFormatHelp")
			); ?>
			</td>
			<td><input type="text" id="bankimportdateformat" name="bankimportdateformat" value="<?php echo $conf->global->BANKIMPORT_DATE_FORMAT; ?>" size="12" /></td> -->
		</tr>
	</table>
	<br />

	<div class="center">
		<input type="submit" class="button" name="compare" value="<?php echo dol_escape_htmltag($langs->trans("BankCompareTransactions")) ?>">
	</div>
</form>


<script type="text/javascript">
	$(function() { 
		$('form[name=bankimport]').submit(function(event) {
			var TError = new Array;
			if ($('#selectaccountid').val() == -1) 	TError.push("<?php echo $langs->transnoentitiesnoconv('bankImportFieldBankAccountRequired'); ?>");
			if (!$('#numreleve').val().trim()) 		TError.push("<?php echo $langs->transnoentitiesnoconv('bankImportFieldNumReleveRequired'); ?>");
			if ($('#bankimportfile').val() == '') 	TError.push("<?php echo $langs->transnoentitiesnoconv('bankImportFieldBankImportFileRequired'); ?>");

			if (TError.length > 0)
			{
				for (var i = 0; i < TError.length; i++)
				{
					$.jnotify(TError[i], 'error', true);
				}

				return false;
			}
			return true;
		});

		$('form[name=bankimportPDF]').submit(function(event) {
			var TError = new Array;
			if ($('#selectaccountidPDF').val() == -1) 	TError.push("<?php echo $langs->transnoentitiesnoconv('bankImportFieldBankAccountRequired'); ?>");
			if ($('#bankimportfilePDF').val() == '') 	TError.push("<?php echo $langs->transnoentitiesnoconv('bankImportFieldBankImportFileRequired'); ?>");
			
			if (TError.length > 0)
			{
				for (var i = 0; i < TError.length; i++)
				{
					$.jnotify(TError[i], 'error', true);
				}
				
				return false;
			}
			return true;
		});

	});
</script>
