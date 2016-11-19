<?php

namespace AppBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Payever\ApplicationFrontendBundle\Manager\UserManager;
use Payever\CommonBundle\Entity\UserAccount;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class DefaultFormHandler
 * Default form handler class.
 */
abstract class DefaultFormHandler implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const RETURN_URL_PARAM = 'return_url';

    /** @var \Symfony\Component\Form\Form */
    protected $form;
    /** @var \Symfony\Component\Form\FormFactoryInterface */
    protected $formFactory;
    /** @var \Symfony\Component\Routing\Router */
    protected $router;
    /** @var \Symfony\Bundle\TwigBundle\TwigEngine */
    protected $templating;
    /** @var EntityManager */
    protected $em;
    /** @var mixed */
    protected $model;
    /** @var Session */
    protected $session;
    /** @var RequestStack */
    protected $requestStack;

    /**
     * On class construct.
     *
     * @param FormFactoryInterface $formFactory
     * @param Router               $router
     * @param TwigEngine           $templating
     * @param EntityManager        $em
     * @param Session              $session
     */
    public function __construct(FormFactoryInterface $formFactory, Router $router, TwigEngine $templating, EntityManager $em, Session $session)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->templating = $templating;
        $this->em = $em;
        $this->session = $session;
    }

    /**
     * Set request stack service
     *
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Gets current request.
     *
     * @return Request
     */
    protected function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * Gets new model instance.
     *
     * @return mixed
     */
    abstract public function getNewModelInstance();

    /**
     * Gets new type instance.
     *
     * @return string
     */
    abstract public function getNewTypeInstance();

    /**
     * Trigger on success submission.
     *
     * @return Response
     */
    abstract public function onSuccess();

    /**
     * Renders the form.
     *
     * @return Response
     */
    abstract public function renderForm();

    /**
     * Gets form options for create form
     * Can be used for example when csrf should be disabled.
     *
     * @return array
     */
    protected function getFormOptions()
    {
        return array();
    }

    /**
     * Handles form submission.
     *
     * @return RedirectResponse|Response
     */
    public function handle()
    {
        $this->buildForm();

        return ($this->getRequest()->query->has('validate')) ? $this->validate() : $this->handleForm();
    }

    /**
     * Builds form
     * Defines form type and model.
     */
    public function buildForm()
    {
        $this->model = $this->getNewModelInstance();
        $this->form = $this->formFactory->create($this->getNewTypeInstance(), $this->model, $this->getFormOptions());
    }

    /**
     * Handles the form.
     *
     * @return Response
     */
    protected function handleForm()
    {
        try {
            $this->form->handleRequest($this->getRequest());
            if ($this->form->isSubmitted()) {
                return $this->form->isValid() ? $this->onSuccess() : $this->onFailed();
            }
        } catch (\Exception $exception) {
            $this->form->addError(new FormError($exception->getMessage()));

            return $this->onFailed();
        }

        return $this->renderForm();
    }

    /**
     * On form submission failed.
     *
     * @return Response
     */
    public function onFailed()
    {
        return $this->renderForm();
    }

    /**
     * Gets security context.
     *
     * @return AuthorizationCheckerInterface
     */
    protected function getAuthorizationChecker()
    {
        return $this->container->get('security.authorization_checker');
    }

    /**
     * Adds flash message.
     *
     * @param string $key
     * @param string $value
     */
    protected function addFlash($key, $value)
    {
        $this->session->getFlashBag()->add($key, $value);
    }

    /**
     * Validates the form and gets JSON response.
     *
     * @return Response
     */
    public function validate()
    {
        $this->model = $this->getNewModelInstance();
        $this->form = $this->formFactory->create($this->getNewTypeInstance(), $this->model);
        $this->form->submit($this->getRequest());

        return $this->validationJSONResponse();
    }

    /**
     * Renders validation errors.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function validationJSONResponse()
    {
        $errors = $this->collectErrors();

        $response = array('errors' => $errors);
        $response['length'] = count($errors);

        return new Response(json_encode($response), 200, array('content-type' => 'application/json'));
    }

    /**
     * Collect errors from the form children.
     *
     * @return array
     */
    protected function collectErrors()
    {
        $errors = array();

        $this->collectErrorsChildren($this->form, $errors, $this->form->getName());

        foreach ($this->form->getErrors() as $error) {
            $errors[$this->form->getName()][] = $this->translateFormError($error);
        }

        return $errors;
    }

    /**
     * Collect errors from the form children.
     *
     * @param FormInterface $form
     * @param array         &$errors
     * @param string        $prefix
     *
     * @return array
     */
    protected function collectErrorsChildren(FormInterface $form, &$errors = array(), $prefix = '')
    {
        foreach ($form->all() as $key => $child) {
            /* @var Form $child */
            $errorName = $prefix.'_'.$key;
            if (count($child->getErrors())) {
                foreach ($child->getErrors() as $error) {
                    @$errors[$errorName][] = $this->translateFormError($error);
                }
            }
            $this->collectErrorsChildren($child, $errors, $errorName);
        }

        return $errors;
    }

    /**
     * Translates form error.
     *
     * @param FormError $error
     *
     * @return string
     */
    public function translateFormError(FormError $error)
    {
        return $this->getTranslator()->trans($error->getMessage());
    }

    /**
     * Gets translator.
     *
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->container->get('translator');
    }

    /**
     * Gets return url request parameter.
     *
     * @return string|null
     */
    protected function getReturnUrl()
    {
        return $this->getRequest()->get(self::RETURN_URL_PARAM);
    }
}
