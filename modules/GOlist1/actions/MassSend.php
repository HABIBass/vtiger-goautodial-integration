<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class GOlist1_MassSend_Action extends Vtiger_Mass_Action {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$recordIds = $this->getRecordsListFromRequest($request);

		foreach($recordIds as $recordId) {
			if(Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
				$this->sendToGOautoDial($recordModel);
			}else{ 
                            $permission =   'No'; 
                        } 
		}
                
		die();

                if($permission==='No'){ 
                    throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));  
                } 

		$cvId = $request->get('viewname');
		$response = new Vtiger_Response();
		$response->setResult(array('viewname'=>$cvId, 'module'=>$moduleName));
		$response->emit();
	}

	private function sendToGOautoDial($entity){
		include_once '/var/www/html/mySOAP/lib/nusoap.php' ;
				
		$url = "http://128.199.48.154/mySOAP/index.php?wsdl";				 
		$client = new nusoap_client($url);				 
		$err = $client->getError();				 
		if ($err) {
		    echo '<p><b>Error: ' . $err . '</b></p>';
		}
		
		var_dump($entity->entity->column_fields);

		$data = $entity->entity->column_fields;
		unset($data['assigned_user_id']);
		unset($data['CreatedTime']);
		unset($data['ModifiedTime']);
		unset($data['createdtime']);
		unset($data['modifiedtime']);
		unset($data['modifiedby']);


		$return = $client->call('create', array('entity_name'=>'list',"data" => $data));
		 
		$return = json_decode($return,1);
		
		if ($return['status'] == 'success')
			return true;
		else {
			var_dump($return);
			die("Connection to goautodial failed");
		}
	}
}
