<?php

namespace CD\PlatformBundle\Controller;

use CD\PlatformBundle\Entity\Project;
use CD\PlatformBundle\Entity\Lang;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProjectController extends Controller
{

    /**
     * @Route("/", name="projects_list")
     */
	public function indexAction()
	{
		if (null === $this->getUser()) {
      		return $this->redirectToRoute('user_login');
		}

		$repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Project');

		$projects = $repository->findAll();

		return $this->render('CDPlatformBundle:Project:index.html.twig', array(
			'projects' => $projects
		));
	}

    /**
     * @Route("/project/{id}", name="project_show", requirements={"id"="\d+"})
     */
	public function viewAction($id)
	{
		if (null === $this->getUser()) {
      		return $this->redirectToRoute('cd_platform_home');
		}

		$repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Project');
		$project = $repository->find($id);

		if (null === $project) {
			throw new NotFoundHttpException("Le projet portant l'id ".$id." n'existe pas.");
		}

		return $this->render('CDPlatformBundle:Project:view.html.twig', array(
			'project_id' => $project->getId(),
			'project_name' => $project->getName(),
			'project_date' => $project->getDate()
		));
	}

    /**
     * @Route("/project/add", name="project_add")
     */
	public function addAction(Request $request)
	{
		if (null === $this->getUser()) {
      		return $this->redirectToRoute('cd_platform_home');
		}

		$user = $this->getUser();

		$project = new Project();

		$formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $project);

		$formBuilder
			->add('name',      TextType::class)
            ->add('lang',  EntityType::class, array('class' => Lang::class, 'choice_label' => 'code', 'multiple' => false))
			->add('save',      SubmitType::class, array('label' => 'Valider'))
		;

		$form = $formBuilder->getForm();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				$project->setDate(new \Datetime());
				$project->setUser($user);

				$em = $this->getDoctrine()->getManager();
				$em->persist($project);
				$em->flush();

				$request->getSession()->getFlashBag()->add('success', 'Le projet a bien été créé!');
				return $this->redirectToRoute('cd_platform_home');
			}
		}

		return $this->render('CDPlatformBundle:Project:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
}
