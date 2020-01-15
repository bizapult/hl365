<?php 
	class Booki_CouponsCSVHandler extends Booki_CSVBaseHandler
	{
		public function __construct(){
			$hasFullControl = Booki_PermissionHelper::hasAdministratorPermission();
			$pageIndex = isset($_GET['pageindex']) ? $_GET['pageindex'] : -1;
			$perPage = isset($_GET['perpage']) ? $_GET['perpage'] : null;
			$orderBy = isset($_GET['orderby']) ? $_GET['orderby'] : 'id';
			$order = isset($_GET['order']) ? $_GET['order'] : null;
			$userId = null;
			$couponRepository = new Booki_CouponRepository();
			if(!$hasFullControl){
				$user = wp_get_current_user();
				$userId = $user->ID;
			}
			$result = $couponRepository->readAll($pageIndex, $perPage, $orderBy, $order, $userId);
			
			$columnNames = array(
				'id'
				, 'discount'
				, 'orderMinimum'
				, 'expirationDate'
				, 'code'
				, 'emailedTo'
				, 'projectName'
				, 'projectId'
				, 'status'
				, 'couponType'
			);
			
			$records = array();
			foreach($result as $coupon){
				$status = $coupon->isValid();
				array_push($records, implode(",", array(
					$this->encode($coupon->id)
					, $this->encode($coupon->discount)
					, $this->encode($coupon->orderMinimum)
					, $this->encode($coupon->expirationDate->format('Y-m-d'))
					, $this->encode($coupon->code)
					, $this->encode($coupon->emailedTo)
					, $this->encode($coupon->projectName)
					, $this->encode($coupon->projectId)
					, $this->encode($status ? 'valid' : 'invalid')
					, $this->encode($coupon->couponType === Booki_CouponType::REGULAR ? 'Regular' : 'Super')
				)));
			}
			echo implode(",", $columnNames);
			echo "\n";
			echo implode("\n", $records);
			parent::__construct();
		}
	}
?>
