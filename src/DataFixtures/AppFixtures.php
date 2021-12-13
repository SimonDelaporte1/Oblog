<?php

namespace App\DataFixtures;

use App\Entity\Author;
use DateTime;
use App\Entity\Post;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $author = new Author();
        $author->setFirstname('Jesus');
        $author->setLastname('Christ');
        $manager->persist($author);

        // $product = new Product();
        // $manager->persist($product);
        for ($i = 0; $i < 20; $i++) {
            $post = new Post();
            $post->setTitle('Article #'.$i);
            $post->setBody('Lorem ipsum dolor sit amet');            $post->setImage('https://picsum.photos/id/'.mt_rand(1,100).'/300/200');
            $post->setNbLikes(mt_rand(1, 1000));
            $post->setPublishedAt(new DateTimeImmutable());
            $post->setAuthor($author);
            $manager->persist($post);
        }
        $manager->flush();
    }
}
