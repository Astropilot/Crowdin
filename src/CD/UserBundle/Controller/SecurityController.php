<?php
namespace CD\UserBundle\Controller;

use CD\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('cd_platform_home');
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        return $this->render('CDUserBundle:Security:login.html.twig', array(
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ));
    }

    public function registerAction(Request $request)
    {
        $user = new User();

        //$repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Lang');
        //$langs = $repository->findAll();

        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $user);

        $formBuilder
        ->add('username',       TextType::class)
        ->add('password',       PasswordType::class)
        ->add('email',          EmailType::class)
        ->add('description',    TextareaType::class, array('required' => false))
        //->add('langs',          ChoiceType::class, array('choices', $langs))
        ->add('save',           SubmitType::class, array('label' => 'Inscription'))
        ;

        $form = $formBuilder->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $user->setSalt('');
                $user->setRoles(array('ROLE_USER'));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $request->getSession()->getFlashBag()->add('success', 'Inscription réussie!');
                return $this->redirectToRoute('cd_platform_home');
            }
        }

        return $this->render('CDUserBundle:Security:register.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}