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
	public function indexAction(Request $request)
	{
		if (null === $this->getUser()) {
      return $this->redirectToRoute('user_login');
		}

		$repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Project');

		$projects_list = $repository->findAll();
    $projects = $this->get('knp_paginator')->paginate($projects_list, $request->query->get('page', 1),10);

		return $this->render('CDPlatformBundle:Project:index.html.twig', array(
			'projects' => $projects
		));
	}

    /**
     * @Route("/project/{id}", name="project_show", requirements={"id"="\d+"})
     */
	public function viewAction($id, Request $request)
	{
		if (null === $this->getUser()) {
      		return $this->redirectToRoute('projects_list');
		}

		$repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Project');
		$project = $repository->find($id);

		if (null === $project) {
			throw new NotFoundHttpException("Le projet portant l'id ".$id." n'existe pas.");
		}

    $project_src = $project->getSources();
    $sources = $this->get('knp_paginator')->paginate($project_src, $request->query->get('page', 1),5);

		return $this->render('CDPlatformBundle:Project:view.html.twig', array(
			'project' => $project,
      'sources' => $sources,
      'allsources' => $project->getSources(),
		));
	}

    /**
     * @Route("/project/{idproj}/view_source/{ids}", name="project_source", requirements={"idproj": "\d+", "ids": "\d+"})
     * @Route("/project/{idproj}/view_source/{ids}/modify_target/{idt}", name="modify_target", requirements={"idproj": "\d+", "ids": "\d+", "idt": "\d+"})
     */
    public function viewTargetsAction($idproj, $ids, $idt = -1, Request $request)
    {
        if (null === $this->getUser()) {
      		return $this->redirectToRoute('projects_list');
		}

        $user = $this->getUser();

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

        $repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Traduction_Target');
        if ($idt !== -1)
            $target = $repository->find($idt);
        else
            $target = new Traduction_Target();

		$formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $target, ['translation_domain' => false]);

        if ($idt === -1) {
            $formBuilder
                ->add('lang',       EntityType::class, array('class' => Lang::class,
                    'query_builder' => function($repository) use($project, $user) {
                        $qb = $repository->createQueryBuilder('l');
                        return $qb
                            ->where('l.code != :projectlang')
                            ->andWhere('l in (:userlangs)')
                            ->setParameter('projectlang', $project->getLang())
                            ->setParameter('userlangs', $user->getLangs())
                        ;
                    }, 'choice_label' => 'code', 'multiple' => false));
        }

        $formBuilder
            ->add('target',     TextType::class)
			->add('save',       SubmitType::class, array('label' => 'Valider'))
		;

		$form = $formBuilder->getForm();

        if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
                $target->setSource($source);
                $target->setAuthor($user);
                $target->setDate(new \Datetime());

				$em = $this->getDoctrine()->getManager();
				$em->persist($target);
				$em->flush();

                if ($idt !== -1) {
				    $request->getSession()->getFlashBag()->add('success', 'La traduction à bien été modifiée !');
                    return $this->redirectToRoute('project_source', array('idproj' => $idproj, 'ids' => $ids));
                }
                else
                    $request->getSession()->getFlashBag()->add('success', 'La traduction à bien été ajoutée !');
			}
		}

        $targets = $this->get('knp_paginator')->paginate($source->getTargets(), $request->query->get('page', 1),10);

        return $this->render('CDPlatformBundle:Project:view_source.html.twig', array(
			'project' => $project,
            'source' => $source,
            'targets' => $targets,
            'form' => $form->createView(),
            'is_modify' => ($idt !== -1),
		));
    }

    /**
     * @Route("/traductor", name="traductor_page")
     */
    public function traductorViewAction(Request $request)
    {
        if (null === $this->getUser()) {
      		return $this->redirectToRoute('projects_list');
		}
        if (count($this->getUser()->getLangs()) < 2) {
            return $this->redirectToRoute('projects_list');
        }

        $repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Project');
		    $projects_list = $repository->getProjectsByLangs($this->getUser()->getId(), $this->getUser()->getLangs());
        $projects = $this->get('knp_paginator')->paginate($projects_list, $request->query->get('page', 1),10);

        $repository = $this->getDoctrine()->getManager()->getRepository('CDPlatformBundle:Traduction_Source');
        $random_source = $repository->getRandomUntranslatedSource($this->getUser()->getId(), $this->getUser()->getLangs());

        return $this->render('CDPlatformBundle:Project:traductor_page.html.twig', array(
			'projects' => $projects,
            'random_source' => $random_source
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

		$formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $project, ['translation_domain' => false]);

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

        $form = $this->get('form.factory')->create(Traduction_TargetType::class, $target, ['translation_domain' => false]);

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
