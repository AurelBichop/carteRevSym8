<?php

namespace App\Controller;

use App\Entity\MarkDownFile;
use App\Repository\MarkDownFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(private Filesystem $filesystem, private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'app_home')]
    public function index(MarkDownFileRepository $markDownFileRepository): Response
    {
        //Créer un service pour la logique ci dessous

        //Recuperer les fichier .md
        //$this->filesystem->exists('../filesCards/php.md'))
        //$phpMd = $this->filesystem->readFile('../filesCards/php.md');
        $fileMD = $markDownFileRepository->findOneBy(['name'=>'Php']) ?? (new MarkDownFile);
        $phpMd = $fileMD->getContent();

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

    #[Route('/update', name: 'app_update')]
    public function update(MarkDownFileRepository $markDownFileRepository): Response 
    {
        
        $markDownRevPhpString = $this->filesystem->readFile($this->getParameter('php.md.url.file'));
        $markDownRevTwigString = $this->filesystem->readFile($this->getParameter('twig.md.url.file'));
        $markDownRevSymString = $this->filesystem->readFile($this->getParameter('symfony.md.url.file'));

        //Faire un boucle const Class 

        foreach(MarkDownFile::NAMES as $name){
            $nameVariable = 'markDownRev'.$name.'String';
            
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
