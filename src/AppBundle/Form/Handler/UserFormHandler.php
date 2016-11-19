<?php

namespace AppBundle\Form\Handler;

use AppBundle\Entity\User;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\Type\UserType;

/**
 * Class UserFormHandler.
 */
class UserFormHandler extends DefaultFormHandler
{
    use DefaultRestFormHandlerTrait;

    /** @var User */
    protected $model;

    /** @var  FormFlatOptionsRetriever */
    protected $optionsRetriever;

    /**
     * @inheritdoc
     */
    public function getNewTypeInstance()
    {
        return UserType::class;
    }

    /**
     * Gets new model instance.
     *
     * @return User
     */
    public function getNewModelInstance()
    {
        return $this->model;
    }

    /**
     * Trigger on success submission.
     *
     * @return Response
     */
    public function onSuccess()
    {
        if ($this->form['password']->getData()) {
            $this->model->setPassword(md5($this->form['password']->getData()));
        }
        $this->em->persist($this->model);
        $this->em->flush();

        $this->getRequest()->setRequestFormat('json');
        $responseData = $this->optionsRetriever->getFlatDefinitions($this->form);

        return $this->viewHandler->handle(View::create($responseData));
    }

    /**
     * {@inheritdoc}
     */
    public function renderForm()
    {
        $request = $this->getRequest();
        $request->setRequestFormat('json');

        return $this->renderFormOrErrorResponse($request, $this->form);
    }

    /**
     * Get built form.view
     *
     * @return FormView
     */
    public function getBuiltFormView()
    {
        $this->buildForm();

        return $this->form->createView();
    }

    /**
     * @return array
     */
    protected function getFormOptions()
    {
        return ['csrf_protection' => true];
    }

    /**
     * Set model
     *
     * @param User $user
     *
     * @return $this
     */
    public function setModel($user)
    {
        $this->model = $user;

        return $this;
    }

    /**
     * Sets flat view options retriever service
     *
     * @param FormFlatOptionsRetriever $optionsRetriever
     */
    public function setOptionsRetriever(FormFlatOptionsRetriever $optionsRetriever)
    {
        $this->optionsRetriever = $optionsRetriever;
    }
}