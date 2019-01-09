<?php

namespace CD\PlatformBundle\Controller;

use CD\PlatformBundle\Entity\Project;
use CD\PlatformBundle\Entity\Lang;
use CD\PlatformBundle\Entity\Traduction_Source;
use CD\PlatformBundle\Entity\Traduction_Target;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use CD\PlatformBundle\Form\Traduction_TargetType;

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
      		return $this->redirectToRoute('projects_list');
		}

		$repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Project');
		$project = $repository->find($id);

		if (null === $project) {
			throw new NotFoundHttpException("Le projet portant l'id ".$id." n'existe pas.");
		}

		return $this->render('CDPlatformBundle:Project:view.html.twig', array(
			'project' => $project,
            'sources' => $project->getSources(),
		));
	}

    /**
     * @Route("/project/{idproj}/view_source/{ids}", name="project_source", requirements={"idproj": "\d+", "ids": "\d+"})
     */
    public function viewTargetsAction($idproj, $ids)
    {
        if (null === $this->getUser()) {
      		return $this->redirectToRoute('projects_list');
		}

        $repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Project');
		$project = $repository->find($idproj);

        if (null === $project) {
			throw new NotFoundHttpException("Le projet portant l'id ".$idproj." n'existe pas.");
		}

        $repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Traduction_Source');
        $source = $repository->findOneBy(array('id' => $ids, 'project' => $project));

        if (null === $source) {
            throw new NotFoundHttpException("La source portant l'id ".$ids." n'existe pas.");
        }

        return $this->render('CDPlatformBundle:Project:view_source.html.twig', array(
			'project' => $project,
            'source' => $source,
		));
    }

    /**
     * @Route("/project/add", name="project_add")
     */
	public function addAction(Request $request)
	{
		if (null === $this->getUser()) {
      		return $this->redirectToRoute('projects_list');
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
				return $this->redirectToRoute('projects_list');
			}
		}

		return $this->render('CDPlatformBundle:Project:add.html.twig', array(
			'form' => $form->createView(),
		));
	}

    /**
     * @Route("/project/{id}/addsource", name="project_addsource", requirements={"id"="\d+"})
     */
    public function addSourceAction($id, Request $request)
    {
        if (null === $this->getUser()) {
      		return $this->redirectToRoute('projects_list');
		}

		$repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Project');
		$project = $repository->find($id);

		if (null === $project) {
			throw new NotFoundHttpException("Le projet portant l'id ".$id." n'existe pas.");
		}

        if ($project->getUser() != $this->getUser()) {
            return $this->redirectToRoute('projects_list');
        }

        $source = new Traduction_Source();
        $source->setProject($project);

        $target = new Traduction_Target();
        $target->setLang($project->getLang());
        $target->setSource($source);

        $form   = $this->get('form.factory')->create(Traduction_TargetType::class, $target);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $source->setDate(new \Datetime());
            $target->setDate(new \Datetime());
            $target->setAuthor($this->getUser());
          $em = $this->getDoctrine()->getManager();
          $em->persist($target);
          $em->persist($source);
          $em->flush();

          $request->getSession()->getFlashBag()->add('success', 'La source à bien été ajoutée !');
          //return $this->redirectToRoute('projects_list');
      } elseif ($request->isMethod('POST') && $request->files->get('sourcesFile') != null) {
          $file = $request->files->get('sourcesFile');
          $extension = $file->guessExtension();
          if ($extension === "txt") {

              if (($handle = fopen($file->getRealPath(), 'r')) !== FALSE) {
                  while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
                      $source = new Traduction_Source();
                      $source->setProject($project);
                      $source->setSource($data[0]);
                      $source->setDate(new \Datetime());
                      $target = new Traduction_Target();
                      $target->setLang($project->getLang());
                      $target->setSource($source);
                      $target->setTarget($data[1]);
                      $target->setDate(new \Datetime());
                      $target->setAuthor($this->getUser());
                      $em = $this->getDoctrine()->getManager();
                      $em->persist($target);
                      $em->persist($source);
                  }
                  $em->flush();
                  $request->getSession()->getFlashBag()->add('success', 'Les sources du fichier ont bien été ajoutées !');
              }
          }
      }

        return $this->render('CDPlatformBundle:Project:add_source.html.twig', array(
			'project' => $project,
            'form' =>$form->createView()
		));
    }

    /**
     * @Route("/project/{id}/froze", name="project_froze", requirements={"id"="\d+"})
     */
    public function frozeProjectAction($id, Request $request)
    {
        if (null === $this->getUser()) {
      		return $this->redirectToRoute('projects_list');
		}

		$repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Project');
		$project = $repository->find($id);

		if (null === $project) {
			throw new NotFoundHttpException("Le projet portant l'id ".$id." n'existe pas.");
		}

        if ($project->getUser() != $this->getUser()) {
            return $this->redirectToRoute('projects_list');
        }

        $project->setFrozen(!$project->getFrozen());

        $em = $this->getDoctrine()->getManager();
        //$em->persist($project);
        $em->flush();

        return $this->redirectToRoute('projects_list');

        /*return $this->render('CDPlatformBundle:Project:add_source.html.twig', array(
			'project' => $project,
            'form' =>$form->createView()
		));*/
    }

    /**
     * @Route("/project/{id}/delete", name="project_delete", requirements={"id"="\d+"})
     */
    public function deleteProjectAction($id, Request $request)
    {
        if (null === $this->getUser()) {
      		return $this->redirectToRoute('projects_list');
		}

		$repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Project');
		$project = $repository->find($id);

		if (null === $project) {
			throw new NotFoundHttpException("Le projet portant l'id ".$id." n'existe pas.");
		}

        if ($project->getUser() != $this->getUser()) {
            return $this->redirectToRoute('projects_list');
        }

        $em = $this->getDoctrine()->getManager();

        foreach ($project->getSources() as $source) {
            foreach ($source->getTargets() as $target) {
                $em->remove($target);
            }
            $em->remove($source);
        }
        $em->flush();
        $em->remove($project);
        $em->flush();

        return $this->redirectToRoute('projects_list');
    }
}
