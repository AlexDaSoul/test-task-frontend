<?php

namespace AppBundle\Form\Handler;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait DefaultRestFormHandlerTrait.
 */
trait DefaultRestFormHandlerTrait
{
    /** @var ViewHandler */
    protected $viewHandler;

    /**
     * Gets form options for create form
     * Can be used for example when csrf should be disabled.
     *
     * @return array
     */
    protected function getFormOptions()
    {
        return array('csrf_protection' => false);
    }

    /**
     * Injects form handler.
     *
     * @param ViewHandler $viewHandler
     */
    public function setViewHandler(ViewHandler $viewHandler)
    {
        $this->viewHandler = $viewHandler;
    }

    /**
     * Renders form fields.
     *
     * @return array
     */
    public function renderFields()
    {
        $this->buildForm();

        return array($this->form->getName() => $this->getContainer()->get('frontend.form_flat_options_retriever')->getDefinitions($this->form));
    }

    /**
     * Renders form view or missng data view.
     *
     * @param Request       $request
     * @param FormInterface $form
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderFormOrErrorResponse(Request $request, FormInterface $form)
    {
        if ($request->isMethod('GET')) {
            return;
        }

        return $this->viewHandler->handle($form->getErrors()->count() ? $this->getFormView($form) : ($this->getPostMissingDataView($form) ?: $this->getFormView($form)));
    }

    /**
     * Gets form view.
     *
     * @param FormInterface $form
     *
     * @return View
     */
    protected function getFormView(FormInterface $form)
    {
        return View::create($form);
    }

    /**
     * If form is not submitted - gets View with error data.
     *
     * @param FormInterface $form
     *
     * @return View|null
     */
    protected function getPostMissingDataView(FormInterface $form)
    {
        return $form->isSubmitted() ? null : View::create(
            array(
                'code'    => 406,
                'message' => 'Missing data parameter',
                'errors'  => array(sprintf('Missing request data key: %s', $form->getName())),
            )
        )->setStatusCode(406);
    }

    /**
     * Gets container
     *
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }
}
