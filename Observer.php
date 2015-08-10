<?php
class Dwd_Logisticlist_Model_Observer
{
	public function sendLogisticMail($observer) {
		$order = $observer->getEvent()->getOrder();
		
		if (($order->getStatus() == 'buckaroo_processed' && $order->getState() == 'processing') || ($order->getStatus() == 'ambassadeur_processed' && $order->getState() == 'new')) {
			
			$billingId = $order->getBillingAddress()->getId();
			$address = Mage::getModel('sales/order_address')->load($billingId);
			
			$fullOrder = Mage::getModel('sales/order')->load($order->getId());
			$items = $fullOrder->getAllItems();

			$logisticMails = array();
			foreach($items as $item) {
				$product = Mage::getModel('catalog/product')->load($item->getProductId());
				$optionId = $product->getLogisticCompany();
				Mage::log($item['parent_item_id'], null, 'logistic.log');
				if ($optionId && !$item['parent_item_id'])
					$logisticMails[(int)$optionId][] = $item->getData();
				
			}
			foreach($logisticMails as $companyId => $data) {
				$info = Mage::helper('logisticlist')->getInfoByOptionId($companyId);
				Mage::helper('logisticlist')->sendMail($order, $address, $info['mail'], $info['name'], $data);
			}	
		}
	}
}
