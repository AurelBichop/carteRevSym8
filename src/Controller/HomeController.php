<?php

namespace App\Controller;

use App\Entity\MarkDownFile;
use App\Repository\MarkDownFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private Filesystem $filesystem, 
        private EntityManagerInterface $entityManager,
        private MarkDownFileRepository $markDownFileRepository
        )
    {
    }

    #[Route('/{categorie}', name: 'app_home', defaults: ['categorie' => null], priority:-5)]
    public function index(string|null $categorie): Response
    {
        $contentMd = "";        

        if(is_null($categorie))
        {
            $allMdFile = $this->markDownFileRepository->findAll();

            foreach($allMdFile as $oneFileMd)
            {
                $contentMd .= $oneFileMd->getContent();
            }
        }else
        {
            $contentMd = $this->checkCategorie($categorie);
        }

        //Tirage au sort
        $splitMd = $this->splitMd($contentMd);
        $choiceCard = array_rand($splitMd);
 
        return $this->render('home/index.html.twig', [
            'card' => $splitMd[$choiceCard],
            'next' => $categorie,
            'nb_cards'=> count($splitMd),
        ]);
    }
    
    #[Route('/all/{categorie}', name: 'app_all')]
    public function allCard(string $categorie)
    {
        $cards = $this->splitMd($this->checkCategorie($categorie));

        shuffle($cards);

        return $this->render('home/all.html.twig',[
            'cards' => $cards,
            'next' => $categorie,
            'nb_cards' => count($cards),
        ]);
    }


    #[Route('/update', name: 'app_update')]
    public function update(MarkDownFileRepository $markDownFileRepository): Response 
    {
        foreach(MarkDownFile::NAMES as $name){
            
            $nameVariable = 'markDownRev'.$name.'String';
            $$nameVariable = $this->filesystem->readFile($this->getParameter(strtolower($name).'.md.url.file'));  

            $file = $markDownFileRepository->findOneBy(["name"=>$name]);

            if($file)
            {
                $file->setContent($$nameVariable);
            }else
            {
                $file =  new MarkDownFile();
                $file->setName($name)
                    ->setContent($$nameVariable);
                $this->entityManager->persist($file);    
            }

            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_home');
    }


    private function checkCategorie(string $categorie): string | HttpException
    {
        if(in_array(ucfirst($categorie), MarkDownFile::NAMES))
        {
            return $this->markDownFileRepository->findOneBy(['name'=>$categorie])->getContent();
        }else{
            throw new HttpException(404,'catégorie qui n\'existe pas');
        }
    }

    //split chaque contenue de fichier par ***
    private function splitMd(string $contentMd): array 
    { 
        return explode($this->getParameter('separator.string'), $contentMd);
    }
}
