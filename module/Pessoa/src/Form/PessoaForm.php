<?php

declare(strict_types=1);

namespace Pessoa\Form;

use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\StringLength;

/**
 * A foto (arquivo) NÃO é validada aqui — a validação real de conteúdo
 * (tamanho, MIME de verdade) fica em Pessoa\Service\PessoaService::salvarFoto(),
 * reaproveitada também pela API. O elemento File aqui existe só para render.
 */
final class PessoaForm extends Form
{
    public function __construct()
    {
        parent::__construct('pessoa');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add((new Text('nome'))->setLabel('Nome'));
        $this->add((new Text('documento'))->setLabel('CPF/Documento'));
        $this->add((new Email('email'))->setLabel('E-mail'));
        $this->add((new Text('telefone'))->setLabel('Telefone'));
        $this->add((new File('foto'))->setLabel('Foto'));
        $this->add(new Csrf('pessoa_csrf'));

        $inputFilter = new InputFilter();
        $inputFilter->add([
            'name' => 'nome',
            'required' => true,
            'validators' => [
                ['name' => StringLength::class, 'options' => ['min' => 1, 'max' => 191]],
            ],
        ]);
        $inputFilter->add([
            'name' => 'documento',
            'required' => true,
            'validators' => [
                ['name' => StringLength::class, 'options' => ['min' => 1, 'max' => 20]],
            ],
        ]);
        $inputFilter->add(['name' => 'email', 'required' => false]);
        $inputFilter->add(['name' => 'telefone', 'required' => false]);
        $this->setInputFilter($inputFilter);
    }
}
