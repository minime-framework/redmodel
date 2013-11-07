<?php

namespace Minime\RedModel;

use Minime\Annotations\Facade as Meta;

class Behaviors
{

    /**
     * Model
     *
     * @var Minime\RedModel\Model
     */
    protected $Model;

    public function __construct(\Minime\RedModel\Model $Model)
    {
        $this->Model = $Model;
    }

    /**
     * Updates "created_at" and "update_at" fields with their respective timestamps.
     *
     * @return void
     */
    public function updateTimestamps()
    {

        $annotations = Meta::getClassAnnotations($this->Model)->useNamespace('redmodel');
        if ($annotations->has('timestamps')) {
            $time = $this->getFreshTimestamp();

            if ($this->Model->bean()->getMeta('tainted')) {
                if (!$this->Model->bean()->id) {
                    $this->Model->bean()->created_at = $time;
                }
                $this->Model->bean()->updated_at = $time;
            }
        }
    }

    /**
     * @todo Add database agnostic logic to check unique constrainsts of values
     */
    public function validateUniqueConstraints()
    {
        return [];
    }

    public function validateFields()
    {
        $validator = new ValidationManager;
        $errors = [];
        foreach ($this->Model->getColumns() as $column) {
            $rules = Meta::getPropertyAnnotations($this->Model, $column)->getAsArray('redmodel.validate');
            $validator->setRules($rules);
            if (FALSE === $validator->isValid($this->Model->$column())) {
                $errors[$column] = $validator->getErrors();
            }
        }

       return $errors;
    }

    public function validate()
    {
        return new ErrorsBag($this->validateUniqueConstraints() + $this->validateFields());
    }

    /**
     * Get a fresh timestamp for the model.
     *
     * @return \DateTime
     */
    protected function getFreshTimestamp()
    {
        return new \DateTime;
    }
}
