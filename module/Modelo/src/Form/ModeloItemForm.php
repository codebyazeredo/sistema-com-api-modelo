<?php

declare(strict_types=1);

namespace Modelo\Form;

use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\StringLength;

final class ModeloItemForm extends Form
{
    public function __construct()
    {
        parent::__construct('modelo_item');

        $this->add((new Text('titulo'))->setLabel('Título'));
        $this->add((new Textarea('descricao'))->setLabel('Descrição'));
        $this->add(new Csrf('modelo_item_csrf'));

        $inputFilter = new InputFilter();
        $inputFilter->add([
            'name' => 'titulo',
            'required' => true,
            'validators' => [
                ['name' => StringLength::class, 'options' => ['min' => 1, 'max' => 191]],
            ],
        ]);
        $inputFilter->add([
            'name' => 'descricao',
            'required' => false,
        ]);
        $this->setInputFilter($inputFilter);
    }
}
