<?php

class Dwd_Logisticlist_Adminhtml_LogisticlistController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("logisticlist/logisticlist")->_addBreadcrumb(Mage::helper("adminhtml")->__("Logisticlist  Manager"),Mage::helper("adminhtml")->__("Logisticlist Manager"));
				return $this;
		}
		public function indexAction() 
		{
				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{
				$brandsId = $this->getRequest()->getParam("id");
				$brandsModel = Mage::getModel("logisticlist/logisticlist")->load($brandsId);
				if ($brandsModel->getId() || $brandsId == 0) {
					Mage::register("logisticlist_data", $brandsModel);
					$this->loadLayout();
					$this->_setActiveMenu("logisticlist/logisticlist");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Logisticlist Manager"), Mage::helper("adminhtml")->__("Logisticlist Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Logisticlist Description"), Mage::helper("adminhtml")->__("Logisticlist Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("logisticlist/adminhtml_logisticlist_edit"))->_addLeft($this->getLayout()->createBlock("logisticlist/adminhtml_logisticlist_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("logisticlist")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

			$id   = $this->getRequest()->getParam("id");
			$model  = Mage::getModel("logisticlist/logisticlist")->load($id);

			$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register("logisticlist_data", $model);

			$this->loadLayout();
			$this->_setActiveMenu("logisticlist/logisticlist");

			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Logisticlist Manager"), Mage::helper("adminhtml")->__("Logisticlist Manager"));
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Logisticlist Description"), Mage::helper("adminhtml")->__("Logisticlist Description"));


			$this->_addContent($this->getLayout()->createBlock("logisticlist/adminhtml_logisticlist_edit"))->_addLeft($this->getLayout()->createBlock("logisticlist/adminhtml_logisticlist_edit_tabs"));

			$this->renderLayout();

		}
		public function saveAction()
		{
			$post_data=$this->getRequest()->getPost();
				if ($post_data) {
					try {
						$optionId = $this->changeAttributeOption($post_data['name'], $post_data['option_id']);
						
						if ($optionId) {
							$post_data['option_id'] = $optionId;
						}
						
						$brandsModel = Mage::getModel("logisticlist/logisticlist")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();
											
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Logisticlist was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setLogisticlistData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $brandsModel->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setLogisticlistData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}
				}
				$this->_redirect("*/*/");
		}
		
		public function deleteAction()
		{
			if( $this->getRequest()->getParam("id") > 0 ) {
				try {
					$brandsModel = Mage::getModel("logisticlist/logisticlist");
					$brandsModel->load($this->getRequest()->getParam("id"));
					$this->deleteAttributeOption('', $brandsModel->getOptionId());
					$brandsModel->delete();
					
					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
					$this->_redirect("*/*/");
				} 
				catch (Exception $e) {
					Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
					$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
				}
			}
			$this->_redirect("*/*/");
		}
		
		public function changeAttributeOption($name, $optionId) 
		{
			if ($optionId) {
				$attr_model = Mage::getModel('catalog/resource_eav_attribute');
				$model_att = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product','logistic_company');
				$attr_model = $attr_model->load($code_att);
				
				$data = array();
				$values = array(
					$optionId => array( 0 => $name)				
				);
				$data['option']['value'] = $values;
				$attr_model->addData($data);
				
				try {
					$attr_model->save();
					$session = Mage::getSingleton('adminhtml/session');
					$session->addSuccess(Mage::helper('catalog')->__('The product attribute has been saved.'));
				} catch (Exception $e) {
					$session->addError($e->getMessage());
					$session->setAttributeData($data);
					return;
				}
			} else {
				$attribute_model        = Mage::getModel('eav/entity_attribute');
				$attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
				$attribute_code         = $attribute_model->getIdByCode('catalog_product', 'logistic_company');
				$attribute              = $attribute_model->load($attribute_code);
				$attribute_table        = $attribute_options_model->setAttribute($attribute);
				$options                = $attribute_options_model->getAllOptions(false);
				

				// code adapted from app/code/core/Mage/Adminhtml/Block/Catalog/Product/Attribute/Edit/Tab/Options.php
				// Its getting all the values of the options on Frontend and Backend. 

				$values = array();
				 //See linked file for getStores() code, it's coming from the same file I just told you about
				
				$stores = Mage::getModel('core/store')
					->getResourceCollection()
					->setLoadDefault(true)
					->load();
					 
				foreach ($options as $option) {
					  
					foreach ($stores as $store) {
						$store_id = $store->getData('store_id');
						  
						$valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
							->setStoreFilter($store_id, false)
							->load();
						
						$justValues = $valuesCollection->getData();
						
						foreach ($justValues as $thevalue) {
							if ($option['value'] == $thevalue['option_id']) {
							  $values[] = new Varien_Object($thevalue);
							}
						}
					}
				}

				$value = array();
				$order = array();
				$delete = array();
				$count = 0;
				for($i=0; $i < count($values);$i++) {  
					$tmp = $values[$i]->getData();
					
					$value['option_' . $i][] = $tmp['value'];
					$order['option_' . $i][] = ''.$i;
					$delete['option_' . $i][] = 1;
					
					$count++;
				}
				// Adding two new options to my select
				$value[] = array('0' => $name, '1' => $name); 
				$order[] =  $count + 1;
				$delete[] =  '';
				// Building the final array of data
				$results = array('value' => $value, 'order' => $order, 'delete' => $delete);
				
				//Setting the option data, in the main data array
				$attribute->setData('option',$results);
				
				//Saving attribute
				$attribute->save();
				
				$options = $attribute->getSource()->getAllOptions(false);
				foreach ($options as $item) {
					if ($item['label'] == $name) {
						return $item['value'];
					}
				}
			}
			
			return false;
		}
		public function deleteAttributeOption($name, $optionId) 
		{
			$attribute_model        = Mage::getModel('eav/entity_attribute');
			$attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
			$attribute_code         = $attribute_model->getIdByCode('catalog_product', 'logistic_company');
			$attribute              = $attribute_model->load($attribute_code);
			$attribute_table        = $attribute_options_model->setAttribute($attribute);
			$options                = $attribute_options_model->getAllOptions(false);
	
			// code adapted from app/code/core/Mage/Adminhtml/Block/Catalog/Product/Attribute/Edit/Tab/Options.php
			// Its getting all the values of the options on Frontend and Backend. 

			$values = array();
			 //See linked file for getStores() code, it's coming from the same file I just told you about
			
			$stores = Mage::getModel('core/store')
				->getResourceCollection()
				->setLoadDefault(true)
				->load();
				 
			foreach ($options as $option) {
				  
				foreach ($stores as $store) {
					$store_id = $store->getData('store_id');
					  
					$valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
						->setStoreFilter($store_id, false)
						->load();
					
					$justValues = $valuesCollection->getData();
					
					foreach ($justValues as $thevalue) {
						if ($option['value'] == $thevalue['option_id']) {
						  $values[] = new Varien_Object($thevalue);
						}
					}
				}
			}

			$value = array();
			$order = array();
			$delete = array();
			$count = 0;
			for($i=0; $i < count($values);$i++) {  
				$tmp = $values[$i]->getData();
				if ($tmp['option_id'] == $optionId) {
					$value[$tmp['option_id']][] = $tmp['value'];
					$order[$tmp['option_id']][] = ''.$i;
					$delete[$tmp['option_id']][] = 1;
				} else {
					$value['option_' . $i][] = $tmp['value'];
					$order['option_' . $i][] = ''.$i;
					$delete['option_' . $i][] = 1;
				}
				
				$count++;
			}
			
			// Building the final array of data
			$results = array('value' => $value, 'order' => $order, 'delete' => $delete);
			
			//Setting the option data, in the main data array
			$attribute->setData('option',$results);
			
			//Saving attribute
			$attribute->save();
			
			return false;
		}
}
