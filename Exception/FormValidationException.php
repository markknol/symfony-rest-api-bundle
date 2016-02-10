<?php

namespace MediaMonks\RestApiBundle\Exception;

use MediaMonks\RestApiBundle\Response\Error;
use MediaMonks\RestApiBundle\Util\StringUtil;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class FormValidationException extends AbstractFieldsException
{
    const FIELD_ROOT = '#';

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * FormValidationException constructor.
     * @param FormInterface $form
     * @param int|string $message
     * @param \Exception|string $code
     */
    public function __construct(
        FormInterface $form,
        $message = Error::MESSAGE_FORM_VALIDATION,
        $code = Error::CODE_FORM_VALIDATION
    ) {
        $this->form    = $form;
        $this->message = $message;
        $this->code    = $code;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->getErrorMessages($this->form);
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    protected function getErrorMessages(FormInterface $form)
    {
        $errors = [];
        foreach ($this->getFormErrorMessages($form) as $error) {
            $errors[] = $error;
        }
        foreach ($this->getFormChildErrorMessages($form) as $error) {
            $errors[] = $error;
        }
        return $errors;
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    protected function getFormErrorMessages(FormInterface $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            if (empty($error)) {
                continue;
            }
            if ($form->isRoot()) {
                $errors[] = $this->toErrorArray($error);
            } else {
                $errors[] = $this->toErrorArray($error, $form);
            }
        }
        return $errors;
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    protected function getFormChildErrorMessages(FormInterface $form)
    {
        $errors = [];
        foreach ($form->all() as $child) {
            if (!empty($child) && !$child->isValid()) {
                foreach ($this->getErrorMessages($child) as $error) {
                    $errors[] = $error;
                }
            }
        }
        return $errors;
    }

    /**
     * @param FormError $error
     * @param FormInterface|null $form
     * @return ErrorField
     */
    protected function toErrorArray(FormError $error, FormInterface $form = null)
    {
        if (is_null($form)) {
            $field = self::FIELD_ROOT;
        } else {
            $field = $form->getName();
        }
        if (!is_null($error->getCause()) && !is_null($error->getCause()->getConstraint())) {
            $code = $this->getErrorCode(StringUtil::classToSnakeCase($error->getCause()->getConstraint()));
        } else {
            $code = $this->getErrorCodeByMessage($error);
        }
        return new ErrorField($field, $code, $error->getMessage());
    }

    /**
     * @param FormError $error
     * @return string
     */
    protected function getErrorCodeByMessage(FormError $error)
    {
        if (stristr($error->getMessage(), Error::FORM_TYPE_CSRF)) {
            return $this->getErrorCode(Error::FORM_TYPE_CSRF);
        }
        return $this->getErrorCode(Error::FORM_TYPE_GENERAL);
    }

    /**
     * @param string $value
     * @return string
     */
    protected function getErrorCode($value)
    {
        return sprintf(Error::CODE_FORM_VALIDATION . '.%s', $value);
    }
}
