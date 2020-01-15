<?php
class Booki_MiniCartTmpl{
	public $data;
	public $url;
	public $editable;
	public $orderHistoryUrl = null;
	public $displayTimezone = true;
	public $projectId = null;
	public function __construct(){
		
		add_action('booki_new_item_in_cart', array($this, 'newItemAdded'), 10);
		
		new Booki_MiniCartController();
		
		$builder = new Booki_MiniCartBuilder();
		$this->data = $builder->data;

		$this->editable = true;
		$this->url = Booki_Helper::appendReferrer(Booki_Helper::getUrl(Booki_PageNames::CART));
		if($this->data->globalSettings->useDashboardHistoryPage){
			$this->orderHistoryUrl = admin_url() . 'admin.php?page=booki/userhistory.php';
		}else{
			$this->orderHistoryUrl = Booki_Helper::getUrl(Booki_PageNames::HISTORY_PAGE);
		}
		
		
		$this->displayTimezone = $this->data->globalSettings->displayTimezone();
	}
	
	public function newItemAdded(){}
}
?>