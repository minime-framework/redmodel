<?php

namespace Minime\RedModel;

use Respect\Validation\Validator;

class Validation
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
  * Saves the messages application 
  * @var array
  */
  protected $messages = []; 

  /**
   * Set rules
   * @param array
   */
  public function setRules(array $rules)
  {
    $this->rules = array_map([$this, 'filterRules'], $rules);

    return $this;
  }

  /**
   * apply validation rules
   * @param  string
   * @return boolean
   */
  public function isValid($input)
  {
    # is valid
    try {
      return $this->createInstanceWithRules()->assert($input);
    }
    # is not valid
    catch(\InvalidArgumentException $e) {           
      $this->errors = array_filter(
            $e->findMessages($this->messages) +
            $e->findMessages(array_keys($this->rules))
          );
    }

    return false;      
  }

  /**
   * Get errors
   * @return array
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * Creates an instance with the rules
   * @return object Respect\Validation\Validator
   */
  private function createInstanceWithRules()
  {
    # instance Respect\Validation\Validator
    $validator = Validator::create();

    foreach ($this->rules as $functions => &$params) {      
      # if multiple methods
      if (strstr($functions, '.')) {
        $methods = explode('.', $functions);
        $functions = array_pop($methods);
        foreach ($methods as $method) {
          call_user_func_array([$validator, $method], []);
        }
      }
      # set messages
      if (isset($params['message'])) {
        $this->messages[$functions] = $params['message'];
        unset($params['message']);
      }
      # set new key from message of validation
      $this->rules[$functions] = $params;
      # filter params
      call_user_func_array([$validator, $functions], $params);
    }

    return $validator;
  }

  /**
   * filter rules
   * @param  string
   * @return array
   */
  private function filterRules($value)
  {    
    $params = [];
    $pattern = '/[{](.*)[}]/';
    # set message
    if (preg_match($pattern, $value, $matches)) {    
      $params['message'] = $matches[1];
      $value = str_replace($matches[0], '', $value);
    }
    # filter args    
    if ($value && strstr($value, ',')) {
      $params += array_filter(array_map('trim', explode(',', $value)));
    } elseif($value) {
      $params[] = $value;
    }

    return $params;
  }

}
