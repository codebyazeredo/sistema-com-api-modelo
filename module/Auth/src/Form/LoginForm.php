<?php

declare(strict_types=1);

namespace Auth\Form;

use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Password;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\StringLength;

final class LoginForm extends Form
{
    public function __construct()
    {
        parent::__construct('login');

        $this->add((new Text('login'))
            ->setLabel('Usuário ou e-mail')
            ->setAttribute('autocomplete', 'username'));

        $this->add((new Password('senha'))
            ->setLabel('Senha')
            ->setAttribute('autocomplete', 'current-password'));

        $this->add(new Csrf('login_csrf'));

        $inputFilter = new InputFilter();
        $inputFilter->add([
            'name' => 'login',
            'required' => true,
            'validators' => [
                ['name' => StringLength::class, 'options' => ['min' => 1, 'max' => 191]],
            ],
        ]);
        $inputFilter->add([
            'name' => 'senha',
            'required' => true,
            'validators' => [
                ['name' => StringLength::class, 'options' => ['min' => 1, 'max' => 255]],
            ],
        ]);
        $this->setInputFilter($inputFilter);
    }
}
