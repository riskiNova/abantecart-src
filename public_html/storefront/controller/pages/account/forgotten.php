<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2016 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}
class ControllerPagesAccountForgotten extends AController {
	private $error = array();
	public $data = array();
	public function main() {
		$this->extensions->hk_InitData($this,__FUNCTION__);
		$this->password();
		$this->extensions->hk_UpdateData($this,__FUNCTION__);
	}

	public function password() {

        $this->extensions->hk_InitData($this,__FUNCTION__);

		if ($this->customer->isLogged()) {
			$this->redirect($this->html->getSecureURL('account/account'));
		}

		$this->document->setTitle( $this->language->get('heading_title') );
		
		$this->loadModel('account/customer');
		
		$cust_details = array();
		if ($this->request->is_POST() && $this->_find_customer('password', $cust_details)) {
			//extra check that we have csutomer details 
			if (!empty($cust_details['email'])) {
				$this->loadLanguage('mail/account_forgotten');
				
				$customer_id = $cust_details['customer_id'];
				$code = genToken(32);
				//save password reset code
				$this->model_account_customer->updateOtherData($customer_id, array('password_reset' => $code));			
				//build reset link 
				$enc = new AEncryption($this->config->get('encryption_key'));
				$rtoken = $enc->encrypt($customer_id.'::'.$code);
				$link = $this->html->getSecureURL('account/forgotten/reset','&rtoken='.$rtoken);
		
				$subject = sprintf($this->language->get('text_subject'), $this->config->get('store_name'));				
				$message  = sprintf($this->language->get('text_greeting'), $this->config->get('store_name')) . "\n\n";
				$message .= $this->language->get('text_password') . "\n\n";
				$message .= $link;
	
				$mail = new AMail( $this->config );
				$mail->setTo($cust_details['email']);
				$mail->setFrom($this->config->get('store_main_email'));
				$mail->setSender($this->config->get('store_name'));
				$mail->setSubject($subject);
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
								
				$this->session->data['success'] = $this->language->get('text_success');
				$this->redirect($this->html->getSecureURL('account/login'));				
			}
		}

      	$this->document->resetBreadcrumbs();

      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('index/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => FALSE
      	 )); 

      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('account/account'),
        	'text'      => $this->language->get('text_account'),
        	'separator' => $this->language->get('text_separator')
      	 ));
		
      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('account/forgotten/password'),
        	'text'      => $this->language->get('text_forgotten'),
        	'separator' => $this->language->get('text_separator')
      	 ));
        
		$this->view->assign('error', $this->error['message'] );
		$this->view->assign('action', $this->html->getSecureURL('account/forgotten') );
        $this->view->assign('back', $this->html->getSecureURL('account/account') );

		$form = new AForm();
        $form->setForm(array( 'form_name' => 'forgottenFrm' ));
        $this->data['form'][ 'form_open' ] = $form->getFieldHtml(
                                                                array(
                                                                       'type' => 'form',
                                                                       'name' => 'forgottenFrm',
                                                                       'action' => $this->html->getSecureURL('account/forgotten/password')));
		
		//verify loginname if non email login used or data encryption is ON
		if( $this->config->get('prevent_email_as_login') || $this->dcrypt->active ){
			$this->data['form']['fields'][ 'loginname' ] = $form->getFieldHtml( array(
                                                                       'type' => 'input',
		                                                               'name' => 'loginname',
		                                                               'value' => $this->request->post['loginname'] ));
		    $this->data['help_text'] =  $this->language->get('text_loginname_email');                                                       
		} else {
		    $this->data['help_text'] =  $this->language->get('text_email');       		
		}
		
		$this->data['form']['fields'][ 'email' ] = $form->getFieldHtml( array(
                                                                       'type' => 'input',
		                                                               'name' => 'email',
		                                                               'value' => $this->request->post['email'] ));
		
		$this->data['form'][ 'continue' ] = $form->getFieldHtml( array(
                                                                       'type' => 'submit',
		                                                               'name' => $this->language->get('button_continue') ));
		$this->data['form'][ 'back' ] = $form->getFieldHtml( array(
                                                                    'type' => 'button',
		                                                            'name' => 'back',
			                                                        'style' => 'button',
		                                                            'text' => $this->language->get('button_back') ));
		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/account/forgotten.tpl');

        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);

	}

	public function reset() {

        $this->extensions->hk_InitData($this,__FUNCTION__);

		$this->loadLanguage('mail/account_forgotten');

		if ($this->customer->isLogged()) {
			$this->redirect($this->html->getSecureURL('account/account'));
		}

		$this->document->setTitle( $this->language->get('heading_title') );
		
		$this->loadModel('account/customer');

		//validate token
		$rtoken = $this->request->get['rtoken'];
		$enc = new AEncryption($this->config->get('encryption_key'));	
		list($customer_id, $code) = explode("::", $enc->decrypt($rtoken));
		$cust_details = $this->model_account_customer->getCustomer($customer_id);		
		if(empty($customer_id) || empty($cust_details['data']['password_reset']) || $cust_details['data']['password_reset'] != $code) {
			$this->error['message'] = $this->language->get('error_reset_token');
			return $this->password();
		}
						
		if ($this->request->is_POST() && $this->_validatePassword()) {
			//extra check that we have csutomer details 
			if (!empty($cust_details['email'])) {
				$this->loadLanguage('mail/account_forgotten');

				$this->model_account_customer->editPassword($cust_details['loginname'], $this->request->post['password']);
				
				$subject = sprintf($this->language->get('text_subject'), $this->config->get('store_name'));				
				$message  = sprintf($this->language->get('text_password_reset'), $this->config->get('store_name')) . "\n\n";
				$mail = new AMail( $this->config );
				$mail->setTo($cust_details['email']);
				$mail->setFrom($this->config->get('store_main_email'));
				$mail->setSender($this->config->get('store_name'));
				$mail->setSubject($subject);
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();

			    //update data and remove password_reset code
				unset($cust_details['data']['password_reset']);	
			    $this->model_account_customer->updateOtherData($customer_id, $cust_details['data']);
								
				$this->session->data['success'] = $this->language->get('text_success');
				$this->redirect($this->html->getSecureURL('account/login'));				
			}
		}

 		$this->loadLanguage('account/password');

      	$this->document->resetBreadcrumbs();

      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('index/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => FALSE
      	 )); 

      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('account/account'),
        	'text'      => $this->language->get('text_account'),
        	'separator' => $this->language->get('text_separator')
      	 ));
		
      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('account/forgotten/password'),
        	'text'      => $this->language->get('text_forgotten'),
        	'separator' => $this->language->get('text_separator')
      	 ));
        
		$this->view->assign('error_warning', $this->error['warning']);
		$this->view->assign('error_password', $this->error['password']);
		$this->view->assign('error_confirm', $this->error['confirm']);

		$form = new AForm();
		$form->setForm(array ('form_name' => 'PasswordFrm'));
		$form_open = $form->getFieldHtml(
				array (
						'type'   => 'form',
						'name'   => 'PasswordFrm',
						'action' => $this->html->getSecureURL('account/forgotten/reset','&rtoken='.$rtoken)));
		$this->view->assign('form_open', $form_open);

		$password = $form->getFieldHtml(
				array (
						'type'     => 'password',
						'name'     => 'password',
						'value'    => '',
						'required' => true));
		$confirm = $form->getFieldHtml(
				array (
						'type'     => 'password',
						'name'     => 'confirm',
						'value'    => '',
						'required' => true));
		$submit = $form->getFieldHtml(
				array (
						'type' => 'submit',
						'name' => $this->language->get('button_continue'),
						'icon' => 'fa fa-check',
				));

		$this->view->assign('password', $password);
		$this->view->assign('submit', $submit);
		$this->view->assign('confirm', $confirm);
		$this->view->assign('back', $this->html->getSecureURL('account/account'));

		$back = $this->html->buildElement(
				array ('type'  => 'button',
				       'name'  => 'back',
				       'text'  => $this->language->get('button_back'),
				       'icon'  => 'fa fa-arrow-left',
				       'style' => 'button'));
		$this->view->assign('button_back', $back);

		$this->processTemplate('pages/account/password_reset.tpl');

        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);

	}

	public function loginname() {

        $this->extensions->hk_InitData($this,__FUNCTION__);

		if ($this->customer->isLogged()) {
			$this->redirect($this->html->getSecureURL('account/account'));
		}

		$this->document->setTitle( $this->language->get('heading_title_loginname') );
		
		$this->loadModel('account/customer');
		
		$cust_detatils = array();
		if ($this->request->is_POST() && $this->_find_customer('loginname', $cust_detatils)) {
			//extra check that we have csutomer details 
			if (!empty($cust_detatils['email'])) {
				$this->loadLanguage('mail/account_forgotten_login');
				
				$subject = sprintf($this->language->get('text_subject'), $this->config->get('store_name'));
				
				$message  = sprintf($this->language->get('text_greeting'), $this->config->get('store_name')) . "\n\n";
				$message .= $this->language->get('text_your_loginname') . "\n\n";
				$message .= $cust_detatils['loginname'];
	
				$mail = new AMail( $this->config );
				$mail->setTo($cust_detatils['email']);
				$mail->setFrom($this->config->get('store_main_email'));
				$mail->setSender($this->config->get('store_name'));
				$mail->setSubject($subject);
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
				
				$this->session->data['success'] = $this->language->get('text_success_loginname');
				$this->redirect($this->html->getSecureURL('account/login'));				
			}
		}

      	$this->document->resetBreadcrumbs();

      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('index/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => FALSE
      	 )); 

      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('account/account'),
        	'text'      => $this->language->get('text_account'),
        	'separator' => $this->language->get('text_separator')
      	 ));
		
      	$this->document->addBreadcrumb( array ( 
        	'href'      => $this->html->getURL('account/forgotten/loginname'),
        	'text'      => $this->language->get('text_forgotten_loginname'),
        	'separator' => $this->language->get('text_separator')
      	 ));
        
		$this->view->assign('error', $this->error['message'] );
		$this->view->assign('action', $this->html->getSecureURL('account/forgotten') );
        $this->view->assign('back', $this->html->getSecureURL('account/account') );


		$form = new AForm();
        $form->setForm(array( 'form_name' => 'forgottenFrm' ));
        $this->data['form'][ 'form_open' ] = $form->getFieldHtml(
                                                                array(
                                                                       'type' => 'form',
                                                                       'name' => 'forgottenFrm',
                                                                       'action' => $this->html->getSecureURL('account/forgotten/loginname')));
		
		$this->data['help_text'] =  $this->language->get('text_lastname_email');                                                       
		$this->data['heading_title'] =  $this->language->get('heading_title_loginname');                                                       
				
		$this->data['form']['fields'][ 'lastname' ] = $form->getFieldHtml( array(
                                                                       'type' => 'input',
		                                                               'name' => 'lastname',
		                                                               'value' => $this->request->post['lastname'] ));
		$this->data['form']['fields'][ 'email' ] = $form->getFieldHtml( array(
                                                                       'type' => 'input',
		                                                               'name' => 'email',
		                                                               'value' => $this->request->post['email'] ));
		
		$this->data['form'][ 'continue' ] = $form->getFieldHtml( array(
                                                                       'type' => 'submit',
		                                                               'name' => $this->language->get('button_continue') ));
		$this->data['form'][ 'back' ] = $form->getFieldHtml( array(
                                                                    'type' => 'button',
		                                                            'name' => 'back',
			                                                        'style' => 'button',
		                                                            'text' => $this->language->get('button_back') ));
		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/account/forgotten.tpl');

        //init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);

	}

	private function _find_customer($mode, &$cust_details ) {
		$email = $this->request->post['email'];
		$loginname = $this->request->post['loginname'];
		$lastname = $this->request->post['lastname'];
		//email is always required 
		if (!isset($email) || empty($email) ) {
			$this->error['message'] = $this->language->get('error_email');
			return FALSE;
		}		
		
		//locate customer based on login name
		if( $this->config->get('prevent_email_as_login') || $this->dcrypt->active ){
			if ( $mode == 'password'){
				if (!empty($loginname)) {
					$cust_details = $this->model_account_customer->getCustomerByLoginnameAndEmail($loginname, $email);	
				} else {
					$this->error['message'] = $this->language->get('error_loginname');
					return FALSE;			
				}
			} else if ( $mode == 'loginname') {
				if (!empty($lastname)) {
					$cust_details = $this->model_account_customer->getCustomerByLastnameAndEmail($lastname, $email);	
				} else {
					$this->error['message'] = $this->language->get('error_lastname');
					return FALSE;			
				}			
			}
		} else {
			//get customer by email
			$cust_details = $this->model_account_customer->getCustomerByEmail($email);
		}
		
		if ( !count($cust_details) ) {
			$this->error['message'] = $this->language->get('error_not_found');
			return FALSE;			
		} else {
			return TRUE;	
		}		
	}
	
	

	private function _validatePassword() {
		$this->loadLanguage('account/password');

		if (mb_strlen($this->request->post['password']) < 4 || mb_strlen($this->request->post['password']) > 20){
			$this->error['password'] = $this->language->get('error_password');
		}

		if ($this->request->post['confirm'] != $this->request->post['password']){
			$this->error['confirm'] = $this->language->get('error_confirm');
		}

		if (!$this->error){
			return true;
		} else{
			$this->error['warning'] = $this->language->get('gen_data_entry_error');
			return false;
		}	
	}
}
