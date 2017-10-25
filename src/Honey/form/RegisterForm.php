<?php

namespace Honey\form;

use Honey\customUI\windows\CustomForm;

use Honey\customUI\elements\Input;
use Honey\customUI\elements\Label;

class RegisterForm implements Form{

	/**
	   * @return CustomForm
	   */
	public function getFormData(){
		$form = new CustomForm("はにー鯖 | アカウント登録");
		$form->addElement(new Label("アカウント登録"));
		$form->addElement(new Input("パスワード :", "", ""));
		$form->addElement(new Input("パスワード(確認用) :", "", ""));
		return $form;
	}

	/**
	   * @param Account $account
	   */
	public function addFormHistory($account){
		$account->addFormHistory($this);
	}
}