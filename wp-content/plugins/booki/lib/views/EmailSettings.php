<?php
class Booki_EmailSettings{
	private $confirmationEmailRepository;
	private $emailSettingRepository;
	const PATH = '/assets/admin/emails/%s.txt';
	public $confirmationEmail;
	public $templateNames;
	public $selectedTemplateName;
	public $selectedTemplateContent;
	public $emailSetting;
	public function __construct(){
		$this->templateNames = Booki_Helper::systemEmails();
		$this->selectedTemplateName = isset($_POST['templateName']) ? $_POST['templateName'] : null;
		if($this->selectedTemplateName){
			$this->emailSettingRepository = new Booki_EmailSettingRepository($this->selectedTemplateName);
		}
		new Booki_EmailSettingController();
		$this->render();
	}
	
	public function render(){
		if($this->emailSettingRepository){
			$this->emailSetting = $this->emailSettingRepository->read();
		}
		if(!$this->emailSetting){
			$this->emailSetting = new Booki_EmailSetting();
		}

		$this->selectedTemplateContent = $this->emailSetting->content;
		if(!trim($this->selectedTemplateContent) && $this->selectedTemplateName){
			$this->selectedTemplateContent = Booki_Helper::readEmailTemplate($this->selectedTemplateName);
		}
	}
}
$_Booki_EmailSettings = new Booki_EmailSettings();
?>
<div class="booki">
	<?php require dirname(__FILE__) .'/partials/restrictedmodewarning.php' ?>
	<div class="booki col-lg-12">
		<h1><?php echo __('Email settings', 'booki') ?></h1>
		<p><?php echo __('The system sends out emails based on specific events.', 'booki') ?> </p>
	</div>
	<div class="booki col-lg-12">
		<div class="booki-content-box">
			<form class="form-horizontal" data-parsley-validate action="<?php echo admin_url() . "admin.php?page=booki/emailsettings.php" ?>" method="post">
				<input type="hidden" name="booki_emailtemplates" />
				<?php if($_Booki_EmailSettings->selectedTemplateName === Booki_EmailType::MASTER_TEMPLATE):?>
				<div class="form-group name">
					<div class="col-lg-8 col-lg-offset-4">
						<div class="bg-warning booki-bg-box">
							<i class="glyphicon glyphicon-sunglasses"></i> <strong>Tip:</strong>
							<?php echo __('This is the master template. It will contain your emails and gives them some structure and styling. Feel free to modify it. There
							are only four tags that are dynamically replaced. %siteName%, %siteUrl%, %siteDescription% and %content%. Everything else can be changed.', 'booki')?>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<div class="form-group name">
					<label class="col-lg-4 control-label" for="templateName">
						<?php echo __('Template', 'booki')?>
					</label>
					<div class="col-lg-8">
						<select id="templateName" name="templateName" class="form-control" onchange="form.submit()">
							<option value=""><?php echo __('Select a template', 'booki') ?></option>
							<?php foreach($_Booki_EmailSettings->templateNames as $key):?>
							<option value="<?php echo $key?>" <?php echo $_Booki_EmailSettings->selectedTemplateName == $key ? 'selected=selected' : '' ?>><?php echo $key ?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>
				<fieldset <?php echo !$_Booki_EmailSettings->selectedTemplateName ? 'disabled' : '' ?>>
					<?php if($_Booki_EmailSettings->selectedTemplateName !== Booki_EmailType::MASTER_TEMPLATE):?>
					<div class="form-group enable">
						<div class="col-lg-8 col-md-offset-4">
						   <label class="checkbox">
								<input 
									name="enable" 
									type="checkbox" 
									<?php echo $_Booki_EmailSettings->emailSetting->enable ? "checked" : ""?> /> 
									<?php echo __('Enable this email', 'booki')?>
							</label>
						</div>
					</div>
					<div class="form-group">
						<div class="col-lg-8 col-lg-offset-4">
							<div class="booki-callout booki-callout-info">
								<h4><?php echo __('Sender name', 'booki') ?></h4>
								<p>
									<?php echo __('The name of company or person to show as the email sending party.', 'booki') ?>
								</p>
							</div>
						</div>
					</div>
					<div class="form-group sendername">
						<label class="col-lg-4 control-label" for="senderName">
							<?php echo __('Sender Name', 'booki')?>
						</label>
						<div class="col-lg-8">
							<input type="text" 
									class="form-control booki_parsley_validated"  
									data-parsley-maxlength="256"
									data-parsley-trigger="change" 
									id="senderName"
									name="senderName" 
									value="<?php echo $_Booki_EmailSettings->emailSetting->senderName?>"/>
						</div>
					</div>
					<div class="form-group">
						<div class="col-lg-8 col-lg-offset-4">
							<div class="booki-callout booki-callout-info">
								<h4><?php echo __('Sender Email', 'booki') ?></h4>
								<p>
									<?php echo __('The email address used as the sender when sending out emails.', 'booki') ?>
								</p>
							</div>
						</div>
					</div>
					<div class="form-group senderemail">
						<label class="col-lg-4 control-label" for="senderEmail">
							<?php echo __('Sender Email', 'booki')?>
						</label>
						<div class="col-lg-8">
							<input type="text" 
								class="form-control booki_parsley_validated"  
								data-parsley-maxlength="256"
								data-parsley-trigger="change" 
								data-parsley-type="email"
								id="senderEmail"
								name="senderEmail" 
								value="<?php echo $_Booki_EmailSettings->emailSetting->senderEmail?>" /> 
						</div>
					</div>
					<div class="form-group">
						<div class="col-lg-8 col-lg-offset-4">
							<div class="booki-callout booki-callout-info">
								<h4><?php echo __('Subject', 'booki') ?></h4>
								<p>
									<?php echo __('If subject is not provided then the selected template name is used instead.', 'booki') ?>
								</p>
							</div>
						</div>
					</div>
					<div class="form-group subject">
						<label class="col-lg-4 control-label" for="subject">
							<?php echo __('Subject', 'booki')?>
						</label>
						<div class="col-lg-8">
							<input type="text" 
								class="form-control booki_parsley_validated"  
								data-parsley-maxlength="256"
								data-parsley-trigger="change" 
								id="subject"
								name="subject" 
								value="<?php echo $_Booki_EmailSettings->emailSetting->subject ?>"/> 
						</div>
					</div>
					<?php endif;?>
					<div class="form-group content">
						<div class="col-lg-8 col-md-offset-4">
							<textarea name="content" class="form-control" rows="15"><?php echo $_Booki_EmailSettings->selectedTemplateContent ?></textarea>
						</div>
					</div>
				</fieldset>
				<div class="form-group">
					<div class="col-lg-8 col-md-offset-4">
						<div class="booki-callout booki-callout-info">
							<h4><?php echo __('Tokens', 'booki') ?></h4>
							<p><?php echo __('The following variables will be replaced by their actual values when the email is sent.', 'booki') ?> </p>
						</div>
						<ul class="booki-token-listing">
							<li><span class="label label-default">%siteName%</span> : <?php echo __('Replaced with the Site Title set in Settings > General.', 'booki') ?></li>
							<li><span class="label label-default">%siteDescription%</span> : <?php echo __('This is replaced with Tagline set in Settings > General. Token used only in the Master Template.', 'booki') ?></li>
							<li><span class="label label-default">%siteUrl%</span> : <?php echo __('This is replaced with the WordPress address (URL). Token used only in the Master Template.', 'booki') ?></li>
							<li><span class="label label-default">%year%</span> : <?php echo __('This is replaced with the current year. Token used only in the Master Template.', 'booki') ?></li>
							<li><span class="label label-default">%content%</span> : <?php echo __('This is replaced with the emails content. Token used only in the Master Template.', 'booki') ?></li>
							<li><span class="label label-default">%customerName%</span> : <?php echo __('Replaced with name of customer, if one exists in customers profile', 'booki') ?></li>
							<li><span class="label label-default">%adminName%</span> : <?php echo __('Replaced with name of admin, if one exists in the admins profile.', 'booki') ?></li>
							<li><span class="label label-default">%orderId%</span> : <?php echo __('Replaced with the order ID', 'booki') ?></li>
							<li><span class="label label-default">%orderDate%</span> : <?php echo __('Replaced with the date the order was made', 'booki') ?></li>
							<li><span class="label label-default">%orderDetails%</span> : <?php echo __('Replaced with the "order details"', 'booki') ?></li>
							<li><span class="label label-default">%orderAddtionalInfo%</span> : <?php echo __('Replaced with the "order additional info"', 'booki') ?></li>
							<li><span class="label label-default">%bookedDateTime%</span> : <?php echo __('The date or datetime of the day booked', 'booki') ?></li>
							<li><span class="label label-default">%bookedDateTimeCost%</span> : <?php echo __('The cost of the booked date', 'booki') ?></li>
							<li><span class="label label-default">%optionalItemName%</span> : <?php echo __('The optional item name', 'booki') ?></li>
							<li><span class="label label-default">%optionalItemCost%</span> : <?php echo __('The optional item cost', 'booki') ?></li>
							<li><span class="label label-default">%quantityElementName%</span> : <?php echo __('The quantity element name', 'booki') ?></li>
							<li><span class="label label-default">%quantityElementCost%</span> : <?php echo __('The quantity element cost', 'booki') ?></li>
							<li><span class="label label-default">%invoicePaymentLink%</span> : <?php echo __('Replaced with a link that redirects the user to paypal for payment of the invoice. This token is valid only on an "invoice" template.', 'booki')?>  </li>
							<li><span class="label label-default">%invoiceDownloadLink%</span> : <?php echo __('Replaced with a link that allows the user to download an invoice in PDF format from your site.', 'booki')?>  </li>
							<li><span class="label label-default">%code%</span> : <?php echo __('Replaced with the coupon code. This token is only valid on a "coupon" template.', 'booki') ?></li>
							<li><span class="label label-default">%orderUrl%</span> : <?php echo __('Replaced with the order url. Currently in use on the Booking Cancel Request template.', 'booki') ?></li>
						</ul>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-8 col-md-offset-4">
						<?php if($_Booki_EmailSettings->emailSetting->id === -1) :?>
						<button class="create btn btn-primary" name="booki_create" <?php echo !$_Booki_EmailSettings->selectedTemplateName ? 'disabled=disabled' : '' ?>><i class="glyphicon glyphicon-ok"></i> Save</button>
						<?php else:?>
						<button class="save btn btn-primary" name="booki_update" value="<?php echo $_Booki_EmailSettings->emailSetting->id?>"><i class="glyphicon glyphicon-ok"></i> Save</button>
						<button class="delete btn btn-danger" name="booki_delete" value="<?php echo $_Booki_EmailSettings->emailSetting->id?>"><i class="glyphicon glyphicon-trash"></i> Reset</button>
						<?php endif;?>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('[data-toggle=tooltip]').tooltip();
	});
</script>