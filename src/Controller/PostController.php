<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * Create post
     * 
     * Avec Symfony, c'est la même action de contrôleur qui affiche et qui traite le form
     * 
     * @Route("/post/create", name="post_create", methods={"GET", "POST"})
     */
    public function create(ManagerRegistry $doctrine, Request $request): Response
    {
        // Si le form a été posté (la méthode HTTP de la requête est POST), on le traite
        if ($request->isMethod('POST')) {
            // On crée une entité "Doctrine"
            $post = new Post;
            //dump($post);

            // Les valeurs par défaut des autres champs nécessaires
            // sont définis directement dans l'entité

            // Reste à définir celles qui viennent du form 

            // Titre
            $post->setTitle($request->request->get('title')); // $post->setTitle($_POST['title']);
            // Contenu
            $post->setBody($request->request->get('body'));
            // Date de publication basée sur l'input du form
            $date = new DateTimeImmutable($request->request->get('published_at'));
            $post->setPublishedAt($date);

            // On va faire appel au Manager de Doctrine
            $entityManager = $doctrine->getManager();
            // Prépare-toi à "persister" notre objet (req. INSERT INTO)
            $entityManager->persist($post);

            // On exécute les requêtes SQL
            $entityManager->flush();

            //dd($post);

            // On redirige vers la liste
            return $this->redirectToRoute('post_list');
        }

        // Sinon on affiche le formulaire
        return $this->render('post/add.html.twig');
    }

    /**
     * List posts
     * 
     * @Route("/", name="post_list")
     */
    public function list(PostRepository $postRepository)
    {
        // On trie par date de publication inverse
        $postsList = $postRepository->findBy(
            [], // Pas de critère, pas de condition WHERE
            ['publishedAt' => 'DESC']
        );

        return $this->render('post/list.html.twig', [
            'postsList' => $postsList,
        ]);
    }

    /**
     * Post show
     * 
     * @Route("/post/{id}", name="post_show", requirements={"id"="\d+"})
     */
    public function show($id, ManagerRegistry $doctrine)
    {
        // Alternative pour accéder au Repository de l'entité Post
        $postRepository = $doctrine->getRepository(Post::class);

        $post = $postRepository->find($id);

        // Post not found ?
        if ($post === null) {
            throw $this->createNotFoundException('Article non trouvé.');
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * Post update
     * 
     * @Route("/post/update/{id}", name="post_update", requirements={"id"="\d+"})
     */
    public function update($id, PostRepository $postRepository, ManagerRegistry $doctrine)
    {
        // On va chercher l'enregistrement
        $post = $postRepository->find($id);

        // Post not found ?
        if ($post === null) {
            throw $this->createNotFoundException('Article non trouvé.');
        }

        // On modifie la date de mise à jour à la date actuelle
        $post->setUpdatedAt(new DateTime());

        // On le met à jour via le Manager
        $entityManager = $doctrine->getManager();
        // Exécute la requête d'UPDATE
        $entityManager->flush();

        // Ajout d'un message Flash
        // (type, message) => (label, message)
        $this->addFlash('success', 'Article mis à jour.');
        $this->addFlash('success', 'C\'est top :)');
        $this->addFlash('warning', 'Attention derrière toi !');

        return $this->redirectToRoute('post_list');
    }

    /**
     * Post delete
     * 
     * @Route("/post/delete/{id}", name="post_delete", requirements={"id"="\d+"})
     */
    public function delete($id, PostRepository $postRepository, ManagerRegistry $doctrine)
    {
        // On va chercher l'enregistrement
        $post = $postRepository->find($id);

        // Post not found ?
        if ($post === null) {
            throw $this->createNotFoundException('Article non trouvé.');
        }

        // On demande au Manager de le supprimer
        $entityManager = $doctrine->getManager();
        $entityManager->remove($post);
        // Exécute la requête d'UPDATE
        $entityManager->flush();

        $this->addFlash('success', 'Article supprimé.');

        return $this->redirectToRoute('post_list');
    }
}
