<?php

namespace App\Controller;

use finfo;
use DateTime;
use App\Entity\Post;
use App\Entity\Author;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Repository\PostRepository;
use App\Repository\AuthorRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PostController extends AbstractController
{
    /**
     * Create post
     * 
     * Avec Symfony, c'est la même action de contrôleur qui affiche et qui traite le form
     * 
     * @Route("/post/create", name="post_create", methods={"GET", "POST"})
     */
    public function create(AuthorRepository $AuthorRepository, ManagerRegistry $doctrine, Request $request): Response
    {
        $post = new Post();
        $form = $this->createFormBuilder($post)
            ->add('title', TextType::class)
            ->add('body', TextareaType::class)
            ->add('publishedAt', DateTimeType::class, [
                'input' => 'datetime_immutable',
            ])
            ->add('author')
            ->getForm();

        
           // Le Form inspecte la Requête
            $form->handleRequest($request);

            // Si le form a été soumis et qu'il est valide
         if ($form->isSubmitted() && $form->isValid()) {
            
            // A ce stade, le From a renseigné l'entité $post :)
            // dd($post);

            // On crée une entité "Doctrine"
            // $author = $AuthorRepository->find(6);
            // $post->setAuthor($author);
            //dump($post);

            // On va faire appel au Manager de Doctrine
            $entityManager = $doctrine->getManager();
            // Prépare-toi à "persister" notre objet (req. INSERT INTO)
            $entityManager->persist($post);

            // On exécute les requêtes SQL
            $entityManager->flush();

            //dd($post);

            // On redirige vers la liste
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        // Sinon on affiche le formulaire
        return $this->render('post/add.html.twig', [
            'form' => $form->createView(),
        ]);
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
     * @Route("/post/{id}", name="post_show", requirements={"id"="\d+"}, methods={"GET", "POST"})
     */
    public function show($id, PostRepository $PostRepository, ManagerRegistry $doctrine, Request $request)
    {
        // Alternative pour accéder au Repository de l'entité Post
        $postRepository = $doctrine->getRepository(Post::class);

        $post = $postRepository->find($id);

        // Post not found ?
        if ($post === null) {
            throw $this->createNotFoundException('Article non trouvé.');
        }

        
        /* Sans la relation inverse
            $commentRepository = $doctrine->getRepository(Comment::class);
            // je veux les commentaires de l'article courant
            $comments = $commentRepository->findBy(['post' => $post]);
        */
        
        if ($request->isMethod('POST')) {
            $comment = new Comment;
            //dump($post);

            // Les valeurs par défaut des autres champs nécessaires
            // sont définis directement dans l'entité

            // Reste à définir celles qui viennent du form 

            // Titre
            $comment->setUsername($request->request->get('username')); // $post->setTitle($_POST['title']);
            // Contenu
            $comment->setBody($request->request->get('body'));
            // Date de publication basée sur l'input du form
            $comment->setPost($post);
            // On va faire appel au Manager de Doctrine
            $entityManager = $doctrine->getManager();
            // Prépare-toi à "persister" notre objet (req. INSERT INTO)
            $entityManager->persist($comment);

            // On exécute les requêtes SQL
            $entityManager->flush();

            $this->addFlash(
                'success', 'Commentaire ajouté'
            );
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);

            //dd($post);
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            // 'comments' => $comments
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
