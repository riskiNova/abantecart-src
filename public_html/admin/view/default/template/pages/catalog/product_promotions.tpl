<?php if ($error) {
	$error = (array)$error;
	foreach ($error as $text) {
		?>
		<div class="warning alert alert-error alert-danger"><?php echo $text; ?></div>

	<?php }
} ?>
<?php if ($success) { ?>
	<div class="success alert alert-success"><?php echo $success; ?></div>
<?php } ?>

<?php echo $summary_form; ?>
<?php echo $product_tabs ?>

<div class="tab-content">
	<div class="panel-heading">
		<div class="pull-right">
			<div class="btn-group mr10 toolbar">
				<?php if (!empty ($help_url)) { ?>
					<a class="btn btn-white tooltips" href="<?php echo $help_url; ?>" target="new" data-toggle="tooltip"
					   title="" data-original-title="Help">
						<i class="fa fa-question-circle"></i>
					</a>
				<?php } ?>
			</div>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="panel-btns">
				<a class="minimize" href="">−</a>
			</div>
			<h4 class="panel-title"><?php echo $tab_discount; ?></h4>
		</div>
		<?php echo $form['form_open']; ?>
		<div class="panel-body panel-body-nopadding">
			<table class="table table-striped">
				<thead>
				<tr>
					<td class="left"><?php echo $entry_customer_group; ?></td>
					<td class="left"><?php echo $entry_quantity; ?></td>
					<td class="left"><?php echo $entry_priority; ?></td>
					<td class="left"><?php echo $entry_price; ?></td>
					<td class="left"><?php echo $entry_date_start; ?></td>
					<td class="left"><?php echo $entry_date_end; ?></td>
					<td></td>
				</tr>
				</thead>
				<?php $discount_row = 0; ?>
				<?php foreach ($product_discounts as $product_discount) { ?>
					<tbody id="discount_row<?php echo $discount_row; ?>">
					<tr>
						<td class="left"><?php echo $customer_groups[$product_discount['customer_group_id']]; ?></td>
						<td class="left"><?php echo $product_discount['quantity']; ?></td>
						<td class="left"><?php echo $product_discount['priority']; ?></td>
						<td class="left"><?php echo moneyDisplayFormat($product_discount['price']); ?></td>
						<td class="left"><?php echo $product_discount['date_start']; ?></td>
						<td class="left"><?php echo $product_discount['date_end']; ?></td>
						<td class="left">
							<a title="<?php echo $button_edit->text; ?>"
							   href="<?php echo str_replace('%ID%', $product_discount['product_discount_id'], $update_discount); ?>"
							   class="btn" data-target="#discount_modal" data-toggle="modal"><i
										class="fa fa-edit fa-lg"></i></a>

							<a title="<?php echo $button_remove->text; ?>" data-confirmation="delete"
							   href="<?php echo str_replace('%ID%', $product_discount['product_discount_id'], $delete_discount); ?>"
							   class="btn"><i class="fa fa-trash-o fa-lg"></i></a>


						</td>
					</tr>
					</tbody>
					<?php $discount_row++; ?>
				<?php } ?>
			</table>
		</div>
		<div class="panel-footer">
			<div class="row pull-right">
				<div class="col-sm-6 col-sm-offset-0">
					<a href="<?php echo $button_add_discount->href; ?>" data-target="#discount_modal"
					   data-toggle="modal">
						<button class="btn btn-primary">
							<i class="fa fa-plus"></i> <?php echo $button_add_discount->text; ?>
						</button>
					</a>
				</div>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="panel-btns">
				<a class="minimize" href="">−</a>
			</div>
			<h4 class="panel-title"><?php echo $tab_special; ?></h4>
		</div>
		<div class="panel-body panel-body-nopadding">

			<table class="table">
				<thead>
				<tr>
					<td class="left"><?php echo $entry_customer_group; ?></td>
					<td class="left"><?php echo $entry_priority; ?></td>
					<td class="left"><?php echo $entry_price; ?></td>
					<td class="left"><?php echo $entry_date_start; ?></td>
					<td class="left"><?php echo $entry_date_end; ?></td>
					<td></td>
				</tr>
				</thead>
				<?php $discount_row = 0; ?>
				<?php foreach ($product_specials as $item) { ?>
					<tbody>
					<tr>
						<td class="left col-sm-6"><?php echo $customer_groups[$item['customer_group_id']]; ?></td>
						<td class="left"><?php echo $item['priority']; ?></td>
						<td class="left"><?php echo moneyDisplayFormat($item['price']); ?></td>
						<td class="left"><?php echo $item['date_start']; ?></td>
						<td class="left"><?php echo $item['date_end']; ?></td>
						<td class="left">
							<a title="<?php echo $button_edit->text; ?>"
							   href="<?php echo str_replace('%ID%', $item['product_special_id'], $update_special); ?>"
							   class="btn"
							   data-target="#discount_modal" data-toggle="modal"><i class="fa fa-edit fa-lg"></i></a>

							<a title="<?php echo $button_remove->text; ?>"
							   class="btn" data-confirmation="delete"
							   href="<?php echo str_replace('%ID%', $item['product_special_id'], $delete_special); ?>"
									><i class="fa fa-trash-o fa-lg"></i></a>


						</td>
					</tr>
					</tbody>
					<?php $discount_row++; ?>
				<?php } ?>
			</table>


		</div>
		<div class="panel-footer">
			<div class="row pull-right">
				<div class="col-sm-6 col-sm-offset-0">
					<a href="<?php echo $button_add_special->href; ?>" data-target="#discount_modal"
					   data-toggle="modal">
						<button class="btn btn-primary">
							<i class="fa fa-plus"></i> <?php echo $button_add_special->text; ?>
						</button>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<?php echo $this->html->buildElement(
		array('type' => 'modal',
				'id' => 'discount_modal',
				'name' => 'discount_modal',
				'modal_type' => 'lg',
				'data_source' => 'remote'));
?>


