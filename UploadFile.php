<?php
namespace foundationphp;


class UploadFile
{

		public $name = "jerry";
		protected $age = 28;


		public function display()
		{
			echo $this->name . '<br />' . $this->age;
		}

}