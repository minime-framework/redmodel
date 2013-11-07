<?php

namespace Minime\RedModel;

use Respect\Validation\Validator;

class ValidationManager
{
    /**
     * Rules for inputs
     * @var array
     */
    protected $rules = [];

    /**
     * Erros of inputs
     * @var array
     */
    protected $errors = [];

    /**
     * Set rules
     * @param array
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * apply validation rules
     * @param  string
     * @return boolean
     */
    public function isValid($input)
    {
        if ($this->rules) {
            # get message
            if (array_key_exists('message', $this->rules)) {
                $message = $this->rules['message'];
                unset($this->rules['message']);
            }

            # instance Respect\Validation\Validator
            $errors = [];
            $search = '<<';
            $validator = Validator::create();
            foreach ($this->rules as $method => $parameters) {
                # When the parameter is a array
                if (is_array($parameters)) {
                    # get message
                    foreach ($parameters as $key => $value) {
                        if (stristr($value, $search)) {
                            $errors[$method] = str_replace($search, '', $parameters[$key]);
                            unset($parameters[$key]);
                        }
                    }
                    $args = $parameters;
                } else {
                    $args = [];
                }

                # methods validate
                $validator = call_user_func_array([$validator, $method], $args);
            }

            # is valid
            try {
                $validator->assert($input);
            }
            # is not valid
            catch(\InvalidArgumentException $e) {
                if (count($errors)) {
                    $this->errors = array_filter($e->findMessages($errors));
                } else {
                    $this->errors = $e->findMessages(array_keys($this->rules));
                }

                return false;
            }

        }

        return true;
    }

    /**
     * Get errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
