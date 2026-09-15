<?php

namespace App\Controller;

use App\Entity\MarkDownFile;
use App\Repository\MarkDownFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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

    #[Route('/{categorie}', name: 'app_home', defaults: ['categorie' => null])]
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
            $categorie = ucfirst($categorie);
            if(in_array($categorie, MarkDownFile::NAMES))
            {
                $contentMd = $this->markDownFileRepository->findOneBy(['name'=>$categorie])->getContent();
            }else{
                throw new HttpException(404,'catégorie qui n\'existe pas');
            }
        }

        //split chaque contenue de fichier par ***
        $separator = $this->getParameter('separator.string');
        $splitMd = explode($separator, $contentMd);
        
        //Tirage au sort
        $choiceCard = array_rand($splitMd);
 

        return $this->render('home/index.html.twig', [
            'card' => $splitMd[$choiceCard],
            'next' => $categorie,
        ]);
    }

    #[Route('/update', name: 'app_update', priority:10)]
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
}
