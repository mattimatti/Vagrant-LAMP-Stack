<?php

class Model_Areas extends RedBean_SimpleModel {

	public function getName() {
		if ($this->isFrench()) {
			return $this->name_fr;
		} else {
			return $this->name_en;
		}

	}

	private function isFrench() {
		if (isset($_SESSION["lang"])) {
			if ($_SESSION["lang"] == "fr") {
				return true;
			}
			return false;
		}
		return false;
	}

}
