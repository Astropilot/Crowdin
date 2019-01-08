<?php
namespace CD\UserBundle\Controller;

use CD\UserBundle\Entity\User;
use CD\PlatformBundle\Entity\Lang;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SecurityController extends Controller
{
    /**
     * @Route("/user/login", name="user_login")
     */
    public function loginAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('projects_list');
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        return $this->render('CDUserBundle:Security:login.html.twig', array(
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ));
    }

    /**
     * @Route("/user/register", name="user_register")
     */
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
        ->add('langs',          EntityType::class, array('class' => Lang::class, 'choice_label' => 'code', 'multiple' => true, 'required' => false))
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
                return $this->redirectToRoute('projects_list');
            }
        }

        return $this->render('CDUserBundle:Security:register.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/user/modify", name="user_modify")
     */
    public function modifyAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('user_login');
        }

        $user = $this->getUser();

        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $user);

        $formBuilder
        ->add('description',    TextareaType::class, array('required' => false))
        ->add('langs',          EntityType::class, array('class' => Lang::class, 'choice_label' => 'code', 'multiple' => true, 'required' => false))
        ->add('save',           SubmitType::class, array('label' => 'Modifier'))
        ;

        $form = $formBuilder->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $request->getSession()->getFlashBag()->add('success', 'Votre compte à bien été modifié !');
            }
        }

        return $this->render('CDUserBundle:Security:modify.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * @Route("/user/{username}", name="user_view", requirements={"username"="\w+"})
     */
    public function showAction($username)
    {
        $repositoryUser = $this->getDoctrine()->getManager()->getRepository('CDUserBundle:User');
        $repositoryTraductionTarget = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Traduction_Target');

		$user = $repositoryUser->findOneByUsername($username);
        $projects = array();

        $user_all_trads = $repositoryTraductionTarget->findByAuthor($user);
        foreach ($user_all_trads as $user_trad) {
            $project = $user_trad->getSource()->getProject();
            if (!array_key_exists($project->getId(), $projects))
                $projects[$project->getId()] = $project;
        }
        $projects = array_values($projects);

        foreach ($projects as $project) {
            $project->{"user_trad"} = $repositoryTraductionTarget->countSourcesTranslatedByUserIdForProject($user->getId(), $project->getId());
        }

        

        return $this->render('CDUserBundle:Security:view.html.twig', array(
            'user' => $user,
            'projects' => $projects,
        ));

    }
}
