<?php

namespace AppBundle\Form\Handler;

use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Form;

/**
 * Class FormFlatOptionsRetriever
 *
 * @package Payever\ApplicationFrontendBundle\Form
 */
class FormFlatOptionsRetriever extends FormOptionsRetriever
{
    /**
     * Gets flat view form definitions
     *
     * @param Form $form
     * @param string $keyPrefix
     * @return array
     */
    public function getFlatDefinitions(Form $form, $keyPrefix = '')
    {
        if ($this->isTypeOf($form->getConfig()->getType(), 'password')) {
            return [];
        }

        if ($this->isTypeOf($form->getConfig()->getType(),'collection')) {
            return $this->getFlatCollectionDefinitions($form, $keyPrefix);
        }

        $result = [];
        if ($form->count() && !$this->isTypeOf($form->getConfig()->getType(),'choice')) {
            foreach ($form->all() as $key => $field) {
                $result += $this->getFlatDefinitions($field, $keyPrefix.'['.$key.']');
            }
        } else {
            $result[$keyPrefix] = [
                'value' => $form->getData(),
            ];

            if ($this->isTypeOf($form->getConfig()->getType(),'choice')) {
                $result[$keyPrefix]['value_label'] = $this->getSelectedChoiceLabel($form);
            }
        }

        return $result;
    }

    /**
     * Gets flat definition form collection field type
     *
     * @param Form $form
     * @param string $keyPrefix
     * @return array
     */
    public function getFlatCollectionDefinitions(Form $form, $keyPrefix = '')
    {
        $result = [$keyPrefix => []];
        foreach ($form->all() as $key => $field) {
            $result[$keyPrefix][] = $field->getData();
        }

        return $result;
    }

    /**
     * Get label of selected choice option
     *
     * @param Form $form
     * @return string|null
     */
    protected function getSelectedChoiceLabel(Form $form)
    {
        $translationDomain = $this->getTranslationDomain($form);
        $view              = $form->createView();

        /** @var ChoiceView[] $choiceViews */
        $choiceViews = $view->vars['preferred_choices'] + $view->vars['choices'];
        foreach ($choiceViews as $choiceView) {
            if ($view->vars['value'] == $choiceView->value) {
                return $this->translator->trans($choiceView->label, [], $translationDomain);
            }
        }
    }
}
