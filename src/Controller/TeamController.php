<?php

namespace App\Controller;

use App\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TeamRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Services\FileUploader;
use App\Form\TeamType;

/**
* @Route("/team", name="team.")
*/
class TeamController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @param TeamRepository $TeamRepository
     */
    public function index(TeamRepository $TeamRepository)
    {
        $teams = $TeamRepository->findAll();
                
        return $this->render('team/index.html.twig', [
            'teams' => $teams,
        ]);
    }
    
    /**
     * @Route("/create", name="create")
     * @param Request $request
     */
    public function create(Request $request, FileUploader $fileUploader)
    {
        
        $team = new Team();
        
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            // entity manager
            $em = $this->getDoctrine()->getManager();
            $em->persist($team);
            $em->flush();
            
            // redirect and add falsh message
            $this->addFlash('success', 'Team Created');
            return $this->redirect($this->generateUrl('team.index'));
        }
        
        return $this->render('team/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/show/{id}", name="show")
     */
    public function show($id, TeamRepository $teamRepository)
    {
        $team = $teamRepository->find($id);
                
        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    }
    
    /**
     * @Route("/remove/{id}", name="remove")
     * @param Team $team
     */
    public function remove(Team $team)
    {
        // entity manager
        $em = $this->getDoctrine()->getManager();
        $em->remove($team);
        $em->flush();
        
        // set redirect and add falsh message
        $this->addFlash('success', 'Team deleted');
        return $this->redirect($this->generateUrl('team.index'));
    }
}
