<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        //Créer un service pour la logique ci dessous

        //Recuperer les fichier .md
        $phpMd = file_get_contents($this->getParameter('symfony.md.file'));

        //split chaque contenue de fichier par ***
        $separator = $this->getParameter('separator.string');
        $splitPhpMd = explode($separator, $phpMd);
        
        //Tirage au sort
        $choiceCard = array_rand($splitPhpMd);
 
        //***** */

        return $this->render('home/index.html.twig', [
            'card' => $splitPhpMd[$choiceCard],
        ]);
    }
}
