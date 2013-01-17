<form method="post" action="<?=BASE?>&amp;C=addons_extensions&amp;M=save_extension_settings">
	<div>
		<input type="hidden" name="file" value="<?=strtolower($name)?>" />
		<input type="hidden" name="XID" value="<?=XID_SECURE_HASH?>" />
	</div>
	<table cellpadding="0" cellspacing="0" style="width:100%" class="mainTable ds-extension-settings">
		<tbody>
			<tr>
				<td>
					<?=lang('url_append')?>
				</td>
				<td>
					<input type="text" value="<?=$current['delimiter'];?>" name="delimiter" />
				</td>
				<td>
					<input type="text" value="<?=$current['append_string'];?>" name="append_string" />
				</td>
			</tr>
		</tbody>
	</table>
	<input type="submit" class="submit" value="<?=lang('save')?>" />
</form>