<?php

namespace Honey\form;

interface Form{

	public function getFormData();

	public function addFormHistory($account);
}