<?php

namespace App\Controller;

use \App\Entity\League;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \App\Repository\LeagueRepository;
use \App\Form\LeagueType;
use \App\Services\FileUploader;

/**
* @Route("/league", name="league.")
*/
class LeagueController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @param LeagueRepository $leagueRepository
     */
    public function index(LeagueRepository $leagueRepository)
    {
        $leagues = $leagueRepository->findAll();
                
        return $this->render('league/index.html.twig', [
            'leagues' => $leagues,
        ]);
    }
    
    /**
     * @Route("/create", name="create")
     * @param Request $request
     */
    public function create(Request $request, FileUploader $fileUploader)
    {
        
        $league = new League();
        
        $form = $this->createForm(LeagueType::class, $league);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            // entity manager
            $em = $this->getDoctrine()->getManager();
            /**
             * @var UploaddedFile $file
             */
//            $file = $request->files->get('league')['logo'];
//            if ($file) {
//                $filename = $fileUploader->uploadFile($file);
//            }
//            
//            $league->setImage($filename);
            $em->persist($league);
            $em->flush();
            
            // redirect and add falsh message
            $this->addFlash('success', 'League Created');
            return $this->redirect($this->generateUrl('league.index'));
        }
        
        return $this->render('league/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    
    // Not using
    /**
     * @Route("/add", name="add")
     * @param Request $request
     */
    public function add(Request $request)
    {
        $league = new League();
        $league->setTitle('A Series');
        $league->setDescription('A Series');
        // entity manager
        $em = $this->getDoctrine()->getManager();
        $em->persist($league);
        $em->flush();
        
        // redirect and add falsh message
        $this->addFlash('success', 'League Created');
        return $this->redirect($this->generateUrl('league.index'));
    }
    
    // Two ways to fetch the entity
    /**
     * First way
     * @Route("/show/{id}", name="show")
     */
    public function show($id, LeagueRepository $leagueRepository)
    {
        $league = $leagueRepository->find($id);
                
        return $this->render('league/show.html.twig', [
            'league' => $league,
        ]);
    }
    
    /**
     * Second way
     * @Route("/show-league/{id}", name="show-league")
     * @param League $league
     */
    public function showLeague(League $league)
    {
        return $this->render('league/show.html.twig', [
            'league' => $league,
        ]);
    }
    
    /**
     * @Route("/remove/{id}", name="remove")
     * @param League $league
     */
    public function remove(League $league)
    {
        // entity manager
        $em = $this->getDoctrine()->getManager();
        $em->remove($league);
        $em->flush();
        
        // set redirect and add falsh message
        $this->addFlash('success', 'League deleted');
        return $this->redirect($this->generateUrl('league.index'));
    }
    
}
