<?php

namespace App\Controller;

use App\Repository\AuthorRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthorController extends AbstractController
{

    /**
     * List posts
     * 
     * @Route("/author/list", name="author_list")
     */
    public function authorList(AuthorRepository $AuthorRepository)
    {
        // On trie par date de publication inverse
        $authorsList = $AuthorRepository->findBy(
            [], // Pas de critère, pas de condition WHERE
            ['lastname' => 'ASC']
        );


        return $this->render('author/authorList.html.twig', [
            'authorsList' => $authorsList,
        ]);
    }

}
