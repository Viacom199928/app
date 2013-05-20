<?php

abstract class CollectionField extends InputField
{
	public function getName() {
		return parent::getName() . '[]';
	}

	public function getId($index = null) {
		return parent::getId() . $index;
	}

	public function getValue($index = null) {
		$values = parent::getValue();
		if (!is_array($values)) {
			$values = [];
		}
		return isset($values[$index]) ? $values[$index] : $values;
	}

	protected function renderInternal($className, $htmlAttributes = [], $data = [], $index = null) {
		$out = '';
		if (isset($index)) {
			$out .= parent::renderInternal($className, $htmlAttributes, $data, $index);
		} else {
			$values = $this->getValue();
			foreach ($values as $index => $value) {
				$out .= parent::renderInternal($className, $htmlAttributes, $data, $index);
			}
		}
		return $out;
	}

	public function validate($value, $formValues) {
		$isValid = true;

		if (isset($this->validator)){
			if( $this->validator instanceof WikiaValidatorDependent ) {
				$this->validator->setFormData($formValues);
			}

			if (!$this->validator->isValid($value)) {
				$validationError = $this->validator->getError();
				foreach ($validationError as $key => $error) {
					if (is_array($error)) {
						// maybe in future we should handle many errors from one validator,
						// but actually we don't need  this feature
						$error = array_shift(array_values($error));
					}
					if (!empty($error)) {
						$validationError[$key] = $error->getMsg();
						$isValid = false;
					}
				}
				$this->setProperty(self::PROPERTY_ERROR_MESSAGE, $validationError);
			}
		}
		return $isValid;
	}

	public function renderErrorMessage($index) {
		$errorMessage = $this->getProperty(self::PROPERTY_ERROR_MESSAGE);

		if (!empty($errorMessage[$index])) {
			return $this->renderView('BaseField', 'errorMessage', ['errorMessage' => $errorMessage[$index]]);
		}
	}
}