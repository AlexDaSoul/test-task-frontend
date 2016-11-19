<?php

namespace AppBundle\Form\Handler;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\ResolvedFormType;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class FormOptionsRetriever
 */
class FormOptionsRetriever
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Gets form definitions.
     *
     * @param Form $form
     *
     * @return array
     */
    public function getDefinitions(Form $form)
    {
        return $form->count() ? $this->getComplexFieldDefinitions($form) : $this->getSimpleFieldOptions($form);
    }

    /**
     * Gets simple field options.
     *
     * @param Form $form
     *
     * @return array
     */
    protected function getSimpleFieldOptions(Form $form)
    {
        $config    = $form->getConfig();
        $innerType = $this->getInnerType($form);
        $data      = array(
            'label'      => $this->translator->trans($config->getOption('label'), [], $this->getTranslationDomain($form)),
            'value'      => $form->createView()->vars['value'],
            'type'       => $innerType->getBlockPrefix(),
            'parentType' => $this->getParentType($innerType),
            'options'    => $this->getTypeOptions($form)
        );

        return $data;
    }

    /**
     * Gets complex form definitions
     *
     * @param Form $form
     *
     * @return array
     */
    public function getComplexFieldDefinitions(Form $form)
    {
        $innerType = $this->getInnerType($form);

        return [
            'type'       => $innerType->getBlockPrefix(),
            'parentType' => $this->getParentType($innerType),
            'children'   => array_map(
                function (Form $child) {
                    return $this->getDefinitions($child);
                },
                $form->all()
            )
        ];
    }

    /**
     * Gets choices.
     *
     * @param Form   $form
     *
     * @return array|null
     */
    protected function getChoices(Form $form, $optionAlias = 'choices')
    {
        $translationDomain = $this->getTranslationDomain($form);
        $view              = $form->createView();
        /** @var ChoiceView[] $choiceViews */
        $choiceViews = $view->vars[$optionAlias];
        $choices     = [];
        foreach ($choiceViews as $choiceView) {
            $choices[$choiceView->value] = $this->translator->trans($choiceView->label, [], $translationDomain);
        }

        return $choices;
    }

    /**
     * Gets parent type.
     *
     * @param AbstractType $type
     *
     * @return string
     */
    protected function getParentType(AbstractType $type)
    {
        $parentType = $type->getParent();

        if ($parentType && class_exists($parentType)) {
            $class = new $parentType();
            if (method_exists($class, 'getBlockPrefix')) {
                $parentType = $class->getBlockPrefix();
            }
        }

        return $parentType == 'form' ? null : $parentType;
    }

    /**
     * Gets additional type options
     *
     * @param Form $form
     *
     * @return array
     */
    protected function getTypeOptions(Form $form)
    {
        $data = [];

        $type = $form->getConfig()->getType();

        if ($this->isTypeOf($type, 'choice')) {
            $choices = $this->getChoices($form, 'choices');
            if (count($choices)) {
                $data['choices'] = $choices;
            }
            $preferredChoices = $this->getChoices($form, 'preferred_choices');
            if (count($preferredChoices)) {
                $data['preferred_choices'] = $preferredChoices;
            }
        }

        if ($this->isTypeOf($type, 'money')) {
            $data['currency'] = $form->getConfig()->getOption('currency');
        }

        return $data;
    }

    /**
     * Gets form translation domain
     *
     * @param FormInterface $form
     *
     * @return string|null
     */
    protected function getTranslationDomain(FormInterface $form)
    {
        return $form->getConfig()->getOption('translation_domain') ?: ($form->getParent() ? $this->getTranslationDomain($form->getParent()) : null);
    }

    /**
     * Checks if type or its ancestors is expected
     *
     * @param ResolvedFormType|ResolvedFormTypeInterface $type
     * @param string                                     $expected
     *
     * @return bool
     */
    protected function isTypeOf(ResolvedFormTypeInterface $type, $expected)
    {
        return ($type->getBlockPrefix() == $expected) ? true : ($type->getParent() ? $this->isTypeOf($type->getParent(), $expected) : false);
    }

    /**
     * Gets form inner type
     *
     * @param FormInterface $form
     *
     * @return AbstractType
     */
    protected function getInnerType(FormInterface $form)
    {
        return $form->getConfig()->getType()->getInnerType();
    }
}
